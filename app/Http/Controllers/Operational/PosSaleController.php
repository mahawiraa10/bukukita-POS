<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Master\MemberController;
use App\Models\CashierShift;
use App\Models\Event;
use App\Models\LoyaltyRule;
use App\Models\MemberTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\StockService;
use App\Support\IdGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Transaksi POS jalur baru (spec Excel: sesi_till + products + diskon berlapis + event).
 * Legacy POST /api/sales (books) tetap ada buat kompatible.
 */
class PosSaleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.diskon_tambahan' => 'nullable|numeric|min:0|max:100', // % manual per item
            'id_anggota' => 'nullable|string',   // MBR-xxxxx atau No HP
            'metode_bayar' => 'required|in:TUNAI,DEBIT',
            'uang_diterima' => 'nullable|numeric|min:0',
            'no_referensi_bank' => 'nullable|string|max:100',
            'catatan' => 'nullable|string|max:255',
            'original_invoice_code' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $shift = CashierShift::where('id_pengguna', (string) $user->id)
            ->where('status_sesi', 'BUKA')->latest('waktu_buka')->first();
        if (!$shift) {
            abort(422, 'Tidak ada sesi kasir aktif. Buka shift (TILL) dulu.');
        }

        $member = null;
        if (!empty($data['id_anggota'])) {
            $member = DB::table('customers')
                ->where('id_anggota', $data['id_anggota'])
                ->orWhere('phone', $data['id_anggota'])
                ->where('is_member', 1)->first();
            if (!$member) {
                abort(422, 'Member tidak ditemukan / bukan member aktif');
            }
        }

        // Event aktif hari ini (diskon acara, Function #12)
        $event = Event::where('status_acara', 'AKTIF')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_selesai', '>=', today())
            ->first();

        $rule = LoyaltyRule::first();
        $tierDisk = 0;
        if ($member) {
            $tierDisk = match ($member->tier) {
                'gold' => (float) ($rule->diskon_gold ?? 10),
                'silver' => (float) ($rule->diskon_silver ?? 5),
                default => 0,
            };
        }

        return DB::transaction(function () use ($data, $user, $shift, $member, $event, $rule, $tierDisk) {
            $subtotal = 0;
            $diskonTotal = 0;
            $lineItems = [];

            foreach ($data['items'] as $it) {
                $p = Product::where('id_produk', $it['id_produk'])->where('is_active', 1)->first();
                if (!$p) {
                    throw new \RuntimeException("Produk {$it['id_produk']} tidak ditemukan/nonaktif");
                }
                if ($p->stok < $it['qty']) {
                    throw new \RuntimeException("Stok '{$p->nama_produk}' tidak cukup (sisa: {$p->stok})");
                }
                $harga = (float) $p->harga_jual;
                $bruto = $harga * $it['qty'];
                // Berlapis: base -> tambahan -> tier -> event. Cap 50% per item (Function #12).
                $persen = (float) ($p->diskon_base ?? 0)
                    + (float) ($it['diskon_tambahan'] ?? 0)
                    + $tierDisk
                    + ($event && $event->jenis_diskon === 'PERSEN' ? (float) $event->nilai_diskon : 0);
                $persen = min($persen, 50);
                $diskonItem = round($bruto * $persen / 100, 2);
                if ($event && $event->jenis_diskon === 'NILAI') {
                    $diskonItem = min($diskonItem + (float) $event->nilai_diskon * $it['qty'], round($bruto * 0.5, 2));
                }
                $subtotal += $bruto;
                $diskonTotal += $diskonItem;
                $lineItems[] = ['produk' => $p, 'qty' => $it['qty'], 'bruto' => $bruto, 'diskon' => $diskonItem];
            }
            $final = round($subtotal - $diskonTotal, 2);

            // Validasi tunai
            $kembali = 0;
            if ($data['metode_bayar'] === 'TUNAI') {
                if (empty($data['uang_diterima']) || $data['uang_diterima'] < $final) {
                    throw new \RuntimeException('Uang diterima kurang dari total bayar');
                }
                $kembali = round($data['uang_diterima'] - $final, 2);
            }

            $sale = Sale::create([
                'invoice_code' => $this->invoice($shift),
                'customer_id' => $member?->id,
                'user_id' => $user->id,
                'total_amount' => $subtotal,
                'discount' => $diskonTotal,
                'final_amount' => $final,
                'payment_method' => $data['metode_bayar'] === 'DEBIT' ? 'debit' : 'cash',
                'payment_status' => 'paid',
                'sale_date' => now(),
                'notes' => $data['catatan'] ?? null,
                'id_sesi_kasir' => $shift->id_sesi_kasir,
                'id_acara' => $event?->id_acara,
                'business_date' => today()->toDateString(),
                'original_invoice_code' => $data['original_invoice_code'] ?? null,
            ]);

            foreach ($lineItems as $li) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'book_id' => null,
                    'id_produk' => $li['produk']->id_produk,
                    'qty' => $li['qty'],
                    'price' => (float) $li['produk']->harga_jual,
                    'hpp_snapshot' => (float) $li['produk']->harga_pokok, // kunci modal saat transaksi
                    'subtotal' => $li['bruto'] - $li['diskon'],
                    'diskon_produk' => $li['diskon'],
                ]);
                StockService::record($li['produk'], 'PENJUALAN', -$li['qty'], 'sale', $sale->invoice_code, (string) $user->id);
            }

            Payment::create([
                'sale_id' => $sale->id,
                'amount' => $final,
                'payment_method' => $sale->payment_method,
                'transaction_id' => $data['no_referensi_bank'] ?? null,
                'status' => 'success',
                'paid_at' => now(),
            ]);

            // Loyalty: poin = floor(final / nilai_transaksi) * nilai_poin; jurnal member_transactions; tier auto
            $poinDapat = 0;
            if ($member) {
                $poinDapat = (int) floor($final / max(1, (float) ($rule->nilai_transaksi ?? 10000)) * (int) ($rule->nilai_poin ?? 1));
                DB::table('customers')->where('id', $member->id)->increment('total_spent', $final);
                DB::table('customers')->where('id', $member->id)->increment('total_orders');
                if ($poinDapat > 0) {
                    DB::table('customers')->where('id', $member->id)->increment('points', $poinDapat);
                }
                $fresh = DB::table('customers')->where('id', $member->id)->first();
                MemberTransaction::create([
                    'id_transaksi_anggota' => IdGen::next(MemberTransaction::class, 'id_transaksi_anggota', 'MTX'),
                    'id_anggota' => $fresh->id_anggota,
                    'id_transaksi' => $sale->invoice_code,
                    'id_poin' => $rule?->id_poin,
                    'poin_diperoleh' => $poinDapat,
                    'saldo_poin' => (int) $fresh->points,
                ]);
                $tierBaru = MemberController::tierFor((float) $fresh->total_spent, $rule);
                if ($tierBaru !== $fresh->tier) {
                    DB::table('customers')->where('id', $member->id)->update(['tier' => $tierBaru]);
                }
            }

            return response()->json([
                'sale' => $sale->fresh(),
                'items' => collect($lineItems)->map(fn ($li) => [
                    'id_produk' => $li['produk']->id_produk,
                    'nama_produk' => $li['produk']->nama_produk,
                    'qty' => $li['qty'],
                    'bruto' => $li['bruto'],
                    'diskon' => $li['diskon'],
                    'net' => $li['bruto'] - $li['diskon'],
                ]),
                'event' => $event?->only(['id_acara', 'nama_acara', 'jenis_diskon', 'nilai_diskon']),
                'kembali' => $kembali,
                'poin_diperoleh' => $poinDapat,
            ], 201);
        });
    }

    private function invoice(CashierShift $shift): string
    {
        $base = 'INV/' . now()->format('Ymd') . '/' . $shift->nomor_kasir . '/';
        $last = Sale::where('invoice_code', 'like', $base . '%')->orderByDesc('invoice_code')->value('invoice_code');
        $n = $last ? ((int) substr($last, strlen($base))) + 1 : 1;
        return $base . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
