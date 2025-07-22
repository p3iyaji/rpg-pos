<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Expense;
use Carbon\CarbonPeriod;

class ProfitAndLossService
{
    public function generateReport($startDate, $endDate, $groupBy = 'day')
    {
        // Validate groupBy parameter
        $validGroupings = ['day', 'week', 'month', 'year'];
        if (!in_array($groupBy, $validGroupings)) {
            $groupBy = 'day';
        }

        // Get sales data
        $sales = $this->getSalesData($startDate, $endDate, $groupBy);

        // Get cost of goods sold (COGS)
        $cogs = $this->getCogsData($startDate, $endDate, $groupBy);

        // Get expenses
        $expenses = $this->getExpensesData($startDate, $endDate, $groupBy);

        // Combine all data
        return $this->combineData($sales, $cogs, $expenses, $groupBy);
    }

    protected function getSalesData($startDate, $endDate, $groupBy)
    {
        return Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw($this->getDateSelect($groupBy, 'created_at') . ' as period')
            ->selectRaw('SUM(total_amount) as revenue')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');
    }

    protected function getCogsData($startDate, $endDate, $groupBy)
    {
        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw($this->getDateSelect($groupBy, 'orders.created_at') . ' as period')
            ->selectRaw('SUM(products.cost * order_items.quantity) as cogs')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');
    }

    protected function getExpensesData($startDate, $endDate, $groupBy)
    {
        return Expense::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw($this->getDateSelect($groupBy, 'date') . ' as period')
            ->selectRaw('SUM(amount) as expenses')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');
    }

    protected function combineData($sales, $cogs, $expenses, $groupBy)
    {
        // Create date range for complete periods
        $periods = CarbonPeriod::create(
            min($sales->keys()->min(), $cogs->keys()->min(), $expenses->keys()->min()),
            min($sales->keys()->max(), $cogs->keys()->max(), $expenses->keys()->max()),
            $groupBy === 'day' ? '1 day' : (
                $groupBy === 'week' ? '1 week' : (
                    $groupBy === 'month' ? '1 month' : '1 year'
                )
            )
        );

        $results = [];
        $startDate = '';
        $endDate = '';

        foreach ($periods as $period) {
            $key = $this->formatPeriodKey($period, $groupBy);

            $results[] = [
                'period' => $this->formatPeriodDisplay($period, $groupBy),
                'revenue' => $sales[$key]->revenue ?? 0,
                'cogs' => $cogs[$key]->cogs ?? 0,
                'gross_profit' => ($sales[$key]->revenue ?? 0) - ($cogs[$key]->cogs ?? 0),
                'expenses' => $expenses[$key]->expenses ?? 0,
                'net_profit' => (($sales[$key]->revenue ?? 0) - ($cogs[$key]->cogs ?? 0)) - ($expenses[$key]->expenses ?? 0),
                'gross_margin' => ($sales[$key]->revenue ?? 0) ?
                    round((($sales[$key]->revenue ?? 0) - ($cogs[$key]->cogs ?? 0)) / ($sales[$key]->revenue ?? 0) * 100, 2) : 0,
                'net_margin' => ($sales[$key]->revenue ?? 0) ?
                    round(((($sales[$key]->revenue ?? 0) - ($cogs[$key]->cogs ?? 0)) - ($expenses[$key]->expenses ?? 0)) / ($sales[$key]->revenue ?? 0) * 100, 2) : 0,
            ];
        }

        // Add summary totals
        $totals = [
            'revenue' => array_sum(array_column($results, 'revenue')),
            'cogs' => array_sum(array_column($results, 'cogs')),
            'gross_profit' => array_sum(array_column($results, 'gross_profit')),
            'expenses' => array_sum(array_column($results, 'expenses')),
            'net_profit' => array_sum(array_column($results, 'net_profit')),
        ];

        $totals['gross_margin'] = $totals['revenue'] ?
            round($totals['gross_profit'] / $totals['revenue'] * 100, 2) : 0;
        $totals['net_margin'] = $totals['revenue'] ?
            round($totals['net_profit'] / $totals['revenue'] * 100, 2) : 0;

        return [
            'periods' => $results,
            'totals' => $totals,
            'group_by' => $groupBy,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function getDateSelect($groupBy, $column)
    {
        switch ($groupBy) {
            case 'year':
                return "DATE_FORMAT($column, '%Y')";
            case 'month':
                return "DATE_FORMAT($column, '%Y-%m')";
            case 'week':
                return "DATE_FORMAT($column, '%x-%v')";
            default: // day
                return "DATE($column)";
        }
    }

    protected function formatPeriodKey($date, $groupBy)
    {
        switch ($groupBy) {
            case 'year':
                return $date->format('Y');
            case 'month':
                return $date->format('Y-m');
            case 'week':
                return $date->format('o-W');
            default: // day
                return $date->format('Y-m-d');
        }
    }

    protected function formatPeriodDisplay($date, $groupBy)
    {
        switch ($groupBy) {
            case 'year':
                return $date->format('Y');
            case 'month':
                return $date->format('F Y');
            case 'week':
                return 'Week ' . $date->format('W, Y');
            default: // day
                return $date->format('M j, Y');
        }
    }
}