<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return User::with('role')->paginate($request->input('per_page', 20));
    }

    public function show(string $id)
    {
        return User::with('role')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:8',
            'pin' => 'nullable|digits:6', // untuk supervisor otorisasi refund/void
        ]);
        $data['password'] = Hash::make($data['password']);
        if (!empty($data['pin'])) { $data['pin'] = Hash::make($data['pin']); }
        return response()->json(User::create($data), 201);
    }

    public function update(Request $request, string $id)
    {
        $u = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:150',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role_id' => 'sometimes|exists:roles,id',
            'password' => 'sometimes|string|min:8',
            'pin' => 'sometimes|digits:6',
            'is_active' => 'sometimes|boolean',
        ]);
        if (!empty($data['password'])) { $data['password'] = Hash::make($data['password']); } else { unset($data['password']); }
        if (!empty($data['pin'])) { $data['pin'] = Hash::make($data['pin']); }
        $u->update($data);
        return response()->json($u);
    }

    public function destroy(string $id)
    {
        User::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['message' => 'akun dinonaktifkan, histori tetap tersimpan (Function 11)']);
    }
}
