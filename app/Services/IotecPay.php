<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal client for the ioTec Pay collections API (https://pay.iotec.io).
 *
 * Mobile Money flow: collectMoMo() sends a payment prompt to the payer's
 * handset, then the transaction is confirmed by polling status() or by the
 * portal callback — the callback is always re-verified before anything
 * is marked paid.
 *
 * Card flow: collectCard() returns a cardRedirectUrl; the customer pays on
 * PegPay's hosted page and is sent back to our RedirectUrl with the outcome.
 */
class IotecPay
{
    public const STATUS_PENDING = 'Pending';

    public const STATUS_SENT = 'SentToVendor';

    public const STATUS_SUCCESS = 'Success';

    public const STATUS_FAILED = 'Failed';

    public static function enabled(): bool
    {
        $cfg = config('services.iotec');

        return ! empty($cfg['client_id']) && ! empty($cfg['client_secret']) && ! empty($cfg['wallet_id']);
    }

    public static function assertEnabled(): void
    {
        if (! static::enabled()) {
            throw new IotecPayException('Online payments are not configured yet. Please confirm on WhatsApp and we will activate your order.');
        }
    }

    /**
     * Normalize a Ugandan phone number to 256XXXXXXXXX MSISDN.
     * Returns null when the number is not a plausible UG mobile number.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '256'.substr($digits, 1);
        } elseif (strlen($digits) === 9 && preg_match('/^[67]/', $digits)) {
            $digits = '256'.$digits;
        }

        return preg_match('/^256\d{9}$/', $digits) ? $digits : null;
    }

    /**
     * Send a Mobile Money prompt to the payer's handset.
     *
     * @return array{id:string,status:string,...} The collection view model.
     */
    public static function collectMoMo(string $phone, int $amount, string $externalId, ?string $payerName = null, ?string $payerNote = null, ?string $payeeNote = null): array
    {
        static::assertEnabled();

        $msisdn = static::normalizePhone($phone);

        if (! $msisdn) {
            throw new IotecPayException('That phone number does not look like a valid Ugandan mobile number. Use e.g. 0772 123456.');
        }

        if ($amount < 500) {
            throw new IotecPayException('The minimum online payment is UGX 500.');
        }

        return static::post('/api/collections/collect', array_filter([
            'category' => 'MobileMoney',
            'currency' => 'UGX',
            'walletId' => config('services.iotec.wallet_id'),
            'externalId' => substr($externalId, 0, 100),
            'payer' => $msisdn,
            'payerName' => $payerName ? substr($payerName, 0, 150) : null,
            'payerNote' => $payerNote ? substr($payerNote, 0, 100) : null,
            'amount' => $amount,
            'payeeNote' => $payeeNote ? substr($payeeNote, 0, 100) : null,
            'channel' => 'ttryy-web',
        ], fn ($v) => $v !== null));
    }

    /**
     * Start a Visa/MasterCard collection via PegPay's hosted page.
     *
     * @return array{id:string,status:string,cardRedirectUrl?:string,...}
     */
    public static function collectCard(string $email, int $amount, string $externalId, string $redirectUrl, ?string $payerName = null, ?string $note = null): array
    {
        static::assertEnabled();

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new IotecPayException('A valid email address is needed for card payments.');
        }

        if ($amount < 500) {
            throw new IotecPayException('The minimum online payment is UGX 500.');
        }

        return static::post('/api/collections/collect/card', array_filter([
            'currency' => 'UGX',
            'walletId' => config('services.iotec.wallet_id'),
            'externalId' => substr($externalId, 0, 100),
            'payer' => $email,
            'payerName' => $payerName ? substr($payerName, 0, 150) : null,
            'payerNote' => $note ? substr($note, 0, 100) : null,
            'amount' => $amount,
            'payeeNote' => substr($externalId, 0, 100),
            'channel' => 'ttryy-web',
            'category' => 'Card',
            'redirectUrl' => $redirectUrl,
        ], fn ($v) => $v !== null));
    }

    /** Authoritative status of a collection by its ioTec id. */
    public static function status(string $id): array
    {
        static::assertEnabled();

        $response = Http::withToken(static::token())->acceptJson()->timeout(20)
            ->get(rtrim((string) config('services.iotec.base_url'), '/').'/api/collections/status/'.$id);

        if (! $response->successful()) {
            Log::warning('ioTec status check failed', ['id' => $id, 'http' => $response->status()]);
            throw new IotecPayException('Could not confirm your payment just now — keep the prompt open, it usually lands within a minute.');
        }

        return $response->json() ?? [];
    }

    /** Status lookup by our own external reference. */
    public static function statusByExternalId(string $externalId): array
    {
        static::assertEnabled();

        $response = Http::withToken(static::token())->acceptJson()->timeout(20)
            ->get(rtrim((string) config('services.iotec.base_url'), '/').'/api/collections/external-id/'.urlencode($externalId));

        if ($response->status() === 404) {
            return ['status' => 'NotFound'];
        }

        if (! $response->successful()) {
            Log::warning('ioTec external-id lookup failed', ['external' => $externalId, 'http' => $response->status()]);
            throw new IotecPayException('Could not confirm your payment just now — please try again in a minute.');
        }

        return $response->json() ?? [];
    }

    public static function isSuccess(array $tx): bool
    {
        return ($tx['status'] ?? null) === static::STATUS_SUCCESS;
    }

    public static function isFailed(array $tx): bool
    {
        return in_array($tx['status'] ?? null, [static::STATUS_FAILED, 'RolledBack', 'Cancelled', 'Rejected'], true);
    }

    public static function isPending(array $tx): bool
    {
        return in_array($tx['status'] ?? null, [static::STATUS_PENDING, static::STATUS_SENT, 'AwaitingApproval', 'Scheduled'], true);
    }

    protected static function post(string $path, array $payload): array
    {
        try {
            $response = Http::withToken(static::token())->acceptJson()->timeout(25)
                ->post(rtrim((string) config('services.iotec.base_url'), '/').$path, $payload);
        } catch (\Throwable $e) {
            Log::warning('ioTec request failed', ['path' => $path, 'error' => $e->getMessage()]);
            throw new IotecPayException('Could not reach the payment provider. Check your connection and try again.');
        }

        if ($response->status() === 401) {
            Cache::forget('iotec-pay-token');
            Log::warning('ioTec rejected our credentials');
            throw new IotecPayException('Payment provider rejected our credentials. Please confirm on WhatsApp and we will sort it immediately.');
        }

        if (! $response->successful()) {
            $message = $response->json('message') ?? $response->json('title') ?? 'Payment could not be started.';
            Log::warning('ioTec collect rejected', ['path' => $path, 'http' => $response->status(), 'body' => $response->body()]);
            throw new IotecPayException(is_string($message) && $message !== '' ? $message : 'Payment could not be started. Please try again.');
        }

        $data = $response->json() ?? [];

        if (empty($data['id'])) {
            Log::warning('ioTec collect returned no transaction id', ['body' => $response->body()]);
            throw new IotecPayException('Payment could not be started. Please try again.');
        }

        return $data;
    }

    protected static function token(): string
    {
        return Cache::remember('iotec-pay-token', 240, function () {
            try {
                $response = Http::asForm()->timeout(20)->post(
                    (string) config('services.iotec.auth_url'),
                    [
                        'client_id' => config('services.iotec.client_id'),
                        'client_secret' => config('services.iotec.client_secret'),
                        'grant_type' => 'client_credentials',
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('ioTec auth failed', ['error' => $e->getMessage()]);
                throw new IotecPayException('Could not reach the payment provider. Check your connection and try again.');
            }

            $token = $response->json('access_token');

            if (! $response->successful() || ! $token) {
                Log::warning('ioTec auth rejected', ['http' => $response->status()]);
                throw new IotecPayException('Payment provider rejected our credentials. Please confirm on WhatsApp and we will sort it immediately.');
            }

            return $token;
        });
    }
}
