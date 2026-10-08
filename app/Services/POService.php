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
    public function createPo(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'po_code'       => $this->generatePOCode(),
                'id_pemasok'    => $data['id_pemasok'],
                'id_acara'      => $data['id_acara'] ?? null,
                'user_id'       => auth()->id(),
                'status'        => POStatus::DRAFT->value,
                'total_amount'  => 0,
                'notes'         => $data['notes'] ?? null,
                'expected_date' => $data['expected_date'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
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

            if ($po->status !== POStatus::APPROVED->value) {
                throw new \DomainException('PO harus di-approve dulu sebelum receive.');
            }

            foreach ($items as $row) {
                $item = $po->items->firstWhere('id', $row['id']);
                if (!$item) {
                    throw new \DomainException('Item PO tidak ditemukan.');
                }
                if ($row['received_qty'] > $item->qty) {
                    throw new \DomainException("Qty diterima melebihi pesanan (maks: {$item->qty}).");
                }

                $item->update(['received_qty' => $row['received_qty']]);
                $product = Product::findOrFail($item->id_produk);
                StockService::record($product, 'PENERIMAAN', (int) $row['received_qty'], 'purchase_order', $po->po_code, (string) auth()->id());
            }

            $po->update([
                'status'        => POStatus::RECEIVED->value,
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