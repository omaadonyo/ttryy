<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceReminderMail;
use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\BtContact;
use App\Models\BtCounter;
use App\Models\BtDocument;
use App\Models\BtDocumentLine;
use App\Models\BtExpense;
use App\Models\BtItem;
use App\Models\BtStockMove;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payslip;
use App\Models\Reminder;
use App\Models\Suggestion;
use App\Models\SuggestionVote;
use App\Models\User;
use App\Notifications\SiteAlert;
use App\Services\Accounting\ChartOfAccounts;
use App\Services\Accounting\Ledger;
use App\Services\ActivityLogger;
use App\Services\BusinessContext;
use App\Services\BusinessPlan;
use App\Services\CashWallet;
use App\Services\Currency\ExchangeRates;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Backs the standalone Business Tools single-page app.
 *
 * Every presentation route renders the same Alpine shell (tools.app) seeded
 * with a per-business bootstrap payload, so navigation is instant and all
 * filtering/searching happens in the browser. Mutations reuse the same
 * endpoints and return JSON to scripts (via expectsJson) while still
 * redirecting for classic form posts, which keeps the feature robust and
 * fully testable.
 *
 * All records belong to a Business (the tenant). The owner and any staff
 * they invite share the same business data; every read and write is scoped
 * to the active business so nothing leaks between tenants.
 */
class BusinessToolsController extends Controller
{
    /** Which SPA tab each document type lives on. */
    private const TYPE_TABS = [
        BtDocument::QUOTATION => 'quotations',
        BtDocument::SALES_ORDER => 'salesorders',
        BtDocument::DELIVERY_NOTE => 'deliverynotes',
        BtDocument::INVOICE => 'invoices',
        BtDocument::RECEIPT => 'receipts',
        BtDocument::CREDIT_NOTE => 'creditnotes',
        BtDocument::PURCHASE_QUOTE => 'purchasequotes',
        BtDocument::PURCHASE_ORDER => 'purchaseorders',
        BtDocument::PURCHASE_INVOICE => 'purchaseinvoices',
        BtDocument::PURCHASE_PAYMENT => 'purchasepayments',
    ];

    /**
     * Allowed "convert to" targets per document type, mirroring how a bookkeeping
     * package lets a document flow through the sales or purchase cycle.
     */
    private const CONVERSIONS = [
        BtDocument::QUOTATION => [BtDocument::SALES_ORDER, BtDocument::INVOICE, BtDocument::DELIVERY_NOTE],
        BtDocument::SALES_ORDER => [BtDocument::INVOICE, BtDocument::DELIVERY_NOTE],
        BtDocument::DELIVERY_NOTE => [BtDocument::INVOICE],
        BtDocument::INVOICE => [BtDocument::DELIVERY_NOTE],
        BtDocument::PURCHASE_QUOTE => [BtDocument::PURCHASE_ORDER, BtDocument::PURCHASE_INVOICE],
        BtDocument::PURCHASE_ORDER => [BtDocument::PURCHASE_INVOICE],
    ];

    /** Types that can be duplicated into a fresh draft/active copy. */
    private const CLONEABLE = [
        BtDocument::QUOTATION, BtDocument::SALES_ORDER, BtDocument::DELIVERY_NOTE, BtDocument::INVOICE,
        BtDocument::PURCHASE_QUOTE, BtDocument::PURCHASE_ORDER, BtDocument::PURCHASE_INVOICE,
    ];

    /** Document types that move inventory when they are first raised in a chain. */
    private const STOCK_TYPES = [BtDocument::INVOICE, BtDocument::DELIVERY_NOTE];

    /* ------------------------------ context ------------------------------ */

    private function business(): Business
    {
        return app(BusinessContext::class)->forUser(request()->user());
    }

    private function bid(): int
    {
        return $this->business()->id;
    }

    private function isOwner(): bool
    {
        return $this->business()->isOwner(request()->user());
    }

    /* ------------------------------ helpers ------------------------------ */

    private function owned(BtDocument|BtContact|BtItem|BtExpense|Account|BankAccount|BankTransaction|Employee|Payslip $model): void
    {
        $mine = $model->business_id === null && (int) $model->user_id === request()->user()->id;

        abort_if((int) $model->business_id !== $this->bid() && ! $mine, 403);
    }

    private function ledger(): Ledger
    {
        return app(Ledger::class);
    }

    private function chart(): ChartOfAccounts
    {
        return app(ChartOfAccounts::class);
    }

    private function settings(): Business
    {
        return $this->business();
    }

    /** Record an audit-trail entry for the current business. */
    private function log(string $action, string $description, ?object $subject = null): void
    {
        app(ActivityLogger::class)->log($this->bid(), request()->user()->id, $action, $description, $subject);
    }

    /** Fan a notification out to everyone working in the business. */
    private function notifyBusiness(string $title, string $body, ?string $url = null, string $kind = 'info'): void
    {
        foreach ($this->business()->users as $member) {
            $member->notify(new SiteAlert($title, $body, $url, $kind));
        }
    }

    /** Success: JSON for the SPA, flash + redirect for classic forms. */
    private function ok(Request $request, string $message, array $data = [])
    {
        if ($request->expectsJson()) {
            return response()->json(array_merge(['message' => $message], $data));
        }

        return back()->with('status', $message);
    }

