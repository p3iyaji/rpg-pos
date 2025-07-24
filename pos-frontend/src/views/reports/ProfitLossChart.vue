<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

const todaySales = ref(0);
const monthSales = ref(0);
const profitSummary = ref({
    net_profit: 0,
    net_margin: 0
});
const topProduct = ref({});
const interval = ref(null);

const formatCurrency = (amount) => {
    const formattedAmount = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)

    return `₦${formattedAmount}`;
}

const fetchData = async () => {
    try {
        // Get today's sales
        const todayResponse = await axios.get('/api/orders/summary', {
            params: {
                start_date: new Date().toISOString().split('T')[0]

            }
        });
        todaySales.value = todayResponse.data.total_sales;

        // Get this month's sales
        const monthStart = new Date();

        monthStart.setDate(1);

        const monthResponse = await axios.get('/api/orders/summary', {
            params: {
                start_date: monthStart.toISOString().split('T')[0]
            }
        });
        monthSales.value = monthResponse.data.total_sales;

        // Get profit summary
        const profitResponse = await axios.get('/api/reports/profit-summary', {
            params: {
                start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
            }
        });
        profitSummary.value = profitResponse.data;

        // Get top product
        const topProductResponse = await axios.get('/api/products/top-selling');
        topProduct.value = topProductResponse.data.data;

    } catch (error) {
        console.error('Failed to fetch dashboard data:', error);
    }
};

onMounted(() => {
    fetchData();
    // Refresh data every 5 minutes
    interval.value = setInterval(fetchData, 5 * 60 * 1000);
});

onBeforeUnmount(() => {
    clearInterval(interval.value);
});
</script>

<template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Today's Sales Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow dark:shadow-gray-700">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Today's Sales</h3>
            <div class="text-md font-bold text-gray-900 dark:text-white">{{ formatCurrency(todaySales) }}</div>
        </div>

        <!-- Month's Sales Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow dark:shadow-gray-700">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">This Month's Sales</h3>
            <div class="text-md font-bold text-gray-900 dark:text-white">{{ formatCurrency(monthSales) }}</div>
        </div>

        <!-- Net Profit Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow dark:shadow-gray-700"
            :class="{ 'bg-red-50 dark:bg-red-900/20': profitSummary.net_profit < 0 }">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Net Profit (30 Days)</h3>
            <div class="text-md font-bold"
                :class="profitSummary.net_profit < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">
                {{ formatCurrency(profitSummary.net_profit) }}
            </div>
            <div class="text-sm mt-1"
                :class="profitSummary.net_margin < 0 ? 'text-red-500 dark:text-red-300' : 'text-gray-500 dark:text-gray-400'">
                Margin: {{ profitSummary.net_margin }}%
            </div>
        </div>

        <!-- Top Product Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow dark:shadow-gray-700">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Top Selling Product</h3>
            <div class="text-md font-semibold text-gray-900 dark:text-white mb-1">
                {{ topProduct.name || 'N/A' }}
            </div>
            <div class="text-md font-bold text-teal-600 dark:text-teal-400">
                {{ formatCurrency(topProduct.revenue || 0) }}
            </div>
        </div>
    </div>
</template>