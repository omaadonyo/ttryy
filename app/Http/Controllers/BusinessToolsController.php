<?php

namespace App\Http\Controllers;

use App\Models\BtContact;
use App\Models\BtCounter;
use App\Models\BtDocument;
use App\Models\BtDocumentLine;
use App\Models\BtExpense;
use App\Models\BtItem;
use App\Models\BtSetting;
use App\Models\BtStockMove;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessToolsController extends Controller
{
    /* ------------------------------ helpers ------------------------------ */

    private function owned(BtDocument|BtContact|BtItem|BtExpense $model): void
    {
        abort_if($model->user_id !== request()->user()->id, 403);
    }

    private function settings(): BtSetting
    {
        return BtSetting::for(request()->user()->id);
    }

    /**
     * Build validated line rows from input. Amounts are recomputed here —
     * client totals are display only and never trusted.
     *
     * @return array{0: array, 1: int} [rows, subtotal]
     */
    private function buildLines(array $input, int $userId): array
    {
        $rows = [];
        $subtotal = 0;

        foreach ($input as $line) {
            if (! is_array($line)) {
                continue;
            }

            $item = null;
            if (! empty($line['item_id'])) {
                $item = BtItem::where('user_id', $userId)->find($line['item_id']);
            }

            $description = trim((string) ($line['description'] ?? $item?->name ?? ''));

            if ($description === '') {
                continue;
            }

            $qty = max(0.01, (float) ($line['qty'] ?? 1));
            $unitPrice = isset($line['unit_price']) && $line['unit_price'] !== ''
                ? max(0, (int) $line['unit_price'])
                : (int) ($item?->sale_price ?? 0);

            $lineTotal = (int) round($qty * $unitPrice);

            $rows[] = [
                'item_id' => $item?->id,
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
            $item = BtItem::where('user_id', $doc->user_id)->find($line->item_id);

            if (! $item) {
                continue;
            }

            $item->decrement('stock', (int) $line->qty);
            BtStockMove::create([
                'user_id' => $doc->user_id,
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
            $item = BtItem::where('user_id', $doc->user_id)->find($line->item_id);

            if (! $item) {
                continue;
            }

            $item->increment('stock', (int) $line->qty);
            BtStockMove::create([
                'user_id' => $doc->user_id,
                'item_id' => $item->id,
                'qty_change' => (int) $line->qty,
                'reason' => $ref,
                'ref_type' => 'document',
                'ref_id' => $doc->id,
            ]);
        }
    }

    /** Fail if any line exceeds available stock. Returns the first offender. */
    private function checkStock(array $rows, int $userId): ?string
    {
        $need = [];

        foreach ($rows as $row) {
            if (! $row['item_id']) {
                continue;
            }
            $need[$row['item_id']] = ($need[$row['item_id']] ?? 0) + (int) $row['qty'];
        }

        foreach ($need as $itemId => $qty) {
            $item = BtItem::where('user_id', $userId)->find($itemId);

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
            'type' => $type,
            'number' => BtCounter::nextNo($userId, $type, BtDocument::PREFIXES[$type]),
            'public_token' => Str::random(32),
        ]));
    }

    private function qrDataUri(BtDocument $doc): string
    {
        $url = route('tools.verify', ['doc' => $doc->id, 'token' => $doc->public_token]);
        $result = (new PngWriter)->write(new QrCode(data: $url, size: 200, margin: 6));

        return $result->getDataUri();
    }

    /* ------------------------------ dashboard ------------------------------ */

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $monthStart = now()->startOfMonth();

        $collectedMonth = (int) BtDocument::where('user_id', $userId)
            ->where('type', BtDocument::RECEIPT)->where('created_at', '>=', $monthStart)->sum('total');
        $receivable = (int) BtDocument::where('user_id', $userId)
            ->where('type', BtDocument::INVOICE)->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')->value('b');
        $expensesMonth = (int) BtExpense::where('user_id', $userId)
            ->where('status', BtExpense::PAID)->whereDate('date', '>=', $monthStart->toDateString())->sum('amount');
        $cash = (int) BtDocument::where('user_id', $userId)->where('type', BtDocument::RECEIPT)->sum('total')
            - (int) BtExpense::where('user_id', $userId)->where('status', BtExpense::PAID)->sum('amount');
        $lowStock = BtItem::where('user_id', $userId)->whereColumn('stock', '<=', 'reorder_level')->count();
        $pendingQuotes = BtDocument::where('user_id', $userId)
            ->where('type', BtDocument::QUOTATION)->whereIn('status', [BtDocument::DRAFT, BtDocument::SENT])->count();
        $overdue = BtDocument::where('user_id', $userId)->where('type', BtDocument::INVOICE)
            ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
            ->whereNotNull('due_date')->where('due_date', '<', now()->toDateString())->count();

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $months[] = [
                'label' => $m->format('M'),
                'in' => (int) BtDocument::where('user_id', $userId)->where('type', BtDocument::RECEIPT)
                    ->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('total'),
                'out' => (int) BtExpense::where('user_id', $userId)->where('status', BtExpense::PAID)
                    ->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'),
            ];
        }

        return view('tools.reports', [
            'active' => 'reports',
            'settings' => $this->settings(),
            'stats' => compact('collectedMonth', 'receivable', 'expensesMonth', 'cash', 'lowStock', 'pendingQuotes', 'overdue'),
            'months' => $months,
            'recentReceipts' => BtDocument::where('user_id', $userId)->where('type', BtDocument::RECEIPT)
                ->with('contact')->latest()->take(5)->get(),
            'recentExpenses' => BtExpense::where('user_id', $userId)->latest('date')->take(5)->get(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ]);

        $this->settings()->update($data);

        return back()->with('status', 'Business profile saved — it prints on every PDF.');
    }

    /* ------------------------------ contacts ------------------------------ */

    public function contacts(Request $request, string $kind)
    {
        abort_unless(in_array($kind, [BtContact::CUSTOMER, BtContact::SUPPLIER], true), 404);

        $q = trim((string) $request->query('q', ''));
        $contacts = BtContact::where('user_id', $request->user()->id)->where('kind', $kind)
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
            ->withCount(['documents as open_invoices' => fn ($w) => $w
                ->where('type', BtDocument::INVOICE)->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])])
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('tools.contacts', [
            'active' => $kind === BtContact::CUSTOMER ? 'customers' : 'suppliers',
            'kind' => $kind,
            'contacts' => $contacts,
            'q' => $q,
        ]);
    }

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

        BtContact::create($data + ['user_id' => $request->user()->id]);

        return back()->with('status', 'Contact saved.');
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

        return back()->with('status', 'Contact updated.');
    }

    public function destroyContact(BtContact $contact)
    {
        $this->owned($contact);

        if ($contact->documents()->exists()) {
            return back()->withErrors(['contact' => 'This contact has documents — reassign them first.']);
        }

        $contact->delete();

        return back()->with('status', 'Contact deleted.');
    }

    /* ------------------------------ inventory ------------------------------ */

    public function inventory(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $items = BtItem::where('user_id', $request->user()->id)
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('tools.inventory', [
            'active' => 'inventory',
            'items' => $items,
            'q' => $q,
            'stockValue' => (int) BtItem::where('user_id', $request->user()->id)
                ->selectRaw('COALESCE(SUM(stock * cost_price), 0) as v')->value('v'),
        ]);
    }

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

        $item = BtItem::create($data + ['user_id' => $request->user()->id]);

        if ($item->stock > 0) {
            BtStockMove::create([
                'user_id' => $request->user()->id, 'item_id' => $item->id,
                'qty_change' => $item->stock, 'reason' => 'Opening stock',
            ]);
        }

        return back()->with('status', 'Item saved.');
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

        return back()->with('status', 'Item updated.');
    }

    public function destroyItem(BtItem $item)
    {
        $this->owned($item);

        if (BtDocumentLine::where('item_id', $item->id)->exists()) {
            return back()->withErrors(['item' => 'This item is used on documents and cannot be deleted. Set stock to 0 instead.']);
        }

        $item->delete();

        return back()->with('status', 'Item deleted.');
    }

    public function adjustStock(Request $request, BtItem $item)
    {
        $this->owned($item);

        $data = $request->validate([
            'qty_change' => ['required', 'integer', 'min:-1000000', 'max:1000000', 'not_in:0'],
            'reason' => ['required', 'string', 'max:120'],
        ]);

        if ($item->stock + $data['qty_change'] < 0) {
            return back()->withErrors(['qty_change' => "Only {$item->stock} {$item->unit} in stock."]);
        }

        $item->increment('stock', $data['qty_change']);
        BtStockMove::create([
            'user_id' => $request->user()->id, 'item_id' => $item->id,
            'qty_change' => $data['qty_change'], 'reason' => $data['reason'],
        ]);

        return back()->with('status', 'Stock updated.');
    }

    /* ------------------------------ documents ------------------------------ */

    private function docIndex(Request $request, string $type, string $active)
    {
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $docs = BtDocument::where('user_id', $request->user()->id)->where('type', $type)
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('number', 'like', "%{$q}%")
                ->orWhereHas('contact', fn ($c) => $c->where('name', 'like', "%{$q}%"))))
            ->with('contact')->latest()->paginate(15)->withQueryString();

        $totals = [
            'count' => BtDocument::where('user_id', $request->user()->id)->where('type', $type)->count(),
            'outstanding' => $type === BtDocument::INVOICE
                ? (int) BtDocument::where('user_id', $request->user()->id)->where('type', $type)
                    ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
                    ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')->value('b')
                : 0,
        ];

        $userId = $request->user()->id;
        $items = BtItem::where('user_id', $userId)->orderBy('name')->get();

        return view('tools.docs', [
            'active' => $active, 'type' => $type, 'docs' => $docs,
            'status' => $status, 'q' => $q, 'totals' => $totals,
            // Data for the in-page "New" modal (same form as the create page).
            'titles' => ['quotation' => 'New Quotation', 'invoice' => 'New Invoice', 'receipt' => 'New Receipt'],
            'settings' => $this->settings(),
            'customers' => BtContact::where('user_id', $userId)->where('kind', BtContact::CUSTOMER)->orderBy('name')->get(),
            'itemsJson' => $items->map(fn ($i) => [
                'id' => $i->id, 'name' => $i->name, 'price' => $i->sale_price,
                'stock' => $i->stock, 'unit' => $i->unit,
            ])->values()->all(),
            'invoices' => $type === BtDocument::RECEIPT
                ? BtDocument::where('user_id', $userId)->where('type', BtDocument::INVOICE)
                    ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])->orderByDesc('issue_date')->get()
                : collect(),
        ]);
    }

    public function quotations(Request $request)
    {
        return $this->docIndex($request, BtDocument::QUOTATION, 'quotations');
    }

    public function invoices(Request $request)
    {
        return $this->docIndex($request, BtDocument::INVOICE, 'invoices');
    }

    public function receipts(Request $request)
    {
        return $this->docIndex($request, BtDocument::RECEIPT, 'receipts');
    }

    public function createDoc(Request $request, string $type)
    {
        $type = str($type)->singular()->toString();
        abort_unless(in_array($type, [BtDocument::QUOTATION, BtDocument::INVOICE, BtDocument::RECEIPT], true), 404);

        $items = BtItem::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('tools.doc-form', [
            'active' => str($type)->plural()->toString(),
            'type' => $type,
            'settings' => $this->settings(),
            'customers' => BtContact::where('user_id', $request->user()->id)
                ->where('kind', BtContact::CUSTOMER)->orderBy('name')->get(),
            'items' => $items,
            // Pre-encoded for the line-item picker (avoids Blade directive parsing limits).
            'itemsJson' => $items->map(fn ($i) => [
                'id' => $i->id, 'name' => $i->name, 'price' => $i->sale_price,
                'stock' => $i->stock, 'unit' => $i->unit,
            ])->values()->all(),
            'invoices' => $type === BtDocument::RECEIPT
                ? BtDocument::where('user_id', $request->user()->id)->where('type', BtDocument::INVOICE)
                    ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])->orderByDesc('issue_date')->get()
                : collect(),
        ]);
    }

    public function storeDoc(Request $request, string $type)
    {
        $type = str($type)->singular()->toString();
        abort_unless(in_array($type, [BtDocument::QUOTATION, BtDocument::INVOICE, BtDocument::RECEIPT], true), 404);

        $userId = $request->user()->id;

        $data = $request->validate([
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('user_id', $userId)],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'invoice_id' => ['nullable', Rule::exists('bt_documents', 'id')->where('user_id', $userId)],
            'payment_method' => ['nullable', 'string', 'max:20'],
            'amount' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'lines' => ['nullable', 'array', 'max:50'],
            'lines.*.item_id' => ['nullable', Rule::exists('bt_items', 'id')->where('user_id', $userId)],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0.01', 'max:999999'],
            'lines.*.unit_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]);

        [$rows, $subtotal] = $this->buildLines($data['lines'] ?? [], $userId);

        if ($type === BtDocument::RECEIPT && ! empty($data['invoice_id'])) {
            return $this->storePaymentReceipt($request, $data);
        }

        if (empty($rows)) {
            return back()->withErrors(['lines' => 'Add at least one line item.'])->withInput();
        }

        if ($type !== BtDocument::QUOTATION && ($short = $this->checkStock($rows, $userId))) {
            return back()->withErrors(['lines' => $short])->withInput();
        }

        $status = match ($type) {
            BtDocument::QUOTATION => BtDocument::DRAFT,
            BtDocument::INVOICE => BtDocument::UNPAID,
            default => BtDocument::POSTED,
        };

        $doc = null;

        DB::transaction(function () use ($type, $data, $rows, $subtotal, $status, &$doc) {
            $doc = $this->newDocument($type, [
                'contact_id' => $data['contact_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'paid_amount' => $type === BtDocument::RECEIPT ? 0 : 0,
            ]);

            $this->applyTotals($doc, $subtotal, (int) ($data['discount'] ?? 0), (float) ($data['tax_rate'] ?? $this->settings()->tax_rate));
            $doc->save();

            foreach ($rows as $row) {
                $doc->lines()->create($row);
            }

            if ($type === BtDocument::RECEIPT) {
                $doc->update(['paid_amount' => $doc->total]);
                $this->decrementStock($doc, 'Standalone receipt '.$doc->number);
            } elseif ($type === BtDocument::INVOICE) {
                $this->decrementStock($doc, 'Invoice '.$doc->number);
            }
        });

        return redirect()->route('tools.doc.show', $doc)->with('status', $doc->label().' '.$doc->number.' created.');
    }

    /** A receipt that records payment against an open invoice. */
    private function storePaymentReceipt(Request $request, array $data)
    {
        $userId = $request->user()->id;

        $invoice = BtDocument::where('user_id', $userId)
            ->where('type', BtDocument::INVOICE)->findOrFail($data['invoice_id']);

        if (! in_array($invoice->status, [BtDocument::UNPAID, BtDocument::PARTIAL], true)) {
            return back()->withErrors(['invoice_id' => 'That invoice is already paid.'])->withInput();
        }

        $amount = (int) ($data['amount'] ?? $invoice->balance());

        if ($amount < 1 || $amount > $invoice->balance()) {
            return back()->withErrors(['amount' => 'Amount must be between 1 and '.$invoice->balance().'.'])->withInput();
        }

        $receipt = null;

        DB::transaction(function () use ($invoice, $amount, $data, &$receipt) {
            $receipt = $this->newDocument(BtDocument::RECEIPT, [
                'contact_id' => $invoice->contact_id,
                'issue_date' => $data['issue_date'],
                'status' => BtDocument::POSTED,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'invoice_id' => $invoice->id,
                'subtotal' => $amount,
                'discount' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $amount,
                'paid_amount' => $amount,
            ]);

            $receipt->lines()->create([
                'description' => 'Payment received for '.$invoice->number,
                'qty' => 1, 'unit_price' => $amount, 'unit_cost' => 0, 'line_total' => $amount,
            ]);

            $invoice->increment('paid_amount', $amount);
            $invoice->refreshInvoiceStatus();
            $invoice->save();
        });

        return redirect()->route('tools.doc.show', $receipt)->with('status', 'Payment of '.$amount.' recorded.');
    }

    public function showDoc(BtDocument $doc)
    {
        $this->owned($doc);
        $doc->load(['lines.item', 'contact', 'receipts']);

        return view('tools.doc-show', [
            'active' => str($doc->type)->plural()->toString(),
            'doc' => $doc,
        ]);
    }

    public function updateDocStatus(Request $request, BtDocument $doc)
    {
        $this->owned($doc);
        abort_unless($doc->type === BtDocument::QUOTATION, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in([BtDocument::DRAFT, BtDocument::SENT, BtDocument::ACCEPTED, BtDocument::DECLINED])],
        ]);

        $doc->update(['status' => $data['status']]);

        return back()->with('status', 'Quotation marked '.$data['status'].'.');
    }

    public function convertQuote(BtDocument $doc)
    {
        $this->owned($doc);
        abort_unless($doc->type === BtDocument::QUOTATION, 404);

        $rows = $doc->lines->map(fn ($l) => [
            'item_id' => $l->item_id, 'description' => $l->description, 'qty' => (float) $l->qty,
            'unit_price' => $l->unit_price, 'unit_cost' => $l->unit_cost, 'line_total' => $l->line_total,
        ])->all();

        if (($short = $this->checkStock($rows, $doc->user_id))) {
            return back()->withErrors(['stock' => $short]);
        }

        $invoice = null;

        DB::transaction(function () use ($doc, $rows, &$invoice) {
            $invoice = $this->newDocument(BtDocument::INVOICE, [
                'contact_id' => $doc->contact_id,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => BtDocument::UNPAID,
                'notes' => $doc->notes,
                'subtotal' => $doc->subtotal,
                'discount' => $doc->discount,
                'tax_rate' => $doc->tax_rate,
                'tax_amount' => $doc->tax_amount,
                'total' => $doc->total,
            ]);

            foreach ($rows as $row) {
                $invoice->lines()->create($row);
            }

            $this->decrementStock($invoice, 'Converted from '.$doc->number);
            $doc->update(['status' => BtDocument::ACCEPTED]);
        });

        return redirect()->route('tools.doc.show', $invoice)->with('status', 'Quotation converted to '.$invoice->number.'.');
    }

    public function destroyDoc(BtDocument $doc)
    {
        $this->owned($doc);

        if ($doc->type === BtDocument::INVOICE) {
            if ($doc->paid_amount > 0 || $doc->receipts()->exists()) {
                return back()->withErrors(['doc' => 'Paid invoices cannot be deleted — they are your sales record.']);
            }
            $this->restoreStock($doc, 'Invoice '.$doc->number.' deleted');
        }

        if ($doc->type === BtDocument::RECEIPT) {
            if ($doc->invoice) {
                $invoice = $doc->invoice;
                $invoice->decrement('paid_amount', min($doc->total, $invoice->paid_amount));
                $invoice->refreshInvoiceStatus();
                $invoice->save();
            } else {
                $this->restoreStock($doc, 'Receipt '.$doc->number.' deleted');
            }
        }

        $doc->delete();

        return redirect()->route('tools.'.str($doc->type)->plural()->toString())->with('status', 'Deleted.');
    }

    public function docPdf(BtDocument $doc)
    {
        $this->owned($doc);
        $doc->load(['lines', 'contact']);

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
            'business' => BtSetting::where('user_id', $doc->user_id)->first(),
        ]);
    }

    /* ------------------------------ POS ------------------------------ */

    public function pos(Request $request)
    {
        $items = BtItem::where('user_id', $request->user()->id)
            ->where('stock', '>', 0)->orderBy('name')->get();

        return view('tools.pos', [
            'active' => 'pos',
            'items' => $items,
            'customers' => BtContact::where('user_id', $request->user()->id)
                ->where('kind', BtContact::CUSTOMER)->orderBy('name')->get(),
        ]);
    }

    public function posCheckout(Request $request)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('user_id', $userId)],
            'payment_method' => ['required', Rule::in(['cash', 'momo', 'card', 'bank'])],
            'tendered' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'lines' => ['required', 'array', 'min:1', 'max:50'],
            'lines.*.item_id' => ['required', Rule::exists('bt_items', 'id')->where('user_id', $userId)],
            'lines.*.qty' => ['required', 'numeric', 'min:0.01', 'max:999999'],
        ]);

        $rows = [];
        $subtotal = 0;

        foreach ($data['lines'] as $line) {
            $item = BtItem::where('user_id', $userId)->findOrFail($line['item_id']);
            $qty = (float) $line['qty'];
            $lineTotal = (int) round($qty * $item->sale_price);
            $rows[] = [
                'item_id' => $item->id, 'description' => $item->name, 'qty' => $qty,
                'unit_price' => $item->sale_price, 'unit_cost' => $item->cost_price, 'line_total' => $lineTotal,
            ];
            $subtotal += $lineTotal;
        }

        if ($subtotal < 1) {
            return back()->withErrors(['lines' => 'The sale total must be at least 1.'])->withInput();
        }

        if (($short = $this->checkStock($rows, $userId))) {
            return back()->withErrors(['lines' => $short])->withInput();
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

        return redirect()->route('tools.doc.show', $receipt)
            ->with('status', 'Sale complete — '.$receipt->number.($change > 0 ? '. Change: '.number_format($change) : '.'));
    }

    /* ------------------------------ expenses ------------------------------ */

    public function expenses(Request $request)
    {
        $expenses = BtExpense::where('user_id', $request->user()->id)
            ->with('supplier')->latest('date')->paginate(20);

        return view('tools.expenses', [
            'active' => 'expenses',
            'expenses' => $expenses,
            'suppliers' => BtContact::where('user_id', $request->user()->id)
                ->where('kind', BtContact::SUPPLIER)->orderBy('name')->get(),
            'monthTotal' => (int) BtExpense::where('user_id', $request->user()->id)
                ->whereDate('date', '>=', now()->startOfMonth()->toDateString())->sum('amount'),
        ]);
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'status' => ['required', Rule::in([BtExpense::PAID, BtExpense::UNPAID])],
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('user_id', $request->user()->id)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        BtExpense::create($data + ['user_id' => $request->user()->id]);

        return back()->with('status', 'Expense recorded.');
    }

    public function updateExpense(Request $request, BtExpense $expense)
    {
        $this->owned($expense);

        $expense->update($request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'status' => ['required', Rule::in([BtExpense::PAID, BtExpense::UNPAID])],
            'contact_id' => ['nullable', Rule::exists('bt_contacts', 'id')->where('user_id', $request->user()->id)],
            'note' => ['nullable', 'string', 'max:500'],
        ]));

        return back()->with('status', 'Expense updated.');
    }

    public function destroyExpense(BtExpense $expense)
    {
        $this->owned($expense);
        $expense->delete();

        return back()->with('status', 'Expense deleted.');
    }

    /* ------------------------------ reports ------------------------------ */

    private function period(Request $request): array
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());

        return [$from, $to];
    }

    public function cashflow(Request $request)
    {
        $userId = $request->user()->id;
        [$from, $to] = $this->period($request);

        $inflows = BtDocument::where('user_id', $userId)->where('type', BtDocument::RECEIPT)
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->with('contact')->orderBy('created_at')->get();
        $outflows = BtExpense::where('user_id', $userId)->where('status', BtExpense::PAID)
            ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->orderBy('date')->get();

        $inTotal = (int) $inflows->sum('total');
        $outTotal = (int) $outflows->sum('amount');

        return view('tools.cashflow', [
            'active' => 'cashflow', 'from' => $from, 'to' => $to,
            'inflows' => $inflows, 'outflows' => $outflows,
            'inTotal' => $inTotal, 'outTotal' => $outTotal, 'net' => $inTotal - $outTotal,
        ]);
    }

    public function pnl(Request $request)
    {
        $userId = $request->user()->id;
        [$from, $to] = $this->period($request);

        $revenue = (int) BtDocument::where('user_id', $userId)->where('type', BtDocument::RECEIPT)
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->sum('total');

        $paidInvoices = BtDocument::where('user_id', $userId)->where('type', BtDocument::INVOICE)
            ->where('status', BtDocument::PAID)
            ->whereDate('updated_at', '>=', $from)->whereDate('updated_at', '<=', $to)
            ->with('lines')->get();

        $cogs = (int) $paidInvoices->flatMap->lines->sum(fn ($l) => $l->qty * $l->unit_cost);

        $expenses = BtExpense::where('user_id', $userId)->where('status', BtExpense::PAID)
            ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->get();
        $expenseTotal = (int) $expenses->sum('amount');
        $byCategory = $expenses->groupBy('category')->map->sum('amount')->sortDesc();

        return view('tools.pnl', [
            'active' => 'pnl', 'from' => $from, 'to' => $to,
            'revenue' => $revenue, 'cogs' => $cogs, 'gross' => $revenue - $cogs,
            'expenseTotal' => $expenseTotal, 'net' => $revenue - $cogs - $expenseTotal,
            'byCategory' => $byCategory, 'paidCount' => $paidInvoices->count(),
        ]);
    }

    public function balancesheet(Request $request)
    {
        $userId = $request->user()->id;

        $cash = (int) BtDocument::where('user_id', $userId)->where('type', BtDocument::RECEIPT)->sum('total')
            - (int) BtExpense::where('user_id', $userId)->where('status', BtExpense::PAID)->sum('amount');
        $receivables = (int) BtDocument::where('user_id', $userId)->where('type', BtDocument::INVOICE)
            ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')->value('b');
        $inventory = (int) BtItem::where('user_id', $userId)
            ->selectRaw('COALESCE(SUM(stock * cost_price), 0) as v')->value('v');
        $payables = (int) BtExpense::where('user_id', $userId)->where('status', BtExpense::UNPAID)->sum('amount');

        $assets = $cash + $receivables + $inventory;

        return view('tools.balancesheet', [
            'active' => 'balancesheet',
            'cash' => $cash, 'receivables' => $receivables, 'inventory' => $inventory,
            'assets' => $assets, 'payables' => $payables, 'equity' => $assets - $payables,
        ]);
    }
}
