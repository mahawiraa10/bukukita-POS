<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyRule;

class LoyaltyRuleController extends Controller
{
    public function show()
    {
        return LoyaltyRule::first() ?? response()->json(['data' => null]);
    }

    public function update(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'nilai_transaksi' => 'required|numeric|min:1',
            'nilai_poin' => 'required|integer|min:1',
            'tier_silver_min' => 'nullable|integer|min:0',
            'tier_gold_min' => 'nullable|integer|min:0',
            'diskon_silver' => 'nullable|numeric|min:0|max:50',
            'diskon_gold' => 'nullable|numeric|min:0|max:50',
        ]);
        $r = LoyaltyRule::first() ?: new LoyaltyRule(['id_poin' => 'PNT-00001']);
        $r->fill($data); $r->save();
        return response()->json($r);
    }
}
