<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $svc) {}

    public function kpi(Request $r) { return response()->json($this->svc->kpi($r->query('from'), $r->query('to'))); }
    public function salesTrend(Request $r) { return response()->json($this->svc->salesTrend($r->query('period', 'monthly'), $r->query('from'), $r->query('to'))); }
    public function demographics(Request $r) { return response()->json($this->svc->demographics($r->query('from'), $r->query('to'))); }
    public function topProducts(Request $r) { return response()->json($this->svc->topProducts((int) $r->query('limit', 10), $r->query('from'), $r->query('to'))); }
    public function lowStock(Request $r) { return response()->json($this->svc->lowStock((int) $r->query('threshold', 5))); }
    public function eventRevenue() { return response()->json($this->svc->eventRevenue()); }
}