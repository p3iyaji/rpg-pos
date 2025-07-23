<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function index()
    {
        return Expense::with('expenseCategory')->paginate(100);

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expense_category_id' => 'required',
            'amount' => 'required|decimal:0,2',
            'description' => 'nullable|string',
            'date' => 'required|string'
        ]);
        if ($validated) {
            $expense = Expense::create([
                'name' => $request->name,
                'description' => $request->description,
                'date' => $request->date,
                'expense_category_id' => $request->expense_category_id,
                'amount' => $request->amount,
                'user_id' => auth()->user()->id,

            ]);

            return response()->json([
                'message' => 'Expense created successfully',
                'expense' => $expense,
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expense_category_id' => 'required',
            'amount' => 'required|decimal:0,2',
            'description' => 'nullable|string',
            'date' => 'required|string'


        ]);
        $expense->update([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'user_id' => auth()->user()->id,
        ]);
        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        try {
            $expense->delete();
            return response()->json(['message' => 'Expense deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete expense'], 500);
        }
    }
}
