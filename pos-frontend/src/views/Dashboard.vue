<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { useAuthStore } from '@/stores/authStore';
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

// Reactive data
const todaySales = ref(0)
const monthSales = ref(0)
const authStore = useAuthStore();

const profitSummary = ref({
    net_profit: 0,
    net_margin: 0
})
const topProduct = ref({})
const interval = ref(null)

// Chart references
const salesChartRef = ref(null);
const productsChartRef = ref(null);

// Format currency values
const formatCurrency = (amount) => {
    const formattedAmount = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)

    return `₦${formattedAmount}`;
}

const fetchData = async () => {
    authStore.isAuthenticated;
    try {
        const today = new Date().toISOString().split('T')[0]
        const monthStart = new Date(new Date().setDate(1)).toISOString().split('T')[0]
        const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]

        // Parallel API requests with better error handling
        const requests = [
            axios.get('/api/orders/summary', { params: { start_date: today } })
                .then(res => {
                    if (res.data.success) {
                        todaySales.value = res.data.total_sales
                    } else {
                        console.error('Today sales error:', res.data.message)
                        todaySales.value = 0
                    }
                })
                .catch(err => {
                    console.error('Today sales fetch failed:', err)
                    todaySales.value = 0
                }),

            axios.get('/api/orders/summary', { params: { start_date: monthStart } })
                .then(res => {
                    if (res.data.success) {
                        monthSales.value = res.data.total_sales
                    } else {
                        console.error('Month sales error:', res.data.message)
                        monthSales.value = 0
                    }
                })
                .catch(err => {
                    console.error('Month sales fetch failed:', err)
                    monthSales.value = 0
                }),

            axios.get('/api/reports/profit-summary', { params: { start_date: thirtyDaysAgo } })
                .then(res => {
                    profitSummary.value = res.data || { net_profit: 0, net_margin: 0 }
                })
                .catch(err => {
                    console.error('Profit summary fetch failed:', err)
                    profitSummary.value = { net_profit: 0, net_margin: 0 }
                }),
            axios.get('/api/products/top-selling')
                .then(res => {
                    const productData = res.data.data || res.data;
                    topProduct.value = {
                        name: productData?.name || 'N/A',
                        revenue: parseFloat(productData?.revenue) || 0,
                        total_quantity: parseInt(productData?.total_quantity) || 0,
                        image: productData?.image || null
                    };
                })
                .catch(err => {
                    console.error('Top product fetch failed:', err);
                    topProduct.value = {
                        name: 'N/A',
                        revenue: 0,
                        total_quantity: 0,
                        image: null
                    };
                }),
            // New endpoint for sales data
            axios.get('/api/reports/sales-data', { params: { days: 7 } })
                .then(res => {
                    if (res.data.success) {
                        renderSalesChart(res.data.data);
                    }
                })
                .catch(err => {
                    console.error('Sales data fetch failed:', err);
                }),
            // New endpoint for profit data
            axios.get('/api/reports/top-products', { params: { limit: 5 } })
                .then(res => {
                    if (res.data.success) {
                        renderProductsChart(res.data.data);
                    }
                })
                .catch(err => {
                    console.error('Profit data fetch failed:', err);
                })
        ]

        await Promise.all(requests)

        // Process responses
        // if (responses[0].data.success) todaySales.value = responses[0].data.total_sales;
        // if (responses[1].data.success) monthSales.value = responses[1].data.total_sales;
        // profitSummary.value = responses[2].data || { net_profit: 0, net_margin: 0 };

        // const productData = responses[3].data.data || responses[3].data;
        // topProduct.value = {
        //     name: productData?.name || 'N/A',
        //     revenue: parseFloat(productData?.revenue) || 0,
        //     total_quantity: parseInt(productData?.total_quantity) || 0,
        //     image: productData?.image || null
        // };

        // if (responses[4].data.success) renderSalesChart(responses[4].data.data);
        // if (responses[5].data.success) renderProductsChart(responses[5].data.data);

    } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
    }
}

