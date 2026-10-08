<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleHistoryController extends Controller
{
    public function index(Request $request)
    {
        $q = Sale::with(['customer', 'user'])->orderByDesc('sale_date');

        if ($search = $request->query('search')) {
            $q->where(function ($qq) use ($search) {
                $qq->where('invoice_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($customerId = $request->query('customer_id')) {
            $q->where('customer_id', $customerId);
        }
        if ($from = $request->query('date_from')) {
            $q->whereDate('sale_date', '>=', $from);
        }
        if ($to = $request->query('date_to')) {
            $q->whereDate('sale_date', '<=', $to);
        }
        // sembunyikan nota lama tanpa id_produk (jalur v2 saja)
        $q->whereHas('items', fn ($iq) => $iq->whereNotNull('id_produk'));

        return SaleResource::collection($q->paginate($request->query('per_page', 15)));
    }

    public function show(Sale $sale)
    {
        return new SaleResource($sale->load(['items.product', 'customer', 'user', 'payment']));
    }
}