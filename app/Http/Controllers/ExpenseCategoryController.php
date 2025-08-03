<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return ExpenseCategory::OrderBy('name', 'ASC')->paginate(100);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        if ($validated) {
            $expenseCategory = ExpenseCategory::create([
                'name' => $request->name,

            ]);

            return response()->json([
                'message' => 'Category created successfully',
                'expense' => $expenseCategory,
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseCategory $expenseCategory)
    {
        return response()->json($expenseCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',

        ]);
        $expenseCategory->update($validated);
        return response()->json($expenseCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        try {
            $expenseCategory->delete();
            return response()->json(['message' => 'Expense category deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete expense category'], 500);
        }
    }
}
