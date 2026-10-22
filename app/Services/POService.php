<?php

namespace App\Services;

use App\Models\PoItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Enums\POStatus;
use App\Support\IdGen;
use Illuminate\Support\Facades\DB;

class POService
{
    public function createPo(array $data, ?int $userId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $po = PurchaseOrder::create([
                'po_code'       => $this->generatePOCode(),
                'id_pemasok'    => $data['id_pemasok'],
                'id_acara'      => $data['id_acara'] ?? null,
                'tipe_po'       => $data['tipe_po'] ?? 'reguler',
                'user_id'       => $userId ?? auth()->id(),
                'status'        => POStatus::DRAFT->value,
                'total_amount'  => 0,
                'notes'         => $data['notes'] ?? null,
                'expected_date' => $data['expected_date'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                // Jika item membawa payload produk baru, lahirkan produk dulu (traceability).
                if (empty($item['id_produk']) && !empty($item['new_product'])) {
                    $np = $item['new_product'];
                    $idProduk = IdGen::next(Product::class, 'id_produk', 'PRD');
                    $product = Product::create([
                        'id_produk'       => $idProduk,
                        'sku'             => $np['sku'] ?? ('SKU-' . $idProduk),
                        'id_pemasok'      => $data['id_pemasok'],
                        'id_departemen'   => $np['id_departemen'],
                        'id_subkelas'     => $np['id_subkelas'] ?? null,
                        'nama_produk'     => $np['nama_produk'],
                        'penerbit'        => $np['penerbit'] ?? null,
                        'harga_jual'      => $np['harga_jual'] ?? 0,
                        'harga_beli'      => $item['unit_price'],
                        'harga_pokok'     => $item['unit_price'],
                        'stok'            => 0,
                        'is_active'       => true,
                    ]);
                    $item['id_produk'] = $idProduk;
                }

                $subtotal = $item['qty'] * $item['unit_price'];
                $total += $subtotal;
                PoItem::create([
                    'purchase_order_id' => $po->id,
                    'id_produk'         => $item['id_produk'],
                    'qty'               => $item['qty'],
                    'unit_price'        => $item['unit_price'],
                    'subtotal'          => $subtotal,
                    'received_qty'      => 0,
                ]);
            }

            $po->update(['total_amount' => $total]);

            return $po->fresh(['items', 'vendor']);
        });
    }

    public function approve(int $id): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== POStatus::DRAFT->value) {
            throw new \DomainException('Hanya PO status draft yang bisa di-approve.');
        }

        $po->update(['status' => POStatus::APPROVED->value]);

        return $po->fresh(['items', 'vendor']);
    }

    /**
     * Terima barang PO. Stok masuk lewat StockService (append-only ledger),
     * bukan increment kolom langsung.
     */
    public function receive(int $id, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $items) {
            $po = PurchaseOrder::with('items')->findOrFail($id);

            if (!in_array($po->status, [POStatus::APPROVED->value, 'partial'])) {
                throw new \DomainException('PO harus status approved atau partial sebelum receive.');
            }

            $allCompleted = true;

            foreach ($items as $row) {
                $item = $po->items->firstWhere('id', $row['id']);
                if (!$item) {
                    throw new \DomainException('Item PO tidak ditemukan.');
                }
                
                $newReceivedTotal = $item->received_qty + $row['received_qty'];

                if ($newReceivedTotal > $item->qty) {
                    throw new \DomainException("Total qty diterima ({$newReceivedTotal}) melebihi pesanan (maks: {$item->qty}).");
                }

                $item->update(['received_qty' => $newReceivedTotal]);
                
                $product = Product::findOrFail($item->id_produk);
                StockService::record($product, 'PENERIMAAN', (int) $row['received_qty'], 'purchase_order', $po->po_code, (string) auth()->id());

                if ($newReceivedTotal < $item->qty) {
                    $allCompleted = false;
                }
            }

            $newStatus = $allCompleted ? POStatus::RECEIVED->value : 'partial';

            $po->update([
                'status'        => $newStatus,
                'received_date' => now()->toDateString(),
            ]);

            return $po->fresh(['items', 'vendor']);
        });
    }

    public function cancel(int $id): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);

        if (!in_array($po->status, [POStatus::DRAFT->value, POStatus::APPROVED->value])) {
            throw new \DomainException('Hanya PO draft/approved yang bisa dibatalkan.');
        }

        $po->update(['status' => POStatus::CANCELLED->value]);

        return $po->fresh(['items', 'vendor']);
    }

    protected function generatePOCode(): string
    {
        $prefix = 'PO-' . date('Ym');
        $n = PurchaseOrder::where('po_code', 'like', "{$prefix}-%")->count() + 1;
        return $prefix . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}