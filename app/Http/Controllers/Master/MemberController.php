<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyRule;
use App\Models\MemberTransaction;
use App\Support\IdGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    private const TIERS = ['bronze', 'silver', 'gold'];

    private function byKey(string $id)
    {
        return Customer::where('id_anggota', $id)->orWhere('phone', $id)->first();
    }

    public function index(Request $request)
    {
        $q = Customer::query();
        if ($s = $request->query('cari')) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")->orWhere('city', 'like', "%$s%"));
        }
        foreach (['tier', 'city', 'gender'] as $f) {
            if ($v = $request->query($f)) {
                $q->where($f, $v);
            }
        }
        $sort = $request->query('sort', 'total_spent');
        return $q->orderByDesc($sort)->paginate(20);
    }

    public function show(string $id)
    {
        $m = $this->byKey($id) ?? abort(404, 'Member tidak ditemukan');
        $rule = LoyaltyRule::first();
        return response()->json([
            'member' => $m,
            'aturan_poin' => $rule,
            'riwayat' => MemberTransaction::where('id_anggota', $m->id_anggota)->orderByDesc('created_at')->limit(50)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'city' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
        ]);
        $last = Customer::max('id');
        $data['id_anggota'] = 'MBR-' . str_pad((string) ($last + 1), 5, '0', STR_PAD_LEFT);
        $data['is_member'] = 1;
        $data['member_since'] = today();
        $data['tier'] = 'bronze';
        return response()->json(Customer::create($data)->fresh(), 201);
    }

    public function update(Request $request, string $id)
    {
        $m = $this->byKey($id) ?? abort(404);
        $data = $request->validate([
            'name' => 'sometimes|string|max:120',
            'city' => 'sometimes|string|max:100',
            'gender' => 'sometimes|in:male,female,other',
            'email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
        ]);
        $m->update($data);
        return $m->fresh();
    }

    public function destroy(string $id)
    {
        $m = $this->byKey($id) ?? abort(404);
        // Nonaktif, bukan hapus: LTV & histori member tidak boleh hilang dari laporan
        $m->update(['is_member' => 0]);
        return response()->json(['message' => 'member dinonaktifkan (data tetap tersimpan)']);
    }

    /** Earn/redeem poin manual + recheck tier (Function #10 CRM) */
    public function points(Request $request, string $id)
    {
        $m = $this->byKey($id) ?? abort(404);
        $data = $request->validate([
            'jenis' => 'required|in:EARN,REDEEM',
            'poin' => 'required|integer|min:1',
            'catatan' => 'nullable|string|max:255',
        ]);
        $rule = LoyaltyRule::first();
        $delta = $data['jenis'] === 'EARN' ? $data['poin'] : -$data['poin'];
        if ($data['jenis'] === 'REDEEM' && $m->points < $data['poin']) {
            abort(422, 'Poin tidak cukup untuk redeem');
        }
        $baru = max(0, (int) $m->points + $delta);
        DB::transaction(function () use ($m, $baru, $data, $delta, $rule) {
            $m->update(['points' => $baru]);
            $this->syncTier($m);
            MemberTransaction::create([
                'id_transaksi_anggota' => IdGen::next(MemberTransaction::class, 'id_transaksi_anggota', 'MTX'),
                'id_anggota' => $m->id_anggota,
                'id_poin' => $rule?->id_poin,
                'poin_diperoleh' => $delta > 0 ? $delta : 0,
                'poin_digunakan' => $delta < 0 ? abs($delta) : null,
                'saldo_poin' => $baru,
            ]);
        });
        return response()->json(['points' => $baru, 'tier' => $m->fresh()->tier], 201);
    }

    /** Tier naik berdasarkan akumulasi belanja (total_spent) — spec: silver 500rb, gold 2jt. Satu arah, tidak turun. */
    public static function tierFor(float $spent, ?LoyaltyRule $rule): string
    {
        $rule = $rule ?? LoyaltyRule::first();
        if ($spent >= (float) ($rule->tier_gold_min ?? 2000000)) return 'gold';
        if ($spent >= (float) ($rule->tier_silver_min ?? 500000)) return 'silver';
        return 'bronze';
    }

    private function syncTier(Customer $m): void
    {
        $baru = self::tierFor((float) $m->total_spent);
        if ($baru !== $m->tier) $m->update(['tier' => $baru]);
    }
}
