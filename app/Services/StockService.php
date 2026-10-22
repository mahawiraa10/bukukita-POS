<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;

class StockService
{
    // Append-only ledger: satu-satunya jalur ubah stok. Tidak ada update/delete row stock_mutations.
    public static function record(Product $product, string $jenis, int $delta, ?string $refType = null, ?string $refId = null, ?string $userId = null): StockMutation
    {
        return DB::transaction(function () use ($product, $jenis, $delta, $refType, $refId, $userId) {
            $product = Product::where('id_produk', $product->id_produk)->lockForUpdate()->first();
            $new = $product->stok + $delta;
            if ($new < 0) {
                throw new \RuntimeException('Stok tidak mencukupi: ' . $product->nama_produk);
            }
            $product->stok = $new;
            $product->save();
            return StockMutation::create([
                'id_perubahan_stok' => self::nextId(),
                'id_produk' => $product->id_produk,
                'nama_produk' => $product->nama_produk,
                'jenis_perubahan' => $jenis,
                'jumlah_perubahan' => $delta,
                'saldo_setelah' => $new,
                'tanggal_perubahan' => now()->toDateString(),
                'ref_type' => $refType,
                'ref_id' => $refId,
                'id_pengguna' => $userId,
            ]);
        });
    }

    public static function nextId(): string
    {
        $last = StockMutation::max('id_perubahan_stok');
        $n = $last ? ((int) substr($last, 4)) + 1 : 1; // 'STK-00001' => substr mulai index 4
        return 'STK-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
    }
}