    /** Logic error: 422 for the SPA, validation flash for classic forms. */
    private function fail(Request $request, string $field, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'errors' => [$field => [$message]]], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }

    /**
     * Free-plan guard. Returns a 422 response when a cap has been reached,
     * or null when the action is allowed.
     */
    private function guardLimit(Request $request, string $key, int $used)
    {
        $limit = BusinessPlan::limits($this->business())[$key] ?? null;

        if ($limit === null || $used < $limit) {
            return null;
        }

        return $this->fail($request, $key, 'The free plan allows '.$this->limitLabel($key, $limit).'. Upgrade to Business Tools Premium for unlimited use.');
    }

    private function limitLabel(string $key, int $limit): string
    {
        return match ($key) {
            'businesses' => $limit.' business'.($limit === 1 ? '' : 'es'),
            'staff' => $limit.' team member'.($limit === 1 ? '' : 's'),
            'items' => $limit.' product'.($limit === 1 ? '' : 's'),
            'documents' => $limit.' document of each type',
            default => (string) $limit,
        };
    }

    /** How many documents of a type already exist for the active business. */
    private function docsOfType(string $type): int
    {
        return BtDocument::where('business_id', $this->bid())->where('type', $type)->count();
    }

    /**
     * Document cap: the free plan allows one document of each type. Premium
     * returns null so every type is unlimited.
     */
    private function guardDocLimit(Request $request, string $type)
    {
        if (BusinessPlan::isPremium($this->business())) {
            return null;
        }

        if ($this->docsOfType($type) < BusinessPlan::FREE_MAX_DOCS) {
            return null;
        }

        return $this->fail($request, 'documents', 'The free plan allows '.BusinessPlan::FREE_MAX_DOCS.' document of each type. Upgrade to Business Tools Premium for unlimited documents.');
    }

    /**
     * Build validated line rows from input. Amounts are recomputed here —
     * client totals are display only and never trusted.
     *
     * @return array{0: array, 1: int} [rows, subtotal]
     */
    private function buildLines(array $input, int $businessId, bool $purchase = false): array
    {
        $rows = [];
        $subtotal = 0;

        foreach ($input as $line) {
            if (! is_array($line)) {
                continue;
            }

            $item = null;
            if (! empty($line['item_id'])) {
                $item = BtItem::where('business_id', $businessId)->find($line['item_id']);
            }

            $description = trim((string) ($line['description'] ?? $item?->name ?? ''));

            if ($description === '') {
                continue;
            }

            $qty = max(0.01, (float) ($line['qty'] ?? 1));
            $fallback = $purchase ? (int) ($item?->cost_price ?? 0) : (int) ($item?->sale_price ?? 0);
            $unitPrice = isset($line['unit_price']) && $line['unit_price'] !== ''
                ? max(0, (int) $line['unit_price'])
                : $fallback;

            $lineTotal = (int) round($qty * $unitPrice);

            $rows[] = [
                'item_id' => $item?->id,
                'account_id' => ! empty($line['account_id']) ? (int) $line['account_id'] : null,
                'description' => mb_substr($description, 0, 255),
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'unit_cost' => (int) ($item?->cost_price ?? 0),
                'line_total' => $lineTotal,
            ];
            $subtotal += $lineTotal;
        }

        return [$rows, $subtotal];
    }

    private function applyTotals(BtDocument $doc, int $subtotal, int $discount, float $taxRate): void
    {
        $discount = min(max(0, $discount), $subtotal);
        $taxRate = min(max(0, $taxRate), 100);
        $taxable = $subtotal - $discount;
        $tax = (int) round($taxable * $taxRate / 100);

        $doc->subtotal = $subtotal;
        $doc->discount = $discount;
        $doc->tax_rate = $taxRate;
        $doc->tax_amount = $tax;
        $doc->total = $taxable + $tax;
    }

    private function decrementStock(BtDocument $doc, string $ref): void
    {
        foreach ($doc->lines()->whereNotNull('item_id')->get() as $line) {
            $item = BtItem::where('business_id', $doc->business_id)->find($line->item_id);

            if (! $item) {
                continue;
            }

            $item->decrement('stock', (int) $line->qty);
            BtStockMove::create([
                'user_id' => $doc->user_id,
                'business_id' => $doc->business_id,
                'item_id' => $item->id,
                'qty_change' => -(int) $line->qty,
                'reason' => $ref,
                'ref_type' => 'document',
                'ref_id' => $doc->id,
            ]);
        }
    }

    private function restoreStock(BtDocument $doc, string $ref): void
    {
        foreach ($doc->lines()->whereNotNull('item_id')->get() as $line) {
            $item = BtItem::where('business_id', $doc->business_id)->find($line->item_id);

            if (! $item) {
                continue;
            }

            $item->increment('stock', (int) $line->qty);
            BtStockMove::create([
                'user_id' => $doc->user_id,
                'business_id' => $doc->business_id,
                'item_id' => $item->id,
                'qty_change' => (int) $line->qty,
                'reason' => $ref,
                'ref_type' => 'document',
                'ref_id' => $doc->id,
            ]);
        }
    }

    /** Goods received against a purchase bill — the reverse of a sale. */
    private function receiveStock(BtDocument $doc, string $ref): void
    {
        foreach ($doc->lines()->whereNotNull('item_id')->get() as $line) {
            $item = BtItem::where('business_id', $doc->business_id)->find($line->item_id);

            if (! $item) {
                continue;
            }

            $item->increment('stock', (int) $line->qty);
            BtStockMove::create([
                'user_id' => $doc->user_id,
                'business_id' => $doc->business_id,
                'item_id' => $item->id,
                'qty_change' => (int) $line->qty,
                'reason' => $ref,
                'ref_type' => 'document',
                'ref_id' => $doc->id,
            ]);
        }
    }

    /**
     * Walk a document's conversion ancestry (the documents it was converted
     * from) and report whether any of them — optionally including itself — is
     * an invoice or delivery note. Inventory must only ever move once per
     * sales chain, so the first such document takes the stock and everything
     * downstream (invoice ⇄ delivery note) leaves it untouched.
     */
    private function chainHasFulfillment(BtDocument $doc, bool $includeSelf = true): bool
    {
        if ($includeSelf && in_array($doc->type, self::STOCK_TYPES, true)) {
            return true;
        }

        $cursor = $doc;
        $guard = 0;

        while ($cursor->converted_from_id && $guard++ < 20) {
            $cursor = BtDocument::where('business_id', $doc->business_id)->find($cursor->converted_from_id);

            if (! $cursor) {
                break;
            }

            if (in_array($cursor->type, self::STOCK_TYPES, true)) {
                return true;
            }
        }

        return false;
    }

    /** Whether the given document is the one that moved stock in its chain. */
    private function movedStock(BtDocument $doc): bool
    {
        return in_array($doc->type, self::STOCK_TYPES, true) && ! $this->chainHasFulfillment($doc, false);
    }

    /** Whether any ancestor is already an invoice (so revenue must not post twice). */
    private function chainHasInvoice(BtDocument $doc): bool
    {
        $cursor = $doc;
        $guard = 0;

        while ($cursor->converted_from_id && $guard++ < 20) {
            $cursor = BtDocument::where('business_id', $doc->business_id)->find($cursor->converted_from_id);

            if (! $cursor) {
                break;
            }

            if ($cursor->type === BtDocument::INVOICE) {
                return true;
            }
        }

        return false;
    }

    /** Fail if any line exceeds available stock. Returns the first offender. */
    private function checkStock(array $rows, int $businessId): ?string
    {
        $need = [];

        foreach ($rows as $row) {
            if (! $row['item_id']) {
                continue;
            }
            $need[$row['item_id']] = ($need[$row['item_id']] ?? 0) + (int) $row['qty'];
        }

        foreach ($need as $itemId => $qty) {
            $item = BtItem::where('business_id', $businessId)->find($itemId);

            if ($item && $item->stock < $qty) {
                return "{$item->name} has only {$item->stock} {$item->unit} in stock.";
            }
        }

        return null;
    }

    private function newDocument(string $type, array $attrs): BtDocument
    {
        $userId = request()->user()->id;

        return BtDocument::create(array_merge($attrs, [
            'user_id' => $userId,
            'business_id' => $this->bid(),
            'type' => $type,
            'number' => BtCounter::nextNo($this->bid(), $userId, $type, BtDocument::PREFIXES[$type]),
            'public_token' => Str::random(32),
        ]));
    }

    private function qrDataUri(BtDocument $doc): string
    {
        $url = route('tools.verify', ['doc' => $doc->id, 'token' => $doc->public_token]);
        $result = (new PngWriter)->write(new QrCode(data: $url, size: 200, margin: 6));

        return $result->getDataUri();
    }

    /* ------------------------------ serializers ------------------------------ */

    private function contactJson(BtContact $c, int $balance = 0): array
    {
        return [
            'id' => $c->id,
            'kind' => $c->kind,
            'name' => $c->name,
            'phone' => $c->phone,
            'email' => $c->email,
            'address' => $c->address,
            'notes' => $c->notes,
            'balance_owed' => $balance,
        ];
    }

    private function itemJson(BtItem $i): array
    {
        return [
            'id' => $i->id,
            'name' => $i->name,
            'sku' => $i->sku,
            'unit' => $i->unit,
            'cost_price' => (int) $i->cost_price,
            'sale_price' => (int) $i->sale_price,
            'stock' => (int) $i->stock,
            'reorder_level' => (int) $i->reorder_level,
            'low' => $i->isLow(),
            'stock_value' => $i->stockValue(),
        ];
    }

    private function expenseJson(BtExpense $e): array
    {
        return [
            'id' => $e->id,
            'date' => $e->date?->toDateString(),
            'category' => $e->category,
            'amount' => (int) $e->amount,
            'status' => $e->status,
            'contact_id' => $e->contact_id,
            'supplier_name' => $e->supplier?->name,
            'note' => $e->note,
        ];
    }

    private function docJson(BtDocument $d, bool $withLines = false): array
    {
        $data = [
            'id' => $d->id,
            'type' => $d->type,
            'label' => $d->label(),
            'number' => $d->number,
            'contact_id' => $d->contact_id,
            'contact_name' => $d->contact?->name,
            'issue_date' => $d->issue_date?->toDateString(),
            'due_date' => $d->due_date?->toDateString(),
            'valid_until' => $d->valid_until?->toDateString(),
            'status' => $d->status,
            'subtotal' => (int) $d->subtotal,
            'discount' => (int) $d->discount,
            'tax_rate' => (float) $d->tax_rate,
            'tax_amount' => (int) $d->tax_amount,
            'total' => (int) $d->total,
            'paid_amount' => (int) $d->paid_amount,
            'balance' => $d->balance(),
            'invoice_id' => $d->invoice_id,
            'converted_from_id' => $d->converted_from_id,
            'converted_from_number' => $d->convertedFrom?->number,
            'chain_from' => $d->chainFrom(),
            'bank_account_id' => $d->bank_account_id,
            'direction' => $d->isPurchase() ? 'purchase' : 'sale',
            'is_bill' => $d->isBill(),
            'payment_method' => $d->payment_method,
            'notes' => $d->notes,
            'overdue' => $d->isOverdue(),
            'lines_count' => $d->lines_count ?? $d->lines()->count(),
            'created_at' => $d->created_at?->toDateString(),
            'last_receipt_date' => $d->isBill() ? $d->latestReceipt?->issue_date?->toDateString() : null,
            'pdf_url' => route('tools.doc.pdf', $d),
        ];

        if ($withLines) {
            $data['lines'] = $d->lines->map(fn ($l) => [
                'id' => $l->id,
                'item_id' => $l->item_id,
                'account_id' => $l->account_id,
                'description' => $l->description,
                'qty' => (float) $l->qty,
                'unit_price' => (int) $l->unit_price,
                'line_total' => (int) $l->line_total,
            ])->values()->all();

            $data['receipts'] = $d->isBill()
                ? $d->receipts()->orderBy('created_at')->get()->map(fn ($r) => [
                    'id' => $r->id,
                    'number' => $r->number,
                    'total' => (int) $r->total,
                    'payment_method' => $r->payment_method,
                    'issue_date' => $r->issue_date?->toDateString(),
                ])->values()->all()
                : [];
        }

        return $data;
    }

    private function reminderJson(Reminder $r): array
    {
        return [
            'id' => $r->id,
            'document_id' => $r->document_id,
            'document_number' => $r->document?->number,
            'contact_name' => $r->document?->contact?->name,
            'channel' => $r->channel,
            'recipient' => $r->recipient,
            'message' => $r->message,
            'sent_at' => $r->sent_at?->toDateTimeString(),
            'sent_by' => $r->user?->name,
            'created_at' => $r->created_at?->toDateTimeString(),
        ];
    }

    private function activityJson(ActivityLog $a): array
    {
        return [
            'id' => $a->id,
            'action' => $a->action,
            'description' => $a->description,
            'user_name' => $a->user?->name ?? 'System',
            'ip' => $a->ip,
            'created_at' => $a->created_at?->toDateTimeString(),
        ];
    }

    private function staffJson(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->pivot->role ?? 'staff',
            'is_owner' => $this->business()->isOwner($u),
            'last_login_at' => $u->last_login_at?->toDateTimeString(),
            'last_login_ip' => $u->last_login_ip,
        ];
    }

    private function businessJson(Business $b): array
    {
        return [
            'id' => $b->id,
            'name' => $b->name,
            'role' => $b->pivot->role ?? ($b->owner_id === request()->user()->id ? 'owner' : 'staff'),
            'active' => (int) $b->id === (int) request()->user()->active_business_id,
            'is_owner' => $b->owner_id === request()->user()->id,
        ];
    }

    private function accountJson(Account $a, array $moves = []): array
    {
        $debit = (int) ($moves['debit'] ?? 0);
        $credit = (int) ($moves['credit'] ?? 0);

        return [
            'id' => $a->id,
            'code' => $a->code,
            'name' => $a->name,
            'type' => $a->type,
            'subtype' => $a->subtype,
            'is_system' => $a->is_system,
            'is_active' => $a->is_active,
            'opening_balance' => (int) $a->opening_balance,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $a->signedBalance($debit, $credit),
        ];
    }

    private function bankJson(BankAccount $b, ?int $movement = null): array
    {
        return [
            'id' => $b->id,
            'name' => $b->name,
            'type' => $b->type,
            'account_number' => $b->account_number,
            'account_id' => $b->account_id,
            'opening_balance' => (int) $b->opening_balance,
            'balance' => (int) $b->opening_balance + ($movement ?? (int) $b->transactions()->sum('amount')),
            'transactions_count' => $b->transactions()->count(),
        ];
    }

    private function bankTxJson(BankTransaction $t): array
    {
        return [
            'id' => $t->id,
            'bank_account_id' => $t->bank_account_id,
            'date' => $t->date?->toDateString(),
            'description' => $t->description,
            'amount' => (int) $t->amount,
            'type' => $t->type,
            'reference' => $t->reference,
        ];
    }

    private function employeeJson(Employee $e): array
    {
        return [
            'id' => $e->id,
            'name' => $e->name,
            'email' => $e->email,
            'phone' => $e->phone,
            'position' => $e->position,
            'employment_type' => $e->employment_type,
            'base_salary' => (int) $e->base_salary,
            'pay_frequency' => $e->pay_frequency,
            'hired_on' => $e->hired_on?->toDateString(),
            'is_active' => $e->is_active,
            'notes' => $e->notes,
            'payslips_count' => $e->payslips()->count(),
        ];
    }

    private function payslipJson(Payslip $p): array
    {
        return [
            'id' => $p->id,
            'number' => $p->number,
            'employee_id' => $p->employee_id,
            'employee_name' => $p->employee?->name,
            'period_start' => $p->period_start?->toDateString(),
            'period_end' => $p->period_end?->toDateString(),
            'gross' => (int) $p->gross,
            'allowances' => (int) $p->allowances,
            'deductions' => (int) $p->deductions,
            'net' => (int) $p->net,
            'status' => $p->status,
            'paid_on' => $p->paid_on?->toDateString(),
        ];
    }

    private function journalJson(JournalEntry $e, bool $withLines = false): array
    {
        $data = [
            'id' => $e->id,
            'date' => $e->date?->toDateString(),
            'reference' => $e->reference,
            'memo' => $e->memo,
            'source_type' => $e->source_type,
            'total' => $e->totalDebit(),
            'lines_count' => $e->lines->count(),
        ];

        if ($withLines) {
            $data['lines'] = $e->lines->map(fn ($l) => [
                'account' => $l->account?->name,
                'code' => $l->account?->code,
                'debit' => (int) $l->debit,
                'credit' => (int) $l->credit,
            ])->values()->all();
        }

        return $data;
    }

    /** All account movements for a business, keyed by account id. */
    private function accountMoves(int $bid): array
    {
        return JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.business_id', $bid)
            ->groupBy('journal_lines.account_id')
            ->selectRaw('journal_lines.account_id as aid, COALESCE(SUM(journal_lines.debit), 0) as debit, COALESCE(SUM(journal_lines.credit), 0) as credit')
            ->get()
            ->keyBy('aid')
            ->map(fn ($r) => ['debit' => (int) $r->debit, 'credit' => (int) $r->credit])
            ->all();
    }

    /** Serialise the chart of accounts with live balances. */
    private function accountList(Business $business, int $bid): array
    {
        $this->chart()->ensure($business);
        $moves = $this->accountMoves($bid);

        return $business->accounts()->orderBy('code')->get()
            ->map(fn ($a) => $this->accountJson($a, $moves[$a->id] ?? []))->all();
    }

    /* ------------------------------ shell + bootstrap ------------------------------ */

    private function shell(Request $request, string $tab, array $extra = [])
    {
        return view('tools.app', [
            'boot' => array_merge($this->bootstrap($request), [
                'startTab' => $tab,
                'compose' => false,
                'composeType' => null,
                'openDoc' => null,
            ], $extra),
        ]);
    }

    private function bootstrap(Request $request): array
    {
        $user = $request->user();
        $bid = $this->bid();
        $business = $this->business();
        $premium = BusinessPlan::isPremium($business);

        // Open-invoice balance per contact, in one query.
        $balances = BtDocument::where('business_id', $bid)
            ->where('type', BtDocument::INVOICE)
            ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
            ->selectRaw('contact_id, COALESCE(SUM(total - paid_amount), 0) as b')
            ->groupBy('contact_id')->pluck('b', 'contact_id');

        $contacts = BtContact::where('business_id', $bid)->orderBy('name')->get()
            ->map(fn ($c) => $this->contactJson($c, (int) ($balances[$c->id] ?? 0)))->all();

        $items = BtItem::where('business_id', $bid)->orderBy('name')->get()
            ->map(fn ($i) => $this->itemJson($i))->all();

        $expenses = BtExpense::where('business_id', $bid)->with('supplier')->latest('date')->get()
            ->map(fn ($e) => $this->expenseJson($e))->all();

        $documents = BtDocument::where('business_id', $bid)->with('contact')->with('latestReceipt')
            ->withCount('lines')->latest()->take(400)->get()
            ->map(fn ($d) => $this->docJson($d))->all();

        [$from, $to] = [now()->startOfMonth()->toDateString(), now()->toDateString()];

        return [
            'user' => ['name' => $user->name, 'email' => $user->email, 'is_owner' => $business->isOwner($user)],
            'business' => ['id' => $business->id, 'name' => $business->name],
            'businesses' => $user->businesses()->orderBy('name')->get()->map(fn ($b) => $this->businessJson($b))->all(),
            'staff' => $business->users()->orderBy('name')->get()->map(fn ($u) => $this->staffJson($u))->all(),
            'settings' => [
                'business_name' => $business->name,
                'phone' => $business->phone,
                'email' => $business->email,
                'address' => $business->address,
                'currency' => $business->currency ?: 'USD',
                'tax_rate' => (float) $business->tax_rate,
                'receipt_footer' => $business->receipt_footer,
                'quotation_footer' => $business->quotation_footer,
                'invoice_footer' => $business->invoice_footer,
            ],
            'contacts' => $contacts,
            'items' => $items,
            'expenses' => $expenses,
            'documents' => $documents,
            'accounts' => $this->accountList($business, $bid),
            'bankAccounts' => $business->bankAccounts()->withSum('transactions as tx_sum', 'amount')
                ->orderBy('name')->get()->map(fn ($b) => $this->bankJson($b, $b->tx_sum !== null ? (int) $b->tx_sum : null))->all(),
            'bankTransactions' => BankTransaction::where('business_id', $bid)->latest('date')->latest('id')->take(300)->get()
                ->map(fn ($t) => $this->bankTxJson($t))->all(),
            'employees' => $business->employees()->orderBy('name')->get()->map(fn ($e) => $this->employeeJson($e))->all(),
            'payslips' => Payslip::where('business_id', $bid)->with('employee')->latest('period_end')->take(200)->get()
                ->map(fn ($p) => $this->payslipJson($p))->all(),
            'journal' => JournalEntry::where('business_id', $bid)->with('lines')->latest('date')->latest('id')->take(120)->get()
                ->map(fn ($e) => $this->journalJson($e, true))->all(),
            'reminders' => Reminder::where('business_id', $bid)->with(['document.contact', 'user'])
                ->latest()->take(60)->get()->map(fn ($r) => $this->reminderJson($r))->all(),
            'activity' => $premium
                ? ActivityLog::where('business_id', $bid)->with('user')
                    ->latest()->take(80)->get()->map(fn ($a) => $this->activityJson($a))->all()
                : $this->dummyActivity(),
            'notifications' => $this->notificationData($user),
            'stats' => $this->stats($bid),
            'months' => $this->months($bid),
            'report' => ($premium ? $this->reportData($bid, $from, $to) : $this->dummyReport()) + ['from' => $from, 'to' => $to],
            'plan' => $this->planPayload($business, $user, $premium),
            'suggestions' => $this->suggestionList($user),
            'verifyBase' => url('/tools/verify'),
            'fx' => app(ExchangeRates::class)->snapshot(),
            'conversions' => self::CONVERSIONS,
            'cloneable' => self::CLONEABLE,
            'routes' => $this->routes(),
        ];
    }

    private function notificationData(User $user, int $limit = 20): array
    {
        return [
            'unread' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()->take($limit)->get()->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notification',
                'body' => $n->data['body'] ?? '',
                'kind' => $n->data['kind'] ?? 'info',
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at?->diffForHumans(),
            ])->values()->all(),
        ];
    }

    /**
     * Plan snapshot for the header widget: which caps apply, how much is used
     * and what an upgrade costs.
     */
    private function planPayload(Business $business, User $user, ?bool $premium = null): array
    {
        $premium ??= BusinessPlan::isPremium($business);

        return [
            'premium' => $premium,
            'plan' => $business->plan ?: BusinessPlan::FREE,
            'until' => $business->premium_until?->toIso8601String(),
            'until_human' => $business->premium_until?->diffForHumans(),
            'days_left' => $business->premium_until ? max(0, (int) now()->diffInDays($business->premium_until, false)) : 0,
            'limits' => BusinessPlan::limits($business),
            'usage' => BusinessPlan::usage($business),
            'prices' => BusinessPlan::PRICES,
            'premium_tabs' => BusinessPlan::PREMIUM_TABS,
            'wallet' => (int) $user->balance_ugx,
            'last' => $business->subscriptions()->latest()->first()?->only(['cycle', 'amount', 'starts_at', 'ends_at', 'source']),
        ];
    }

    /**
     * Neutral figures shown in place of a premium report. Nothing here reflects
     * real activity, so the blurred preview cannot be copied.
     */
    private function dummyReport(): array
    {
        $cash = 5240000;
        $receivables = 1620000;
        $inventory = 2410000;
        $payables = 890000;
        $assets = $cash + $receivables + $inventory;

        return [
            'cashflow' => [
                'inflows' => [
                    ['number' => 'RCT-0001', 'date' => now()->subDays(12)->toDateString(), 'contact' => 'Sample Customer', 'amount' => 1850000],
                    ['number' => 'RCT-0002', 'date' => now()->subDays(6)->toDateString(), 'contact' => 'Sample Customer', 'amount' => 1240000],
                    ['number' => 'RCT-0003', 'date' => now()->subDays(2)->toDateString(), 'contact' => 'Sample Customer', 'amount' => 980000],
                ],
                'outflows' => [
                    ['category' => 'Transport', 'date' => now()->subDays(10)->toDateString(), 'amount' => 320000],
                    ['category' => 'Rent', 'date' => now()->subDays(8)->toDateString(), 'amount' => 750000],
                    ['category' => 'Utilities', 'date' => now()->subDays(4)->toDateString(), 'amount' => 210000],
                ],
                'in_total' => 4070000, 'out_total' => 1280000, 'net' => 2790000,
            ],
            'pnl' => [
                'revenue' => 4070000, 'cogs' => 1480000, 'gross' => 2590000,
                'expense_total' => 1280000, 'net' => 1310000,
                'by_category' => [
                    ['category' => 'Rent', 'amount' => 750000],
                    ['category' => 'Transport', 'amount' => 320000],
                    ['category' => 'Utilities', 'amount' => 210000],
                ],
                'paid_count' => 6,
            ],
            'balance' => [
                'cash' => $cash, 'receivables' => $receivables, 'inventory' => $inventory,
                'assets' => $assets, 'payables' => $payables, 'equity' => $assets - $payables,
            ],
            'ledger' => [
                'rows' => [
                    ['id' => -1, 'code' => '1000', 'name' => 'Cash on hand', 'type' => 'asset', 'debit' => 5240000, 'credit' => 0],
                    ['id' => -2, 'code' => '1100', 'name' => 'Accounts receivable', 'type' => 'asset', 'debit' => 1620000, 'credit' => 0],
                    ['id' => -3, 'code' => '1200', 'name' => 'Inventory', 'type' => 'asset', 'debit' => 2410000, 'credit' => 0],
                    ['id' => -5, 'code' => '5000', 'name' => 'Cost of goods sold', 'type' => 'expense', 'debit' => 1480000, 'credit' => 0],
                    ['id' => -6, 'code' => '6000', 'name' => 'Operating expenses', 'type' => 'expense', 'debit' => 1280000, 'credit' => 0],
                    ['id' => -7, 'code' => '2000', 'name' => 'Accounts payable', 'type' => 'liability', 'debit' => 0, 'credit' => 890000],
                    ['id' => -4, 'code' => '4000', 'name' => 'Sales revenue', 'type' => 'income', 'debit' => 0, 'credit' => 4070000],
                    ['id' => -8, 'code' => '3000', 'name' => 'Owner equity', 'type' => 'equity', 'debit' => 0, 'credit' => 7070000],
                ],
                'debit_total' => 12030000,
                'credit_total' => 12030000,
            ],
        ];
    }

    /** Neutral audit-trail lines shown while the activity log is locked. */
    private function dummyActivity(): array
    {
        $rows = [
            ['Sample User', 'invoice.created', 'Created invoice INV-0001', 3],
            ['Sample User', 'receipt.created', 'Recorded a payment of UGX 1,850,000', 8],
            ['Sample User', 'item.created', 'Added item Cement 50kg', 15],
            ['Sample User', 'contact.created', 'Added customer Sample Customer', 26],
            ['Sample User', 'settings.updated', 'Updated business profile', 40],
            ['Sample User', 'staff.added', 'Added a teammate', 61],
        ];

        return array_map(fn ($r) => [
            'id' => -1 * ($r[3] + 1),
            'action' => $r[1],
            'description' => $r[2],
            'user' => $r[0],
            'created_at' => now()->subDays($r[3])->diffForHumans(),
        ], $rows);
    }

    private function routes(): array
    {
        return [
            'state' => route('tools.api.state'),
            'reports' => route('tools.api.reports'),
            'activity' => route('tools.api.activity'),
            'fx' => route('tools.api.fx'),
            'notifications' => route('tools.api.notifications'),
            'notificationsRead' => route('tools.notifications.read'),
            'wallet' => route('wallet.index'),
            'apiDoc' => route('tools.api.doc', ['doc' => '_ID_']),
            'settings' => route('tools.settings.update'),
            'businessStore' => route('tools.businesses.store'),
            'businessUpdate' => route('tools.businesses.update', ['business' => '_ID_']),
            'businessDestroy' => route('tools.businesses.destroy', ['business' => '_ID_']),
            'businessSwitch' => route('tools.businesses.switch', ['business' => '_ID_']),
            'subscribe' => route('tools.subscribe'),
            'suggestions' => route('tools.api.suggestions'),
            'suggestionStore' => route('tools.suggestions.store'),
            'suggestionVote' => route('tools.suggestions.vote', ['suggestion' => '_ID_']),
            'staffStore' => route('tools.staff.store'),
            'staffUpdate' => route('tools.staff.update', ['staff' => '_ID_']),
            'staffDestroy' => route('tools.staff.destroy', ['staff' => '_ID_']),
            'reminderStore' => route('tools.reminders.store'),
            'contactStore' => route('tools.contacts.store'),
            'contactUpdate' => route('tools.contacts.update', ['contact' => '_ID_']),
            'contactDestroy' => route('tools.contacts.destroy', ['contact' => '_ID_']),
            'itemStore' => route('tools.items.store'),
            'itemUpdate' => route('tools.items.update', ['item' => '_ID_']),
            'itemDestroy' => route('tools.items.destroy', ['item' => '_ID_']),
            'itemAdjust' => route('tools.items.adjust', ['item' => '_ID_']),
            'docStore' => route('tools.doc.store', ['type' => '_TYPE_']),
            'docDestroy' => route('tools.doc.destroy', ['doc' => '_ID_']),
            'docStatus' => route('tools.doc.status', ['doc' => '_ID_']),
            'docConvert' => route('tools.doc.convert', ['doc' => '_ID_']),
            'docClone' => route('tools.doc.clone', ['doc' => '_ID_']),
            'posCheckout' => route('tools.pos.checkout'),
            'apiItem' => route('tools.api.item', ['item' => '_ID_']),
            'expenseStore' => route('tools.expenses.store'),
            'expenseUpdate' => route('tools.expenses.update', ['expense' => '_ID_']),
            'expenseDestroy' => route('tools.expenses.destroy', ['expense' => '_ID_']),
            'accountStore' => route('tools.accounts.store'),
            'accountUpdate' => route('tools.accounts.update', ['account' => '_ID_']),
            'accountDestroy' => route('tools.accounts.destroy', ['account' => '_ID_']),
            'bankStore' => route('tools.bank.store'),
            'bankUpdate' => route('tools.bank.update', ['account' => '_ID_']),
            'bankDestroy' => route('tools.bank.destroy', ['account' => '_ID_']),
            'bankTxStore' => route('tools.bank.tx.store', ['account' => '_ID_']),
            'bankTxDestroy' => route('tools.bank.tx.destroy', ['transaction' => '_ID_']),
            'employeeStore' => route('tools.employees.store'),
            'employeeUpdate' => route('tools.employees.update', ['employee' => '_ID_']),
            'employeeDestroy' => route('tools.employees.destroy', ['employee' => '_ID_']),
            'payslipStore' => route('tools.payslips.store'),
            'payslipUpdate' => route('tools.payslips.update', ['payslip' => '_ID_']),
            'payslipDestroy' => route('tools.payslips.destroy', ['payslip' => '_ID_']),
        ];
    }

    private function stats(int $bid): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'collected_month' => (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::RECEIPT)
                ->where('created_at', '>=', $monthStart)->sum('total'),
            'receivable' => (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::INVOICE)
                ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
                ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')->value('b'),
            'expenses_month' => (int) BtExpense::where('business_id', $bid)->where('status', BtExpense::PAID)
                ->whereDate('date', '>=', $monthStart->toDateString())->sum('amount'),
            'cash' => (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::RECEIPT)->sum('total')
                - (int) BtExpense::where('business_id', $bid)->where('status', BtExpense::PAID)->sum('amount'),
            'low_stock' => BtItem::where('business_id', $bid)->whereColumn('stock', '<=', 'reorder_level')->count(),
            'pending_quotes' => BtDocument::where('business_id', $bid)->where('type', BtDocument::QUOTATION)
                ->whereIn('status', [BtDocument::DRAFT, BtDocument::SENT])->count(),
            'overdue' => BtDocument::where('business_id', $bid)->where('type', BtDocument::INVOICE)
                ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
                ->whereNotNull('due_date')->where('due_date', '<', now()->toDateString())->count(),
            'inventory_value' => (int) BtItem::where('business_id', $bid)
                ->selectRaw('COALESCE(SUM(stock * cost_price), 0) as v')->value('v'),
            'documents' => BtDocument::where('business_id', $bid)->count(),
            'customers' => BtContact::where('business_id', $bid)->where('kind', BtContact::CUSTOMER)->count(),
            'suppliers' => BtContact::where('business_id', $bid)->where('kind', BtContact::SUPPLIER)->count(),
            'staff' => $this->business()->users()->count(),
            'payables' => (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::PURCHASE_INVOICE)
                ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
                ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')->value('b')
                + (int) BtExpense::where('business_id', $bid)->where('status', BtExpense::UNPAID)->sum('amount'),
            'purchase_orders' => BtDocument::where('business_id', $bid)->where('type', BtDocument::PURCHASE_ORDER)
                ->whereIn('status', [BtDocument::DRAFT, BtDocument::SENT])->count(),
            'sales_orders' => BtDocument::where('business_id', $bid)->where('type', BtDocument::SALES_ORDER)
                ->whereIn('status', [BtDocument::DRAFT, BtDocument::SENT])->count(),
            'employees' => $this->business()->employees()->where('is_active', true)->count(),
        ];
    }

    private function months(int $bid): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $months[] = [
                'label' => $m->format('M'),
                'year' => $m->year,
                'in' => (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::RECEIPT)
                    ->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('total'),
                'out' => (int) BtExpense::where('business_id', $bid)->where('status', BtExpense::PAID)
                    ->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'),
            ];
        }

        return $months;
    }

    /** Cash flow, P&L and balance sheet numbers for a period. */
    private function reportData(int $bid, string $from, string $to): array
    {
        $inflows = BtDocument::where('business_id', $bid)->where('type', BtDocument::RECEIPT)
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->with('contact')->orderBy('created_at')->get();

        $outflows = BtExpense::where('business_id', $bid)->where('status', BtExpense::PAID)
            ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->orderBy('date')->get();

        $inTotal = (int) $inflows->sum('total');
        $outTotal = (int) $outflows->sum('amount');

        $revenue = (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::RECEIPT)
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->sum('total');

        $paidInvoices = BtDocument::where('business_id', $bid)->where('type', BtDocument::INVOICE)
            ->where('status', BtDocument::PAID)
            ->whereDate('updated_at', '>=', $from)->whereDate('updated_at', '<=', $to)
            ->with('lines')->get();

        $cogs = (int) $paidInvoices->flatMap->lines->sum(fn ($l) => $l->qty * $l->unit_cost);
        $expenses = BtExpense::where('business_id', $bid)->where('status', BtExpense::PAID)
            ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->get();
        $expenseTotal = (int) $expenses->sum('amount');

        $cash = (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::RECEIPT)->sum('total')
            - (int) BtExpense::where('business_id', $bid)->where('status', BtExpense::PAID)->sum('amount');
        $receivables = (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::INVOICE)
            ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')->value('b');
        $inventory = (int) BtItem::where('business_id', $bid)
            ->selectRaw('COALESCE(SUM(stock * cost_price), 0) as v')->value('v');
        $payables = (int) BtDocument::where('business_id', $bid)->where('type', BtDocument::PURCHASE_INVOICE)
            ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')->value('b')
            + (int) BtExpense::where('business_id', $bid)->where('status', BtExpense::UNPAID)->sum('amount');
        $assets = $cash + $receivables + $inventory;

        return [
            'cashflow' => [
                'inflows' => $inflows->map(fn ($r) => [
                    'number' => $r->number, 'date' => $r->created_at?->toDateString(),
                    'contact' => $r->contact?->name, 'amount' => (int) $r->total,
                ])->values()->all(),
                'outflows' => $outflows->map(fn ($e) => [
                    'category' => $e->category, 'date' => $e->date?->toDateString(), 'amount' => (int) $e->amount,
                ])->values()->all(),
                'in_total' => $inTotal, 'out_total' => $outTotal, 'net' => $inTotal - $outTotal,
            ],
            'pnl' => [
                'revenue' => $revenue, 'cogs' => $cogs, 'gross' => $revenue - $cogs,
                'expense_total' => $expenseTotal, 'net' => $revenue - $cogs - $expenseTotal,
                'by_category' => $expenses->groupBy('category')->map->sum('amount')->sortDesc()
                    ->map(fn ($v, $k) => ['category' => $k, 'amount' => (int) $v])->values()->all(),
                'paid_count' => $paidInvoices->count(),
            ],
            'balance' => [
                'cash' => $cash, 'receivables' => $receivables, 'inventory' => $inventory,
                'assets' => $assets, 'payables' => $payables, 'equity' => $assets - $payables,
            ],
            'ledger' => $this->trialBalance($bid),
        ];
    }

    /** A trial balance straight from the journal — debits must equal credits. */
    private function trialBalance(int $bid): array
    {
        $moves = $this->accountMoves($bid);
        $accounts = Account::where('business_id', $bid)->orderBy('code')->get();

        $rows = [];
        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($accounts as $account) {
            $m = $moves[$account->id] ?? ['debit' => 0, 'credit' => 0];
            $opening = (int) $account->opening_balance;

            $debit = (int) $m['debit'] + (Account::NORMAL[$account->type] === 'debit' ? max(0, $opening) : 0);
            $credit = (int) $m['credit'] + (Account::NORMAL[$account->type] === 'credit' ? max(0, $opening) : 0);

            if ($debit === 0 && $credit === 0) {
                continue;
            }

            $rows[] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'debit' => $debit,
                'credit' => $credit,
            ];
            $debitTotal += $debit;
            $creditTotal += $credit;
        }

        return ['rows' => $rows, 'debit_total' => $debitTotal, 'credit_total' => $creditTotal];
    }

    /* ------------------------------ presentation routes ------------------------------ */

    public function index(Request $request)
    {
        return $this->shell($request, 'overview');
    }

    public function settingsPage(Request $request)
    {
        return $this->shell($request, 'settings');
    }

    public function contacts(Request $request, string $kind)
    {
        abort_unless(in_array($kind, [BtContact::CUSTOMER, BtContact::SUPPLIER], true), 404);

        return $this->shell($request, $kind === BtContact::CUSTOMER ? 'customers' : 'suppliers');
    }

    public function inventory(Request $request)
    {
        return $this->shell($request, 'inventory');
    }

    public function quotations(Request $request)
    {
        return $this->shell($request, 'quotations');
    }

    public function invoices(Request $request)
    {
        return $this->shell($request, 'invoices');
    }

    public function receipts(Request $request)
    {
        return $this->shell($request, 'receipts');
    }

    public function createDoc(Request $request, string $type)
    {
        $type = str($type)->singular()->toString();
        abort_unless(in_array($type, BtDocument::TYPES, true), 404);

        return $this->shell($request, self::TYPE_TABS[$type], ['compose' => true, 'composeType' => $type]);
    }

    public function showDoc(BtDocument $doc)
    {
        $this->owned($doc);

        return $this->shell(request(), self::TYPE_TABS[$doc->type], ['openDoc' => $doc->id]);
    }

    public function pos(Request $request)
    {
        return $this->shell($request, 'pos');
    }

    public function expenses(Request $request)
    {
        return $this->shell($request, 'expenses');
    }

    public function reminders(Request $request)
    {
        return $this->shell($request, 'reminders');
    }

    public function staff(Request $request)
    {
        return $this->shell($request, 'staff');
    }

    public function activity(Request $request)
    {
        return $this->shell($request, 'activity');
    }

    public function notifications(Request $request)
    {
        return $this->shell($request, 'notifications');
    }

    public function cashflow(Request $request)
    {
        return $this->shell($request, 'cashflow');
    }

    public function pnl(Request $request)
    {
        return $this->shell($request, 'pnl');
    }

    public function balancesheet(Request $request)
    {
        return $this->shell($request, 'balancesheet');
    }

    public function salesOrders(Request $request)
    {
        return $this->shell($request, 'salesorders');
    }

    public function deliveryNotes(Request $request)
    {
        return $this->shell($request, 'deliverynotes');
    }

    public function creditNotes(Request $request)
    {
        return $this->shell($request, 'creditnotes');
    }

    public function purchaseQuotes(Request $request)
    {
        return $this->shell($request, 'purchasequotes');
    }

    public function purchaseOrders(Request $request)
    {
        return $this->shell($request, 'purchaseorders');
    }

    public function purchaseInvoices(Request $request)
    {
        return $this->shell($request, 'purchaseinvoices');
    }

    public function purchasePayments(Request $request)
    {
        return $this->shell($request, 'purchasepayments');
    }

    public function accounts(Request $request)
    {
        return $this->shell($request, 'accounts');
    }

    public function bank(Request $request)
    {
        return $this->shell($request, 'bank');
    }

    public function employees(Request $request)
    {
        return $this->shell($request, 'employees');
    }

    public function payslips(Request $request)
    {
        return $this->shell($request, 'payslips');
    }

    public function journal(Request $request)
    {
        return $this->shell($request, 'journal');
    }

    /* ------------------------------ api ------------------------------ */

    public function apiState(Request $request)
    {
        return response()->json($this->bootstrap($request));
    }

    public function apiReports(Request $request)
    {
        $from = (string) $request->query('from', now()->startOfMonth()->toDateString());
        $to = (string) $request->query('to', now()->toDateString());

        $data = BusinessPlan::isPremium($this->business())
            ? $this->reportData($this->bid(), $from, $to)
            : $this->dummyReport();

        return response()->json($data + ['from' => $from, 'to' => $to]);
    }

    public function apiFx(Request $request)
    {
        return response()->json([
            'fx' => app(ExchangeRates::class)->snapshot($request->boolean('fresh')),
        ]);
    }

    public function apiActivity(Request $request)
    {
        return response()->json([
            'activity' => BusinessPlan::isPremium($this->business())
                ? ActivityLog::where('business_id', $this->bid())->with('user')
                    ->latest()->take(120)->get()->map(fn ($a) => $this->activityJson($a))->all()
                : $this->dummyActivity(),
        ]);
    }

    public function apiNotifications(Request $request)
    {
        return response()->json(['notifications' => $this->notificationData($request->user(), 100)]);
    }

    public function readNotifications(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->ok($request, 'Notifications cleared.', ['notifications' => $this->notificationData($request->user(), 100)]);
    }

    public function apiItem(BtItem $item)
    {
        $this->owned($item);

        return response()->json([
            'item' => $this->itemJson($item),
            'moves' => BtStockMove::where('item_id', $item->id)->latest()->take(80)->get()->map(fn ($m) => [
                'id' => $m->id,
                'qty_change' => (int) $m->qty_change,
                'reason' => $m->reason,
                'ref_type' => $m->ref_type,
                'created_at' => $m->created_at?->toDateTimeString(),
            ])->values()->all(),
        ]);
    }

    public function apiDoc(BtDocument $doc)
    {
        $this->owned($doc);
        $doc->load(['lines', 'contact', 'receipts']);

        $data = $this->docJson($doc, true);
        $data['qr'] = $this->qrDataUri($doc);
        $data['verify_url'] = route('tools.verify', ['doc' => $doc->id, 'token' => $doc->public_token]);

        return response()->json(['document' => $data]);
    }

    /* ------------------------------ business + settings ------------------------------ */

    public function updateSettings(Request $request)
    {
        abort_unless($this->isOwner(), 403);

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:8'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
            'quotation_footer' => ['nullable', 'string', 'max:255'],
            'invoice_footer' => ['nullable', 'string', 'max:255'],
        ]);

        $business = $this->business();
        $business->update([
            'name' => $data['business_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'tax_rate' => $data['tax_rate'] ?? 0,
            'receipt_footer' => $data['receipt_footer'] ?? null,
            'quotation_footer' => $data['quotation_footer'] ?? null,
            'invoice_footer' => $data['invoice_footer'] ?? null,
        ]);

        $this->log('settings.updated', 'Updated business profile "'.$business->name.'"');

        return $this->ok($request, 'Business profile saved — it prints on every PDF.', [
            'settings' => [
                'business_name' => $business->name,
                'phone' => $business->phone,
                'email' => $business->email,
                'address' => $business->address,
                'currency' => $business->currency ?: 'USD',
                'tax_rate' => (float) $business->tax_rate,
                'receipt_footer' => $business->receipt_footer,
                'quotation_footer' => $business->quotation_footer,
                'invoice_footer' => $business->invoice_footer,
            ],
        ]);
    }

    /* ------------------------------ subscription ------------------------------ */

    /** Buy or extend Business Tools Premium from the cash wallet. */
    public function subscribe(Request $request)
    {
        $business = $this->business();

        abort_unless($business->isOwner($request->user()), 403);

        $data = $request->validate([
            'cycle' => ['required', Rule::in([BusinessSubscription::DAILY, BusinessSubscription::MONTHLY])],
        ]);

        $price = BusinessPlan::price($data['cycle']);

        if (! CashWallet::canAfford($request->user(), $price)) {
            return $this->fail($request, 'cycle', 'Top up your wallet with at least UGX '.number_format($price).' to start premium.');
        }

        $subscription = BusinessPlan::subscribe($business, $request->user(), $data['cycle']);

        $this->log('subscription.started', 'Premium activated ('.$data['cycle'].') — '.$subscription->ends_at->toFormattedDateString());

        return $this->ok($request, 'Premium active until '.$subscription->ends_at->toFormattedDateString().'.', [
            'state' => $this->bootstrap($request),
        ]);
    }

    /* ------------------------------ suggestions ------------------------------ */

    public function suggestions(Request $request)
    {
        return response()->json(['suggestions' => $this->suggestionList($request->user())]);
    }

    public function storeSuggestion(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $suggestion = Suggestion::create([
            'user_id' => $request->user()->id,
            'business_id' => $this->bid(),
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'status' => Suggestion::NEW,
        ]);

        SuggestionVote::firstOrCreate(['suggestion_id' => $suggestion->id, 'user_id' => $request->user()->id]);
        $suggestion->forceFill(['votes' => $suggestion->ballots()->count()])->save();

        return $this->ok($request, 'Idea shared — thanks!', [
            'suggestions' => $this->suggestionList($request->user()),
        ]);
    }

    public function voteSuggestion(Request $request, Suggestion $suggestion)
    {
        $user = $request->user();
        $existing = SuggestionVote::where('suggestion_id', $suggestion->id)->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
        } else {
            SuggestionVote::create(['suggestion_id' => $suggestion->id, 'user_id' => $user->id]);
        }

        $suggestion->forceFill(['votes' => $suggestion->ballots()->count()])->save();

        return $this->ok($request, $existing ? 'Vote removed.' : 'Voted.', [
            'suggestions' => $this->suggestionList($user),
        ]);
    }

    private function suggestionList(User $user, int $limit = 60): array
    {
        $myVotes = SuggestionVote::where('user_id', $user->id)->pluck('suggestion_id')->all();

        return Suggestion::with('user')->latest()->take($limit)->get()->map(fn ($s) => [
            'id' => $s->id,
            'title' => $s->title,
            'body' => $s->body,
            'status' => $s->status,
            'reply' => $s->reply,
            'votes' => (int) $s->votes,
            'voted' => in_array($s->id, $myVotes, true),
            'author' => $s->user?->name ?? 'Someone',
            'mine' => $s->user_id === $user->id,
            'created_at' => $s->created_at?->diffForHumans(),
        ])->values()->all();
    }

    public function storeBusiness(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:8'],
        ]);

        $owned = Business::where('owner_id', $request->user()->id)->count();

        if ($blocked = $this->guardLimit($request, 'businesses', $owned)) {
            return $blocked;
        }

        $current = $this->business();
        $keepPremium = BusinessPlan::isPremium($current);

        $business = app(BusinessContext::class)->create($request->user(), $data['name']);
        $business->update(collect($data)->except('name')->filter()->all());

        // A premium account keeps its plan across the businesses it opens.
        if ($keepPremium) {
            $business->forceFill(['plan' => BusinessPlan::PREMIUM, 'premium_until' => $current->premium_until])->save();
        }

        $request->user()->forceFill(['active_business_id' => $business->id])->save();
        $this->log('business.created', 'Created business "'.$business->name.'"', $business);

        return $this->ok($request, 'Business "'.$business->name.'" created.', [
            'business' => $this->businessJson($business->fresh()),
            'state' => $this->bootstrap($request),
        ]);
    }

    public function updateBusiness(Request $request, Business $business)
    {
        abort_unless($business->isOwner($request->user()), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:8'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ]);

        $business->update($data);
        $this->log('business.updated', 'Updated business "'.$business->name.'"', $business);

        return $this->ok($request, 'Business updated.', ['business' => $this->businessJson($business->fresh())]);
    }

    public function destroyBusiness(Request $request, Business $business)
    {
        abort_unless($business->isOwner($request->user()), 403);

        if ($request->user()->businesses()->count() <= 1) {
            return $this->fail($request, 'business', 'You need at least one business.');
        }

        $this->log('business.deleted', 'Deleted business "'.$business->name.'"');
        $business->delete();

        $remaining = $request->user()->businesses()->first();
        $request->user()->forceFill(['active_business_id' => $remaining?->id])->save();

        return $this->ok($request, 'Business deleted.', ['state' => $this->bootstrap($request)]);
    }

    public function switchBusiness(Request $request, Business $business)
    {
        abort_unless(
            app(BusinessContext::class)->userCanAccess($request->user(), $business),
            403
        );

        $request->user()->forceFill(['active_business_id' => $business->id])->save();

        $this->log('business.switched', 'Switched to business "'.$business->name.'"', $business);

        return $this->ok($request, 'Switched to '.$business->name.'.', ['state' => $this->bootstrap($request)]);
    }

    /* ------------------------------ staff ------------------------------ */

    public function storeStaff(Request $request)
    {
        abort_unless($this->isOwner(), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', Rule::in(['owner', 'staff'])],
        ]);

        $business = $this->business();

        if ($business->users()->where('email', $data['email'])->exists()) {
            return $this->fail($request, 'email', 'That person already has access to this business.');
        }

        if ($blocked = $this->guardLimit($request, 'staff', $business->users()->count())) {
            return $blocked;
        }

        $newAccount = false;
        $tempPassword = null;

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            $newAccount = true;
            $tempPassword = Str::password(10);
            $user = User::create([
                'name' => ($data['name'] ?? null) ?: Str::headline(Str::before($data['email'], '@')),
                'email' => $data['email'],
                'password' => Hash::make($tempPassword),
            ]);
        }

        $business->users()->attach($user->id, ['role' => $data['role'] ?? 'staff']);
        $user->notify(new SiteAlert(
            'Added to '.$business->name,
            $request->user()->name.' gave you access to '.$business->name.'.',
            route('tools.index'),
            'team',
        ));

        $this->log('staff.added', 'Added '.$user->email.' to the team', $user);

        return $this->ok($request, $newAccount
            ? 'Account created — share the temporary password with '.$user->email.'.'
            : $user->name.' now has access.', [
                'staff' => $business->users()->orderBy('name')->get()->map(fn ($u) => $this->staffJson($u))->all(),
                'temporary_password' => $tempPassword,
            ]);
    }

    public function updateStaff(Request $request, User $staff)
    {
        abort_unless($this->isOwner(), 403);

        $business = $this->business();

        if (! $business->users()->whereKey($staff->id)->exists()) {
            abort(404);
        }

        if ($business->isOwner($staff)) {
            return $this->fail($request, 'role', "The owner's role cannot be changed.");
        }

        $data = $request->validate(['role' => ['required', Rule::in(['owner', 'staff'])]]);
        $business->users()->updateExistingPivot($staff->id, ['role' => $data['role']]);

        $this->log('staff.role', 'Set '.$staff->name.' as '.$data['role'], $staff);

        return $this->ok($request, $staff->name.' is now '.$data['role'].'.', [
            'staff' => $business->users()->orderBy('name')->get()->map(fn ($u) => $this->staffJson($u))->all(),
        ]);
    }

    public function destroyStaff(Request $request, User $staff)
    {
        abort_unless($this->isOwner(), 403);

        $business = $this->business();

        if ($business->isOwner($staff) || $staff->id === $request->user()->id) {
            return $this->fail($request, 'staff', 'You cannot remove the owner.');
        }

        $business->users()->detach($staff->id);
        $this->log('staff.removed', 'Removed '.$staff->name.' from the team', $staff);

        return $this->ok($request, $staff->name.' removed.', [
            'staff' => $business->users()->orderBy('name')->get()->map(fn ($u) => $this->staffJson($u))->all(),
        ]);
    }

    /* ------------------------------ contacts ------------------------------ */

    public function storeContact(Request $request)
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in([BtContact::CUSTOMER, BtContact::SUPPLIER])],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $contact = BtContact::create($data + [
            'user_id' => $request->user()->id,
            'business_id' => $this->bid(),
        ]);

        $this->log('contact.created', 'Added '.$contact->kind.' '.$contact->name, $contact);

        return $this->ok($request, 'Contact saved.', ['contact' => $this->contactJson($contact)]);
    }

    public function updateContact(Request $request, BtContact $contact)
    {
        $this->owned($contact);

        $contact->update($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]));

        return $this->ok($request, 'Contact updated.', [
            'contact' => $this->contactJson($contact->fresh(), $contact->balanceOwed()),
        ]);
    }

    public function destroyContact(Request $request, BtContact $contact)
    {
        $this->owned($contact);

        if ($contact->documents()->exists()) {
            return $this->fail($request, 'contact', 'This contact has documents — reassign them first.');
        }

        $contact->delete();
        $this->log('contact.deleted', 'Deleted contact '.$contact->name);

        return $this->ok($request, 'Contact deleted.', ['deleted' => $contact->id]);
    }

    /* ------------------------------ inventory ------------------------------ */

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:40'],
            'unit' => ['nullable', 'string', 'max:12'],
            'cost_price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'sale_price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reorder_level' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        if ($blocked = $this->guardLimit($request, 'items', BtItem::where('business_id', $this->bid())->count())) {
            return $blocked;
        }

        $item = BtItem::create($data + [
            'user_id' => $request->user()->id,
            'business_id' => $this->bid(),
        ]);

        if ($item->stock > 0) {
            BtStockMove::create([
                'user_id' => $request->user()->id, 'business_id' => $this->bid(), 'item_id' => $item->id,
                'qty_change' => $item->stock, 'reason' => 'Opening stock',
            ]);
        }

        $this->log('item.created', 'Added item '.$item->name, $item);

        return $this->ok($request, 'Item saved.', ['item' => $this->itemJson($item)]);
    }

    public function updateItem(Request $request, BtItem $item)
    {
        $this->owned($item);

        $item->update($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:40'],
            'unit' => ['nullable', 'string', 'max:12'],
            'cost_price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'sale_price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'reorder_level' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]));

        return $this->ok($request, 'Item updated.', ['item' => $this->itemJson($item->fresh())]);
    }

    public function destroyItem(Request $request, BtItem $item)
    {
        $this->owned($item);

        if (BtDocumentLine::where('item_id', $item->id)->exists()) {
            return $this->fail($request, 'item', 'This item is used on documents. Set stock to 0 instead of deleting.');
        }

        $item->delete();
        $this->log('item.deleted', 'Deleted item '.$item->name);

        return $this->ok($request, 'Item deleted.', ['deleted' => $item->id]);
    }

    public function adjustStock(Request $request, BtItem $item)
    {
        $this->owned($item);

        $data = $request->validate([
            'qty_change' => ['required', 'integer', 'min:-1000000', 'max:1000000', 'not_in:0'],
            'reason' => ['required', 'string', 'max:120'],
        ]);

        if ($item->stock + $data['qty_change'] < 0) {
            return $this->fail($request, 'qty_change', "Only {$item->stock} {$item->unit} in stock.");
        }

        $item->increment('stock', $data['qty_change']);
        BtStockMove::create([
            'user_id' => $request->user()->id, 'business_id' => $this->bid(), 'item_id' => $item->id,
            'qty_change' => $data['qty_change'], 'reason' => $data['reason'],
        ]);

        $this->log('stock.adjusted', 'Stock '.$item->name.' '.($data['qty_change'] > 0 ? '+' : '').$data['qty_change'], $item);

        return $this->ok($request, 'Stock updated.', ['item' => $this->itemJson($item->fresh())]);
    }

    /* ------------------------------ documents ------------------------------ */

    public function storeDoc(Request $request, string $type)
    {
        $type = str($type)->singular()->toString();
        abort_unless(in_array($type, BtDocument::TYPES, true), 404);

        $bid = $this->bid();
        $purchase = in_array($type, BtDocument::PURCHASE_TYPES, true);

        $data = $request->validate([
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('business_id', $bid)],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'invoice_id' => ['nullable', Rule::exists('bt_documents', 'id')->where('business_id', $bid)],
            'converted_from_id' => ['nullable', Rule::exists('bt_documents', 'id')->where('business_id', $bid)],
            'bank_account_id' => ['nullable', Rule::exists('bank_accounts', 'id')->where('business_id', $bid)],
            'payment_method' => ['nullable', 'string', 'max:20'],
            'amount' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'lines' => ['nullable', 'array', 'max:50'],
            'lines.*.item_id' => ['nullable', Rule::exists('bt_items', 'id')->where('business_id', $bid)],
            'lines.*.account_id' => ['nullable', Rule::exists('accounts', 'id')->where('business_id', $bid)],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0.01', 'max:999999'],
            'lines.*.unit_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]);

        // Receipts, credit notes and supplier payments settle a bill.
        if (in_array($type, [BtDocument::RECEIPT, BtDocument::CREDIT_NOTE, BtDocument::PURCHASE_PAYMENT], true) && ! empty($data['invoice_id'])) {
            if ($blocked = $this->guardDocLimit($request, $type)) {
                return $blocked;
            }

            return $this->storeSettlement($request, $data, $type);
        }

        if ($blocked = $this->guardDocLimit($request, $type)) {
            return $blocked;
        }

        [$rows, $subtotal] = $this->buildLines($data['lines'] ?? [], $bid, $purchase);

        if (empty($rows)) {
            return $this->fail($request, 'lines', 'Add at least one line item.');
        }

        $sellsFromStock = in_array($type, [BtDocument::INVOICE, BtDocument::DELIVERY_NOTE], true);

        if ($sellsFromStock && ($short = $this->checkStock($rows, $bid))) {
            return $this->fail($request, 'lines', $short);
        }

        $status = match ($type) {
            BtDocument::QUOTATION, BtDocument::SALES_ORDER, BtDocument::PURCHASE_QUOTE, BtDocument::PURCHASE_ORDER => BtDocument::DRAFT,
            BtDocument::INVOICE, BtDocument::PURCHASE_INVOICE => BtDocument::UNPAID,
            default => BtDocument::POSTED,
        };

        $doc = null;

        DB::transaction(function () use ($type, $data, $rows, $subtotal, $status, $sellsFromStock, &$doc) {
            $doc = $this->newDocument($type, [
                'contact_id' => $data['contact_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'converted_from_id' => $data['converted_from_id'] ?? null,
                'paid_amount' => 0,
            ]);

            $this->applyTotals($doc, $subtotal, (int) ($data['discount'] ?? 0), (float) ($data['tax_rate'] ?? $this->settings()->tax_rate));
            $doc->save();

            foreach ($rows as $row) {
                $doc->lines()->create($row);
            }

            if ($type === BtDocument::RECEIPT) {
                $doc->update(['paid_amount' => $doc->total]);
                $this->decrementStock($doc, 'Standalone receipt '.$doc->number);
            } elseif ($sellsFromStock) {
                $this->decrementStock($doc, $doc->label().' '.$doc->number);
            } elseif ($type === BtDocument::PURCHASE_INVOICE) {
                $this->receiveStock($doc, $doc->label().' '.$doc->number);
            }
        });

        $doc->load('contact');

        if ($type === BtDocument::INVOICE) {
            $this->ledger()->postInvoice($doc->load('lines'));
        } elseif ($type === BtDocument::PURCHASE_INVOICE) {
            $this->ledger()->postPurchaseInvoice($doc->load('lines'));
        }

        $this->log($type.'.created', 'Created '.$doc->label().' '.$doc->number.' for '.(int) $doc->total, $doc);

        if ($type === BtDocument::INVOICE) {
            $this->notifyBusiness(
                'Invoice '.$doc->number.' issued',
                ($doc->contact?->name ?? 'A customer').' was invoiced '.$this->settings()->currency.' '.number_format($doc->total).'.',
                route('tools.invoices'),
                'invoice',
            );
        }

        $payload = $this->docJson($doc, true);
        $payload['qr'] = $this->qrDataUri($doc);
        $payload['verify_url'] = route('tools.verify', ['doc' => $doc->id, 'token' => $doc->public_token]);

        return $this->ok($request, $doc->label().' '.$doc->number.' created.', [
            'document' => $payload,
            'items' => BtItem::where('business_id', $bid)->orderBy('name')->get()->map(fn ($i) => $this->itemJson($i))->all(),
            'reminders' => $this->recentReminders(),
        ]);
    }

    /** A receipt, credit note or supplier payment that settles a bill. */
    private function storeSettlement(Request $request, array $data, string $type)
    {
        $bid = $this->bid();
        $billType = $type === BtDocument::PURCHASE_PAYMENT ? BtDocument::PURCHASE_INVOICE : BtDocument::INVOICE;

        $bill = BtDocument::where('business_id', $bid)
            ->where('type', $billType)->findOrFail($data['invoice_id']);

        if (! in_array($bill->status, [BtDocument::UNPAID, BtDocument::PARTIAL], true)) {
            return $this->fail($request, 'invoice_id', 'That '.$bill->label().' is already settled.');
        }

        $amount = (int) ($data['amount'] ?? $bill->balance());

        if ($amount < 1 || $amount > $bill->balance()) {
            return $this->fail($request, 'amount', 'Amount must be between 1 and '.$bill->balance().'.');
        }

        $method = $type === BtDocument::CREDIT_NOTE
            ? null
            : ($data['payment_method'] ?? ($type === BtDocument::PURCHASE_PAYMENT ? 'bank' : 'cash'));

        $doc = null;

        DB::transaction(function () use ($bill, $amount, $data, $type, $method, &$doc) {
            $doc = $this->newDocument($type, [
                'contact_id' => $bill->contact_id,
                'issue_date' => $data['issue_date'],
                'status' => BtDocument::POSTED,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $method,
                'invoice_id' => $bill->id,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'subtotal' => $amount,
                'discount' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $amount,
                'paid_amount' => $amount,
            ]);

            $doc->lines()->create([
                'description' => match ($type) {
                    BtDocument::CREDIT_NOTE => 'Credit against '.$bill->number,
                    BtDocument::PURCHASE_PAYMENT => 'Payment for '.$bill->number,
                    default => 'Payment received for '.$bill->number,
                },
                'qty' => 1, 'unit_price' => $amount, 'unit_cost' => 0, 'line_total' => $amount,
            ]);

            $bill->increment('paid_amount', $amount);
            $bill->refreshInvoiceStatus();
            $bill->save();
        });

        $doc->load('contact');
        $bill->refresh()->load('contact');

        $this->ledger()->postSettlement($doc);

        $this->log($type.'.created', 'Recorded '.$doc->label().' '.$doc->number.' on '.$bill->number.' for '.$amount, $doc);

        $this->notifyBusiness(
            $type === BtDocument::PURCHASE_PAYMENT ? 'Supplier payment made' : ($type === BtDocument::CREDIT_NOTE ? 'Credit note issued' : 'Payment received'),
            $this->settings()->currency.' '.number_format($amount).' on '.$bill->label().' '.$bill->number.'.',
            $type === BtDocument::PURCHASE_PAYMENT ? route('tools.purchaseinvoices') : route('tools.invoices'),
            'payment',
        );

        return $this->ok($request, $doc->label().' of '.$amount.' recorded.', [
            'document' => $this->docJson($doc, true),
            'invoice' => $this->docJson($bill),
        ]);
    }

    public function updateDocStatus(Request $request, BtDocument $doc)
    {
        $this->owned($doc);
        abort_unless(in_array($doc->type, [BtDocument::QUOTATION, BtDocument::SALES_ORDER, BtDocument::PURCHASE_QUOTE, BtDocument::PURCHASE_ORDER], true), 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(BtDocument::STATUSES[$doc->type])],
        ]);

        $doc->update(['status' => $data['status']]);
        $this->log($doc->type.'.status', $doc->label().' '.$doc->number.' marked '.$data['status'], $doc);

        return $this->ok($request, $doc->label().' marked '.$data['status'].'.', ['document' => $this->docJson($doc->fresh()->load('contact'))]);
    }

    public function convertDoc(Request $request, BtDocument $doc)
    {
        $this->owned($doc);

        $options = self::CONVERSIONS[$doc->type] ?? [];

        abort_if($options === [], 404);

        $target = (string) ($request->input('target') ?: $options[0]);

        abort_unless(in_array($target, $options, true), 404);

        if ($blocked = $this->guardDocLimit($request, $target)) {
            return $blocked;
        }

        $rows = $doc->lines->map(fn ($l) => [
            'item_id' => $l->item_id, 'description' => $l->description, 'qty' => (float) $l->qty,
            'unit_price' => $l->unit_price, 'unit_cost' => $l->unit_cost, 'line_total' => $l->line_total,
            'account_id' => $l->account_id,
        ])->all();

        // Stock only leaves the warehouse once per chain: whichever of the
        // invoice or delivery note is raised first moves it, the other doesn't.
        $movesStock = in_array($target, self::STOCK_TYPES, true) && ! $this->chainHasFulfillment($doc);

        if ($movesStock && ($short = $this->checkStock($rows, $doc->business_id))) {
            return $this->fail($request, 'stock', $short);
        }

        $postsRevenue = $target === BtDocument::INVOICE && ! $this->chainHasInvoice($doc);

        $next = null;

        DB::transaction(function () use ($doc, $rows, $target, $movesStock, &$next) {
            $next = $this->newDocument($target, [
                'contact_id' => $doc->contact_id,
                'issue_date' => now()->toDateString(),
                'due_date' => $target === BtDocument::INVOICE ? now()->addDays(14)->toDateString() : null,
                'status' => in_array($target, [BtDocument::INVOICE, BtDocument::PURCHASE_INVOICE], true)
                    ? BtDocument::UNPAID
                    : (in_array($target, self::STOCK_TYPES, true) ? BtDocument::POSTED : BtDocument::DRAFT),
                'notes' => $doc->notes,
                'converted_from_id' => $doc->id,
                'subtotal' => $doc->subtotal,
                'discount' => $doc->discount,
                'tax_rate' => $doc->tax_rate,
                'tax_amount' => $doc->tax_amount,
                'total' => $doc->total,
                'paid_amount' => 0,
            ]);

            foreach ($rows as $row) {
                $next->lines()->create($row);
            }

            if ($movesStock) {
                $this->decrementStock($next, 'Converted from '.$doc->number);
            } elseif ($target === BtDocument::PURCHASE_INVOICE) {
                $this->receiveStock($next, 'Converted from '.$doc->number);
            }

            // Advance the source along its lifecycle.
            $sourceStatus = match ($doc->type) {
                BtDocument::QUOTATION, BtDocument::PURCHASE_QUOTE => BtDocument::ACCEPTED,
                BtDocument::SALES_ORDER, BtDocument::PURCHASE_ORDER => BtDocument::FULFILLED,
                default => null,
            };

            if ($sourceStatus) {
                $doc->update(['status' => $sourceStatus]);
            }
        });

        $next->load('contact');

        if ($postsRevenue) {
            $this->ledger()->postInvoice($next->load('lines'));
        } elseif ($target === BtDocument::PURCHASE_INVOICE) {
            $this->ledger()->postPurchaseInvoice($next->load('lines'));
        }

        $this->log($doc->type.'.converted', 'Converted '.$doc->number.' to '.$next->label().' '.$next->number, $next);

        return $this->ok($request, $doc->label().' converted to '.$next->number.'.', [
            'document' => $this->docJson($next, true),
            'quote' => $this->docJson($doc->fresh()->load('contact')),
            'items' => BtItem::where('business_id', $this->bid())->orderBy('name')->get()->map(fn ($i) => $this->itemJson($i))->all(),
        ]);
    }

    /** Duplicate a document into a fresh copy, respecting its accounting behaviour. */
    public function cloneDoc(Request $request, BtDocument $doc)
    {
        $this->owned($doc);
        abort_unless(in_array($doc->type, self::CLONEABLE, true), 404);

        if ($blocked = $this->guardDocLimit($request, $doc->type)) {
            return $blocked;
        }

        $type = $doc->type;

        $rows = $doc->lines->map(fn ($l) => [
            'item_id' => $l->item_id, 'description' => $l->description, 'qty' => (float) $l->qty,
            'unit_price' => $l->unit_price, 'unit_cost' => $l->unit_cost, 'line_total' => $l->line_total,
            'account_id' => $l->account_id,
        ])->all();

        $sellsFromStock = in_array($type, self::STOCK_TYPES, true);

        if ($sellsFromStock && ($short = $this->checkStock($rows, $doc->business_id))) {
            return $this->fail($request, 'stock', $short);
        }

        $copy = null;

        DB::transaction(function () use ($doc, $type, $rows, $sellsFromStock, &$copy) {
            $copy = $this->newDocument($type, [
                'contact_id' => $doc->contact_id,
                'issue_date' => now()->toDateString(),
                'due_date' => $type === BtDocument::INVOICE ? now()->addDays(14)->toDateString() : null,
                'status' => in_array($type, [BtDocument::INVOICE, BtDocument::PURCHASE_INVOICE], true)
                    ? BtDocument::UNPAID
                    : ($sellsFromStock ? BtDocument::POSTED : BtDocument::DRAFT),
                'notes' => $doc->notes,
                'discount' => $doc->discount,
                'tax_rate' => $doc->tax_rate,
                'subtotal' => $doc->subtotal,
                'tax_amount' => $doc->tax_amount,
                'total' => $doc->total,
                'paid_amount' => 0,
            ]);

            foreach ($rows as $row) {
                $copy->lines()->create($row);
            }

            if ($sellsFromStock) {
                $this->decrementStock($copy, 'Copy of '.$doc->number);
            } elseif ($type === BtDocument::PURCHASE_INVOICE) {
                $this->receiveStock($copy, 'Copy of '.$doc->number);
            }
        });

        $copy->load('contact');

        if ($type === BtDocument::INVOICE) {
            $this->ledger()->postInvoice($copy->load('lines'));
        } elseif ($type === BtDocument::PURCHASE_INVOICE) {
            $this->ledger()->postPurchaseInvoice($copy->load('lines'));
        }

        $this->log($type.'.duplicated', 'Duplicated '.$doc->number.' as '.$copy->number, $copy);

        $payload = $this->docJson($copy, true);
        $payload['qr'] = $this->qrDataUri($copy);
        $payload['verify_url'] = route('tools.verify', ['doc' => $copy->id, 'token' => $copy->public_token]);

        return $this->ok($request, 'Duplicated as '.$copy->number.'.', [
            'document' => $payload,
            'items' => BtItem::where('business_id', $this->bid())->orderBy('name')->get()->map(fn ($i) => $this->itemJson($i))->all(),
        ]);
    }

    public function destroyDoc(Request $request, BtDocument $doc)
    {
        $this->owned($doc);

        $type = $doc->type;
        $number = $doc->number;

        if ($type === BtDocument::INVOICE) {
            if ($doc->paid_amount > 0 || $doc->receipts()->exists()) {
                return $this->fail($request, 'doc', 'Paid invoices cannot be deleted — they are your sales record.');
            }
            if ($this->movedStock($doc)) {
                $this->restoreStock($doc, 'Invoice '.$doc->number.' deleted');
            }
        }

        if ($type === BtDocument::DELIVERY_NOTE) {
            if ($this->movedStock($doc)) {
                $this->restoreStock($doc, 'Delivery note '.$doc->number.' deleted');
            }
        }

        if ($type === BtDocument::PURCHASE_INVOICE) {
            if ($doc->paid_amount > 0) {
                return $this->fail($request, 'doc', 'Settled bills cannot be deleted — reverse the payment first.');
            }
            $this->decrementStock($doc, 'Purchase bill '.$doc->number.' deleted');
        }

        if (in_array($type, [BtDocument::RECEIPT, BtDocument::CREDIT_NOTE, BtDocument::PURCHASE_PAYMENT], true)) {
            if ($doc->invoice) {
                $bill = $doc->invoice;
                $bill->decrement('paid_amount', min($doc->total, $bill->paid_amount));
                $bill->refreshInvoiceStatus();
                $bill->save();
            } elseif ($type === BtDocument::RECEIPT) {
                $this->restoreStock($doc, 'Receipt '.$doc->number.' deleted');
            }
        }

        $this->ledger()->reverse(Ledger::SOURCE_DOCUMENT, $doc->id);

        $doc->delete();
        $this->log($type.'.deleted', 'Deleted '.$number);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Deleted.',
                'deleted' => $doc->id,
                'items' => BtItem::where('business_id', $this->bid())->orderBy('name')->get()->map(fn ($i) => $this->itemJson($i))->all(),
            ]);
        }

        return redirect()->route('tools.'.str($type)->plural()->toString())->with('status', 'Deleted.');
    }

    public function docPdf(BtDocument $doc)
    {
        $this->owned($doc);
        $doc->load(['lines', 'contact', 'receipts']);

        $pdf = Pdf::loadView('tools.doc-pdf', [
            'doc' => $doc,
            'settings' => $this->settings(),
            'qr' => $this->qrDataUri($doc),
            'verifyUrl' => route('tools.verify', ['doc' => $doc->id, 'token' => $doc->public_token]),
        ]);

        return $pdf->download($doc->number.'.pdf');
    }

    /** Public authenticity check — the QR code on every PDF lands here. */
    public function verify(Request $request, BtDocument $doc)
    {
        if (! hash_equals($doc->public_token, (string) $request->query('token', ''))) {
            abort(404);
        }

        $doc->load(['lines', 'contact']);

        return view('tools.verify', [
            'doc' => $doc,
            'business' => $doc->business,
        ]);
    }

    /* ------------------------------ reminders ------------------------------ */

    public function storeReminder(Request $request)
    {
        $bid = $this->bid();

        $data = $request->validate([
            'document_id' => ['required', Rule::exists('bt_documents', 'id')->where('business_id', $bid)->where('type', BtDocument::INVOICE)],
            'channel' => ['required', Rule::in([Reminder::EMAIL, Reminder::WHATSAPP])],
            'message' => ['nullable', 'string', 'max:1000'],
            'recipient' => ['nullable', 'string', 'max:255'],
        ]);

        $doc = BtDocument::where('business_id', $bid)->with('contact')->findOrFail($data['document_id']);

        $recipient = ($data['recipient'] ?? null) ?: ($data['channel'] === Reminder::EMAIL
            ? $doc->contact?->email
            : $doc->contact?->phone);

        if (! $recipient) {
            return $this->fail($request, 'recipient', 'No '.($data['channel'] === Reminder::EMAIL ? 'email address' : 'phone number').' on this customer.');
        }

        $message = ($data['message'] ?? null) ?: $this->defaultReminderMessage($doc);
        $waUrl = null;
        $sent = false;

        if ($data['channel'] === Reminder::WHATSAPP) {
            $digits = preg_replace('/\D+/', '', $recipient);
            $waUrl = 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
        } else {
            Mail::to($recipient)->send(new InvoiceReminderMail($doc, $message));
            $sent = true;
        }

        $reminder = Reminder::create([
            'business_id' => $bid,
            'document_id' => $doc->id,
            'user_id' => $request->user()->id,
            'channel' => $data['channel'],
            'recipient' => $recipient,
            'message' => $message,
            'sent_at' => $sent ? now() : null,
        ]);

        $this->log('reminder.sent', ucfirst($data['channel']).' reminder for '.$doc->number.' to '.$recipient, $doc);

        return $this->ok($request, $data['channel'] === Reminder::WHATSAPP
            ? 'WhatsApp reminder ready.'
            : 'Reminder emailed to '.$recipient.'.', [
                'reminder' => $this->reminderJson($reminder->load('document.contact', 'user')),
                'reminders' => $this->recentReminders(),
                'wa_url' => $waUrl,
            ]);
    }

    private function defaultReminderMessage(BtDocument $doc): string
    {
        $currency = $this->settings()->currency ?: 'UGX';
        $name = $doc->contact?->name ?? 'there';
        $due = $doc->due_date?->toFormattedDateString() ?? 'soon';

        return sprintf(
            'Hello %s, this is a friendly reminder from %s that invoice %s for %s %s is due on %s. Thank you.',
            $name,
            $this->settings()->name,
            $doc->number,
            $currency,
            number_format($doc->balance()),
            $due,
        );
    }

    private function recentReminders(): array
    {
        return Reminder::where('business_id', $this->bid())->with(['document.contact', 'user'])
            ->latest()->take(60)->get()->map(fn ($r) => $this->reminderJson($r))->all();
    }

    /* ------------------------------ POS ------------------------------ */

    public function posCheckout(Request $request)
    {
        $bid = $this->bid();

        $data = $request->validate([
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('business_id', $bid)],
            'payment_method' => ['required', Rule::in(['cash', 'momo', 'card', 'bank'])],
            'tendered' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'lines' => ['required', 'array', 'min:1', 'max:50'],
            'lines.*.item_id' => ['required', Rule::exists('bt_items', 'id')->where('business_id', $bid)],
            'lines.*.qty' => ['required', 'numeric', 'min:0.01', 'max:999999'],
        ]);

        $rows = [];
        $subtotal = 0;

        foreach ($data['lines'] as $line) {
            $item = BtItem::where('business_id', $bid)->findOrFail($line['item_id']);
            $qty = (float) $line['qty'];
            $lineTotal = (int) round($qty * $item->sale_price);
            $rows[] = [
                'item_id' => $item->id, 'description' => $item->name, 'qty' => $qty,
                'unit_price' => $item->sale_price, 'unit_cost' => $item->cost_price, 'line_total' => $lineTotal,
            ];
            $subtotal += $lineTotal;
        }

        if ($subtotal < 1) {
            return $this->fail($request, 'lines', 'The sale total must be at least 1.');
        }

        if (($short = $this->checkStock($rows, $bid))) {
            return $this->fail($request, 'lines', $short);
        }

        if ($blocked = $this->guardDocLimit($request, BtDocument::INVOICE)) {
            return $blocked;
        }

        if ($blocked = $this->guardDocLimit($request, BtDocument::RECEIPT)) {
            return $blocked;
        }

        $invoice = null;
        $receipt = null;

        DB::transaction(function () use ($data, $rows, $subtotal, &$invoice, &$receipt) {
            $invoice = $this->newDocument(BtDocument::INVOICE, [
                'contact_id' => $data['contact_id'] ?? null,
                'issue_date' => now()->toDateString(),
                'status' => BtDocument::PAID,
                'notes' => 'POS sale',
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $subtotal,
                'paid_amount' => $subtotal,
                'payment_method' => $data['payment_method'],
            ]);

            foreach ($rows as $row) {
                $invoice->lines()->create($row);
            }

            $this->decrementStock($invoice, 'POS sale '.$invoice->number);

            $receipt = $this->newDocument(BtDocument::RECEIPT, [
                'contact_id' => $data['contact_id'] ?? null,
                'issue_date' => now()->toDateString(),
                'status' => BtDocument::POSTED,
                'notes' => 'POS sale '.$invoice->number,
                'payment_method' => $data['payment_method'],
                'invoice_id' => $invoice->id,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $subtotal,
                'paid_amount' => $subtotal,
            ]);

            $receipt->lines()->create([
                'description' => 'POS sale '.$invoice->number,
                'qty' => 1, 'unit_price' => $subtotal, 'unit_cost' => 0, 'line_total' => $subtotal,
            ]);
        });

        $change = max(0, (int) ($data['tendered'] ?? 0) - $subtotal);

        $receipt->load('contact');
        $payload = $this->docJson($receipt, true);
        $payload['qr'] = $this->qrDataUri($receipt);
        $payload['verify_url'] = route('tools.verify', ['doc' => $receipt->id, 'token' => $receipt->public_token]);

        $this->log('pos.sale', 'POS sale '.$receipt->number.' for '.$subtotal, $receipt);

        return $this->ok($request, 'Sale complete — '.$receipt->number.($change > 0 ? '. Change: '.number_format($change) : '.'), [
            'document' => $payload,
            'invoice' => $this->docJson($invoice->load('contact')),
            'change' => $change,
            'items' => BtItem::where('business_id', $bid)->orderBy('name')->get()->map(fn ($i) => $this->itemJson($i))->all(),
        ]);
    }

    /* ------------------------------ expenses ------------------------------ */

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'status' => ['required', Rule::in([BtExpense::PAID, BtExpense::UNPAID])],
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('business_id', $this->bid())],
            'bank_account_id' => ['nullable', Rule::exists('bank_accounts', 'id')->where('business_id', $this->bid())],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $expense = BtExpense::create($data + [
            'user_id' => $request->user()->id,
            'business_id' => $this->bid(),
        ]);

        $this->ledger()->postExpense($expense);
        $this->log('expense.created', 'Recorded expense '.$expense->category.' for '.$expense->amount, $expense);

        return $this->ok($request, 'Expense recorded.', ['expense' => $this->expenseJson($expense->load('supplier'))]);
    }

    public function updateExpense(Request $request, BtExpense $expense)
    {
        $this->owned($expense);

        $expense->update($request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'status' => ['required', Rule::in([BtExpense::PAID, BtExpense::UNPAID])],
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('business_id', $this->bid())],
            'bank_account_id' => ['nullable', Rule::exists('bank_accounts', 'id')->where('business_id', $this->bid())],
            'note' => ['nullable', 'string', 'max:500'],
        ]));

        $this->ledger()->reverse(Ledger::SOURCE_EXPENSE, $expense->id);
        $this->ledger()->postExpense($expense->fresh());

        return $this->ok($request, 'Expense updated.', ['expense' => $this->expenseJson($expense->fresh()->load('supplier'))]);
    }

    public function destroyExpense(Request $request, BtExpense $expense)
    {
        $this->owned($expense);
        $this->ledger()->reverse(Ledger::SOURCE_EXPENSE, $expense->id);
        $expense->delete();
        $this->log('expense.deleted', 'Deleted expense '.$expense->category.' for '.$expense->amount);

        return $this->ok($request, 'Expense deleted.', ['deleted' => $expense->id]);
    }

    /* ------------------------------ chart of accounts ------------------------------ */

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(Account::TYPES)],
            'opening_balance' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]);

        $business = $this->business();
        $this->chart()->ensure($business);

        $account = $business->accounts()->create($data + [
            'user_id' => $request->user()->id,
            'subtype' => null,
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->log('account.created', 'Added account '.$account->code.' '.$account->name, $account);

        return $this->ok($request, 'Account added.', ['account' => $this->accountJson($account)]);
    }

    public function updateAccount(Request $request, Account $account)
    {
        $this->owned($account);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'is_active' => ['nullable', 'boolean'],
            'opening_balance' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]);

        if ($account->is_system) {
            unset($data['name']);
            $data['name'] = $account->name;
        }

        $account->update($data);
        $this->log('account.updated', 'Updated account '.$account->code, $account);

        return $this->ok($request, 'Account updated.', ['account' => $this->accountJson($account->fresh())]);
    }

    public function destroyAccount(Request $request, Account $account)
    {
        $this->owned($account);

        if ($account->is_system) {
            return $this->fail($request, 'account', 'System accounts cannot be deleted.');
        }

        if ($account->lines()->exists()) {
            return $this->fail($request, 'account', 'That account has postings and cannot be deleted.');
        }

        $account->delete();
        $this->log('account.deleted', 'Deleted account '.$account->code);

        return $this->ok($request, 'Account deleted.', ['deleted' => $account->id]);
    }

    /* ------------------------------ bank & cash ------------------------------ */

    public function storeBank(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['nullable', Rule::in([BankAccount::BANK, BankAccount::CASH, BankAccount::MOMO])],
            'account_number' => ['nullable', 'string', 'max:40'],
            'opening_balance' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]);

        $business = $this->business();
        $this->chart()->ensure($business);

        $data['type'] = $data['type'] ?? BankAccount::BANK;

        $account = $business->accounts()->firstOrCreate(
            ['code' => $data['type'] === BankAccount::CASH ? '1000' : '1010'],
            [
                'user_id' => $request->user()->id,
                'name' => $data['type'] === BankAccount::CASH ? 'Cash' : 'Bank',
                'type' => Account::ASSET,
                'subtype' => $data['type'] === BankAccount::CASH ? Account::CASH : Account::BANK,
                'is_system' => true,
            ],
        );

        $bank = $business->bankAccounts()->create([
            'user_id' => $request->user()->id,
            'account_id' => $account->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'account_number' => $data['account_number'] ?? null,
            'opening_balance' => $data['opening_balance'] ?? 0,
        ]);

        $this->log('bank.created', 'Added '.$bank->name.' '.$bank->type.' account', $bank);

        return $this->ok($request, 'Account added.', ['bank' => $this->bankJson($bank)]);
    }

    public function updateBank(Request $request, BankAccount $account)
    {
        $this->owned($account);

        $account->update($request->validate([
            'name' => ['required', 'string', 'max:80'],
            'account_number' => ['nullable', 'string', 'max:40'],
            'opening_balance' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]));

        return $this->ok($request, 'Account updated.', ['bank' => $this->bankJson($account->fresh())]);
    }

    public function destroyBank(Request $request, BankAccount $account)
    {
        $this->owned($account);
        $account->delete();
        $this->log('bank.deleted', 'Deleted '.$account->name);

        return $this->ok($request, 'Account deleted.', ['deleted' => $account->id]);
    }

    public function storeBankTransaction(Request $request, BankAccount $account)
    {
        $this->owned($account);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'type' => ['required', Rule::in([BankTransaction::DEBIT, BankTransaction::CREDIT])],
            'reference' => ['nullable', 'string', 'max:60'],
        ]);

        $tx = $account->transactions()->create([
            'business_id' => $this->bid(),
            'user_id' => $request->user()->id,
            'date' => $data['date'],
            'description' => $data['description'],
            'type' => $data['type'],
            'reference' => $data['reference'] ?? null,
            'amount' => $data['type'] === BankTransaction::CREDIT ? (int) $data['amount'] : -(int) $data['amount'],
        ]);

        $this->log('bank.transaction', $data['type'].' '.$data['amount'].' on '.$account->name, $tx);

        return $this->ok($request, 'Transaction recorded.', [
            'transaction' => $this->bankTxJson($tx),
            'bank' => $this->bankJson($account->fresh()),
        ]);
    }

    public function destroyBankTransaction(Request $request, BankTransaction $transaction)
    {
        $this->owned($transaction);
        $transaction->delete();

        return $this->ok($request, 'Transaction deleted.', ['deleted' => $transaction->id]);
    }

    /* ------------------------------ employees & payslips ------------------------------ */

    public function storeEmployee(Request $request)
    {
        $data = $this->validateEmployee($request);

        $employee = Employee::create($data + [
            'user_id' => $request->user()->id,
            'business_id' => $this->bid(),
        ]);

        $this->log('employee.created', 'Added employee '.$employee->name, $employee);

        return $this->ok($request, 'Employee added.', ['employee' => $this->employeeJson($employee)]);
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $this->owned($employee);
        $employee->update($this->validateEmployee($request));

        return $this->ok($request, 'Employee updated.', ['employee' => $this->employeeJson($employee->fresh())]);
    }

    public function destroyEmployee(Request $request, Employee $employee)
    {
        $this->owned($employee);
        $employee->delete();
        $this->log('employee.deleted', 'Deleted employee '.$employee->name);

        return $this->ok($request, 'Employee removed.', ['deleted' => $employee->id]);
    }

    private function validateEmployee(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:80'],
            'employment_type' => ['nullable', Rule::in([Employee::FULL_TIME, Employee::PART_TIME, Employee::CONTRACT])],
            'base_salary' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'pay_frequency' => ['nullable', Rule::in([Employee::MONTHLY, Employee::WEEKLY, Employee::DAILY])],
            'hired_on' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
    }

    public function storePayslip(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('business_id', $this->bid())],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'gross' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'allowances' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'deductions' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'status' => ['required', Rule::in([Payslip::DRAFT, Payslip::PAID])],
            'paid_on' => ['nullable', 'date'],
            'bank_account_id' => ['nullable', Rule::exists('bank_accounts', 'id')->where('business_id', $this->bid())],
        ]);

        $payslip = new Payslip($data + [
            'user_id' => $request->user()->id,
            'business_id' => $this->bid(),
            'allowances' => (int) ($data['allowances'] ?? 0),
            'deductions' => (int) ($data['deductions'] ?? 0),
        ]);
        $payslip->net = (int) $payslip->gross + (int) $payslip->allowances - (int) $payslip->deductions;
        $payslip->number = BtCounter::nextNo($this->bid(), $request->user()->id, 'payslip', 'PS');
        $payslip->save();

        $this->ledger()->postPayslip($payslip);
        $this->log('payslip.created', 'Created payslip '.$payslip->number, $payslip);

        return $this->ok($request, 'Payslip created.', ['payslip' => $this->payslipJson($payslip->load('employee'))]);
    }

    public function updatePayslip(Request $request, Payslip $payslip)
    {
        $this->owned($payslip);

        $payslip->update($request->validate([
            'gross' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'allowances' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'deductions' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'status' => ['required', Rule::in([Payslip::DRAFT, Payslip::PAID])],
            'paid_on' => ['nullable', 'date'],
            'bank_account_id' => ['nullable', Rule::exists('bank_accounts', 'id')->where('business_id', $this->bid())],
        ]));

        $payslip->net = (int) $payslip->gross + (int) $payslip->allowances - (int) $payslip->deductions;
        $payslip->save();

        $this->ledger()->reverse(Ledger::SOURCE_PAYSLIP, $payslip->id);
        $this->ledger()->postPayslip($payslip->fresh());

        return $this->ok($request, 'Payslip updated.', ['payslip' => $this->payslipJson($payslip->fresh()->load('employee'))]);
    }

    public function destroyPayslip(Request $request, Payslip $payslip)
    {
        $this->owned($payslip);
        $this->ledger()->reverse(Ledger::SOURCE_PAYSLIP, $payslip->id);
        $payslip->delete();
        $this->log('payslip.deleted', 'Deleted payslip '.$payslip->number);

        return $this->ok($request, 'Payslip deleted.', ['deleted' => $payslip->id]);
    }
}
