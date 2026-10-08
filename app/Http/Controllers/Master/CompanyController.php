<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show()
    {
        return Company::first() ?? response()->json(['data' => null]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_perusahaan' => 'required|string|max:100',
            'alamat_perusahaan' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'email' => 'nullable|email',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);
        $c = Company::first() ?: new Company(['id_perusahaan' => 1]);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('public/company');
        }
        $c->fill($data);
        $c->save();
        return response()->json($c);
    }
}
