<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class ReportController extends Controller
{
    public function salesData(Request $request)
    {
        $days = $request->input('days', 7);

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('D, M j');

            $sales = Order::whereDate('created_at', $date)
                ->sum('total');

            $values[] = $sales ?? 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }

    public function topProducts(Request $request)
    {
        $limit = $request->input('limit', 5);

        $products = Product::select('products.id', 'products.name')
            ->selectRaw('SUM(order_items.quantity) as total_quantity')
            ->selectRaw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $products->pluck('name')->toArray(),
                'quantities' => $products->pluck('total_quantity')->toArray(),
                'revenues' => $products->pluck('total_revenue')->toArray()
            ]
        ]);
    }


    // public function profitSummary(Request $request)
    // {
    //     $startDate = $request->input('start_date');

    //     $totalSales = Order::where('created_at', '>=', $startDate)
    //         ->sum('total_amount');

    //     // Calculate cost of goods sold (COGS) if you have this data
    //     // This is just an example - adjust based on your actual data structure
    //     $cogs = OrderItem::whereHas('order', function ($q) use ($startDate) {
    //         $q->where('created_at', '>=', $startDate);
    //     })
    //         ->sum(DB::raw('quantity * cost_price')); // Assuming you have cost_price

    //     $netProfit = $totalSales - $cogs;
    //     $netMargin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;

    //     return response()->json([
    //         'net_profit' => $netProfit,
    //         'net_margin' => round($netMargin, 2)
    //     ]);
    // }
}
