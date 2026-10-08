<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function kpi(?string $from, ?string $to): array
    {
        $q = Sale::query()->when($from, fn ($qq) => $qq->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($qq) => $qq->whereDate('sale_date', '<=', $to));

        $totalRevenue = (float) $q->clone()->sum('final_amount');
        $totalOrders = (int) $q->clone()->count();
        $avgOrder = $totalOrders ? round($totalRevenue / $totalOrders, 2) : 0;
        $activeCustomers = (int) $q->clone()->whereNotNull('customer_id')->distinct('customer_id')->count('customer_id');
        $totalItems = (int) DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($from, fn ($qq) => $qq->whereDate('sales.sale_date', '>=', $from))
            ->when($to, fn ($qq) => $qq->whereDate('sales.sale_date', '<=', $to))
            ->sum('sale_items.qty');

        return [
            'totalRevenue'    => $totalRevenue,
            'totalOrders'     => $totalOrders,
            'avgOrder'        => $avgOrder,
            'activeCustomers' => $activeCustomers,
            'totalItems'      => $totalItems,
            // alias lama biar frontend lama nggak pecah
            'totalBooks'      => $totalItems,
        ];
    }

    public function salesTrend(string $period, ?string $from, ?string $to): array
    {
        $format = match ($period) {
            'daily' => '%Y-%m-%d',
            'yearly' => '%Y',
            default => '%Y-%m',
        };

        return Sale::selectRaw("DATE_FORMAT(sale_date, '{$format}') as label, SUM(final_amount) as revenue, COUNT(*) as orders, SUM(final_amount)/COUNT(*) as avg_order")
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->groupBy('label')->orderBy('label')->get()->toArray();
    }

    public function demographics(?string $from, ?string $to): array
    {
        $salesQ = Sale::query()->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->whereNotNull('customer_id');

        $customerIds = $salesQ->pluck('customer_id')->unique();

        $byCity = Customer::whereIn('id', $customerIds)->selectRaw('city, COUNT(*) as count')->groupBy('city')->orderByDesc('count')->get();
        $byGender = Customer::whereIn('id', $customerIds)->selectRaw('gender, COUNT(*) as count')->groupBy('gender')->get();
        $byAge = Customer::whereIn('id', $customerIds)->whereNotNull('date_of_birth')
            ->selectRaw("CASE WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN '<18' WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 25 THEN '18-25' WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 26 AND 35 THEN '26-35' WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 50 THEN '36-50' ELSE '50+' END as range_age, COUNT(*) as count")
            ->groupBy('range_age')->get();

        return ['by_city' => $byCity, 'by_gender' => $byGender, 'by_age' => $byAge];
    }

    /**
     * Produk terlaris. Baca jalur v2 (sale_items.id_produk).
     */
    public function topProducts(int $limit = 10, ?string $from = null, ?string $to = null): array
    {
        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id_produk', '=', 'sale_items.id_produk')
            ->whereNotNull('sale_items.id_produk')
            ->when($from, fn ($q) => $q->whereDate('sales.sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sales.sale_date', '<=', $to))
            ->selectRaw('products.id_produk, products.nama_produk, products.penerbit, SUM(sale_items.qty) as total_qty, SUM(sale_items.subtotal) as total_revenue')
            ->groupBy('products.id_produk', 'products.nama_produk', 'products.penerbit')
            ->orderByDesc('total_qty')->limit($limit)->get()->toArray();
    }

    public function lowStock(int $threshold = 5): array
    {
        return Product::whereColumn('stok', '<=', 'min_stock')->orWhere('stok', '<=', $threshold)
            ->select('id_produk', 'nama_produk', 'penerbit', 'stok', 'min_stock', 'id_subkelas')
            ->orderBy('stok')->get()->toArray();
    }

    /**
     * Perbandingan omzet antar event. Event tanpa penjualan tetap muncul (revenue 0).
     */
    public function eventRevenue(): array
    {
        return DB::table('events')
            ->leftJoin('sales', 'sales.id_acara', '=', 'events.id_acara')
            ->selectRaw("events.id_acara, events.nama_acara, events.status_acara,
                COALESCE(SUM(sales.final_amount), 0) as revenue,
                COUNT(sales.id) as orders")
            ->groupBy('events.id_acara', 'events.nama_acara', 'events.status_acara')
            ->orderByDesc('revenue')->get()->toArray();
    }
}