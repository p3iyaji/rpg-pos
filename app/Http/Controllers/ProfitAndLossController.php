<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProfitAndLossService;
use Illuminate\Http\Request;

class ProfitAndLossController extends Controller
{
    protected $plService;

    public function __construct(ProfitAndLossService $plService)
    {
        $this->plService = $plService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'sometimes|in:day,week,month,year'
        ]);

        $report = $this->plService->generateReport(
            $validated['start_date'],
            $validated['end_date'],
            $validated['group_by'] ?? 'day'
        );

        return response()->json($report);
    }

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|required_with:start_date'
        ]);

        $startDate = $validated['start_date'] ?? now()->subMonth()->startOfDay();
        $endDate = $validated['end_date'] ?? now()->endOfDay();

        $report = $this->plService->generateReport($startDate, $endDate, 'month');

        return response()->json([
            'gross_profit' => $report['totals']['gross_profit'],
            'net_profit' => $report['totals']['net_profit'],
            'gross_margin' => $report['totals']['gross_margin'],
            'net_margin' => $report['totals']['net_margin'],
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ]
        ]);
    }

}
