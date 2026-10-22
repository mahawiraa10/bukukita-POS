<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        return Role::all();
    }

    public function update(Request $request, string $id)
    {
        // Proteksi: Role Owner hanya bisa diedit oleh User dengan role Owner itu sendiri
        $caller = auth()->user();
        $targetRole = Role::where('id_divisi', $id)->first(); // or whatever PK is used
        
        if ($targetRole->name === 'owner' && $caller->role_id !== 1) { // Assuming owner role id is 1
            return response()->json(['message' => 'Role Owner hanya bisa diedit oleh role Owner itu sendiri.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $targetRole->update($validated);
        
        return response()->json($targetRole);
    }
}
