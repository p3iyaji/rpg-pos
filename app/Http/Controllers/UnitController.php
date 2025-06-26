<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Unit::paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable'
        ]);

        if ($validated) {
            $unit = Unit::create([
                'name' => $request['name'],
                'slug' => Str::slug($request['name']),
                'description' => $request['description'],
            ]);

            return response()->json([
                'message' => 'Unit created successfully',
                'unit' => $unit,
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return response()->json($unit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $unit->update($validated);

        return response()->json($unit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        try {
            $unit->delete();
            return response()->json(['message' => 'Unit deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete unit'], 500);
        }
    }
}
