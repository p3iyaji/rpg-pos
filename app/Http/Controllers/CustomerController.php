<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Customer::paginate(100);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if ($validated) {
            $customer = Customer::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'address' => $request['address'],

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);

        }


    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return response()->json($customer);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return response()->json(['message' => 'Customer deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete customer'], 500);
        }
    }
}
