<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('is_member')) {
            $query->where('is_member', true);
        }

        if ($request->boolean('birthday_this_month')) {
            $query->whereMonth('date_of_birth', now()->month);
        }

        return CustomerResource::collection($query->paginate($request->input('per_page', 15)));
    }

    public function show(int $id)
    {
        $customer = Customer::with(['sales.items.book'])->findOrFail($id);
        return new CustomerResource($customer);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'is_member' => 'boolean',
        ]);

        $customer = Customer::create($data);
        return new CustomerResource($customer);
    }

    public function update(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20|unique:customers,phone,' . $id,
            'email' => 'sometimes|nullable|email|unique:customers,email,' . $id,
            'date_of_birth' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'address' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string|max:100',
            'postal_code' => 'sometimes|nullable|string|max:10',
            'is_member' => 'sometimes|boolean',
        ]);

        $customer->update($data);
        return new CustomerResource($customer);
    }

    public function destroy(int $id)
    {
        Customer::findOrFail($id)->delete();
        return response()->json(['message' => 'Customer deleted']);
    }
}