// Render sales chart
const renderSalesChart = (data) => {
    const ctx = document.getElementById('salesChart');

    // Destroy previous chart if it exists
    if (salesChartRef.value) {
        salesChartRef.value.destroy();
    }

    salesChartRef.value = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Daily Sales',
                data: data.values,
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

// Render top product chart
const renderProductsChart = (data) => {
    const ctx = document.getElementById('productsChart');

    if (productsChartRef.value) productsChartRef.value.destroy();

    productsChartRef.value = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Quantity Sold',
                data: data.quantities,
                backgroundColor: 'rgba(234, 179, 8, 0.7)',
                borderColor: 'rgba(234, 179, 8, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Revenue',
                data: data.revenues,
                backgroundColor: 'rgba(139, 92, 246, 0.7)',
                borderColor: 'rgba(139, 92, 246, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.datasetIndex === 0) {
                                label += context.raw;
                            } else {
                                label += formatCurrency(context.raw);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Quantity' },
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Revenue' },
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: function (value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

// Lifecycle hooks
onMounted(() => {
    document.title = 'Dashboard - RPG-Pos'

    fetchData()
    interval.value = setInterval(fetchData, 5 * 60 * 1000) // Refresh every 5 minutes
})

onBeforeUnmount(() => {
    clearInterval(interval.value)
    // Clean up charts
    if (salesChartRef.value) {
        salesChartRef.value.destroy();
    }
    // if (profitChartRef.value) {
    //     profitChartRef.value.destroy();
    // }
    if (productsChartRef.value) productsChartRef.value.destroy();

})
</script>

<template>
    <AppLayout>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Today's Sales -->
            <div
                class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border border-blue-100 dark:border-blue-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-medium text-blue-600 dark:text-blue-200">Today's Sales</h3>
                    <div class="text-blue-400 dark:text-blue-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2md font-bold text-blue-800 dark:text-blue-100">{{ formatCurrency(todaySales) }}</div>
            </div>

            <!-- Monthly Sales -->
            <div
                class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border border-purple-100 dark:border-purple-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-medium text-purple-600 dark:text-purple-200">This Month's Sales</h3>
                    <div class="text-purple-400 dark:text-purple-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2md font-bold text-purple-800 dark:text-purple-100">{{ formatCurrency(monthSales) }}
                </div>
            </div>

            <!-- Net Profit -->
            <div class="p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border" :class="profitSummary.net_profit < 0
                ? 'bg-red-50 dark:bg-red-900/20 border-red-100 dark:border-red-800'
                : 'bg-green-50 dark:bg-green-900/20 border-green-100 dark:border-green-800'">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-medium" :class="profitSummary.net_profit < 0
                        ? 'text-red-600 dark:text-red-200'
                        : 'text-green-600 dark:text-green-200'">
                        Net Profit (30 Days)
                    </h3>
                    <div :class="profitSummary.net_profit < 0
                        ? 'text-red-400 dark:text-red-300'
                        : 'text-green-400 dark:text-green-300'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="text-2md font-bold" :class="profitSummary.net_profit < 0
                    ? 'text-red-800 dark:text-red-100'
                    : 'text-green-800 dark:text-green-100'">
                    {{ formatCurrency(profitSummary.net_profit) }}
                </div>
                <div class="text-sm mt-1" :class="profitSummary.net_margin < 0
                    ? 'text-red-500 dark:text-red-300'
                    : 'text-green-500 dark:text-green-300'">
                    Margin: {{ profitSummary.net_margin }}%
                </div>
            </div>

            <!-- Top Product -->
            <div
                class="bg-amber-50 dark:bg-amber-900/20 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border border-amber-100 dark:border-amber-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-medium text-amber-600 dark:text-amber-200">Top Selling Product</h3>
                    <div class="text-amber-400 dark:text-amber-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                </div>
                <div class="text-md font-semibold text-amber-800 dark:text-amber-100 mb-2">
                    {{ topProduct.name || 'N/A' }}
                </div>
                <div class="text-md font-bold text-amber-600 dark:text-amber-300">
                    {{ formatCurrency(topProduct.revenue || 0) }}
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Sales Chart -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Weekly Sales</h3>
                <div class="h-80">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Top Products Chart (replaces Profit Chart) -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Top Selling Products</h3>
                <div class="h-80">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>
    </AppLayout>
</template>