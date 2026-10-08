<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\StockService;
use App\Support\IdGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RefundController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_transaksi_referensi' => 'required|string', // no invoice lama
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'sumber_pengembalian' => 'required|string',
            'pin_supervisor' => 'required|string',
        ]);

        // 1) Same-day guard: tolak kalau bukan business_date hari ini (lewat 00:00 = hangus)
        $sale = DB::table('sales')->where('invoice_code', $data['id_transaksi_referensi'])->first();
        if (!$sale) {
            abort(404, 'Nota tidak ditemukan');
        }
        if ((string) ($sale->business_date ?? now()->toDateString()) !== today()->toDateString()) {
            abort(403, 'Refund ditolak: hanya untuk transaksi hari yang sama');
        }

        // 2) PIN supervisor
        $supervisor = \App\Models\User::where('role_id', 2)->where('is_active', 1)->first();
        if (!$supervisor || !$supervisor->pin || !Hash::check($data['pin_supervisor'], $supervisor->pin)) {
            abort(422, 'PIN supervisor salah');
        }

        $totalUang = 0;
        DB::beginTransaction();
        try {
            foreach ($data['items'] as $it) {
                $item = SaleItem::where('sale_id', $sale->id)->where('id_produk', $it['id_produk'])->firstOrFail();
                $product = \App\Models\Product::findOrFail($it['id_produk']);
                $nilai = round($item->price * $it['qty'], 2);
                $totalUang += $nilai;
                Refund::create([
                    'id_pengembalian' => IdGen::next(Refund::class, 'id_pengembalian', 'RFD'),
                    'id_produk' => $product->id_produk,
                    'id_transaksi_referensi' => $data['id_transaksi_referensi'],
                    'tanggal_pengembalian' => today(),
                    'jumlah_pengembalian' => $nilai,
                    'sumber_pengembalian' => $data['sumber_pengembalian'],
                    'jumlah_dikembalikan' => $it['qty'],
                    'jumlah_nilai' => $nilai,
                ]);
                StockService::record($product, 'REFUND', $it['qty'], 'refund', $data['id_transaksi_referensi'], (string) auth()->id());
            }
            // ponytail: status sale dibiarkan 'paid'; jurnal refund ada di tabel refunds.
            // Kalau butuh flag "sebagian dibatalkan", tambah kolom refunded_amount saat dibutuhkan.
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return response()->json(['message' => 'refund sukses', 'nilai_refund' => $totalUang], 201);
    }
}
