<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{

    protected $table = 'expense_categories';
    protected $fillable = [
        'name',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
