<script setup>
import AppLayout from '@/components/AppLayout.vue';

import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

// Reactive data
const todaySales = ref(0)
const monthSales = ref(0)
const profitSummary = ref({
    net_profit: 0,
    net_margin: 0
})
const topProduct = ref({})
const interval = ref(null)

// Format currency values
const formatNumber = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value)
}

// Fetch all dashboard data
const fetchData = async () => {
    try {
        const today = new Date().toISOString().split('T')[0]
        const monthStart = new Date(new Date().setDate(1)).toISOString().split('T')[0]
        const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]

        // Parallel API requests
        const [todayRes, monthRes, profitRes, topProductRes] = await Promise.all([
            axios.get('/api/orders/summary', { params: { start_date: today } }),
            axios.get('/api/orders/summary', { params: { start_date: monthStart } }),
            axios.get('/api/reports/profit-summary', { params: { start_date: thirtyDaysAgo } }),
            axios.get('/api/products/top-selling')
        ])

        todaySales.value = todayRes.data.total_sales
        monthSales.value = monthRes.data.total_sales
        profitSummary.value = profitRes.data
        topProduct.value = topProductRes.data
    } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
    }
}

// Lifecycle hooks
onMounted(() => {
    document.title = 'Dashboard - RPG-Pos'

    fetchData()
    interval.value = setInterval(fetchData, 5 * 60 * 1000) // Refresh every 5 minutes
})

onBeforeUnmount(() => {
    clearInterval(interval.value)
})


</script>

<template>

    <AppLayout>
        <div class="dashboard-grid">
            <!-- Today's Sales -->
            <div class="summary-card">
                <div class="card-header">
                    <h3 class="card-title">Today's Sales</h3>
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="card-amount">{{ formatNumber(todaySales) }}</div>
            </div>

            <!-- Monthly Sales -->
            <div class="summary-card">
                <div class="card-header">
                    <h3 class="card-title">This Month's Sales</h3>
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="card-amount">{{ formatNumber(monthSales) }}</div>
            </div>

            <!-- Net Profit -->
            <div class="summary-card" :class="{ 'negative-profit': profitSummary.net_profit < 0 }">
                <div class="card-header">
                    <h3 class="card-title">Net Profit (30 Days)</h3>
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="card-amount">{{ formatNumber(profitSummary.net_profit) }}</div>
                <div class="card-meta">Margin: {{ profitSummary.net_margin }}%</div>
            </div>

            <!-- Top Product -->
            <div class="summary-card">
                <div class="card-header">
                    <h3 class="card-title">Top Selling Product</h3>
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                </div>
                <div class="card-product">{{ topProduct.name || 'N/A' }}</div>
                <div class="card-sales">{{ formatNumber(topProduct.revenue || 0) }}</div>
            </div>
        </div>




    </AppLayout>

</template>

<style scoped>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.summary-card {
    padding: 1.5rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 500;
    color: #6b7280;
}

.card-icon {
    color: #9ca3af;
}

.card-amount {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.card-meta {
    font-size: 0.875rem;
    color: #6b7280;
}

.card-product {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0.5rem 0;
}

.card-sales {
    font-size: 1.25rem;
    font-weight: 600;
    color: #10b981;
}

.negative-profit .card-amount,
.negative-profit .card-meta {
    color: #ef4444;
}
</style>