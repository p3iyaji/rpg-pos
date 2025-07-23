<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { ref, onMounted } from 'vue';
import { saveAs } from 'file-saver';
import * as XLSX from 'xlsx';
import axios from 'axios';
import ProfitLossChart from './ProfitLossChart.vue';
import { initFlowbite } from 'flowbite';

const startDate = ref(new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0]);
const endDate = ref(new Date().toISOString().split('T')[0]);
const groupBy = ref('month');
const report = ref(null);
const loading = ref(false);

onMounted(() => {
    initFlowbite();
    generateReport();
});

const generateReport = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/reports/profit-and-loss', {
            params: {
                start_date: startDate.value,
                end_date: endDate.value,
                group_by: groupBy.value
            }
        });
        report.value = response.data;
    } catch (error) {
        console.error('Failed to generate report:', error);
        alert('Failed to generate report. Please try again.');
    } finally {
        loading.value = false;
    }
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const exportToExcel = () => {
    if (!report.value) return;

    const exportData = [
        ['Profit & Loss Report', '', '', '', '', '', '', ''],
        [`From ${startDate.value} to ${endDate.value}`, '', '', '', '', '', '', ''],
        [''],
        ['Period', 'Revenue', 'COGS', 'Gross Profit', 'Gross Margin', 'Expenses', 'Net Profit', 'Net Margin'],
        ...report.value.periods.map(p => [
            p.period,
            p.revenue,
            p.cogs,
            p.gross_profit,
            p.gross_margin,
            p.expenses,
            p.net_profit,
            p.net_margin
        ]),
        [''],
        ['Total',
            report.value.totals.revenue,
            report.value.totals.cogs,
            report.value.totals.gross_profit,
            report.value.totals.gross_margin,
            report.value.totals.expenses,
            report.value.totals.net_profit,
            report.value.totals.net_margin
        ]
    ];

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(exportData);
    XLSX.utils.book_append_sheet(wb, ws, 'Profit and Loss');

    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
    saveAs(new Blob([wbout], { type: 'application/octet-stream' }),
        `Profit_and_Loss_${startDate.value}_to_${endDate.value}.xlsx`);
};
</script>

<template>
    <AppLayout>
        <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
            <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <h2 class="p-5 text-lg">Profit & Loss Report</h2>

                    <div
                        class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                        <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">From:</label>
                                <input type="date" v-model="startDate"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">To:</label>
                                <input type="date" v-model="endDate"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Group by:</label>
                                <select v-model="groupBy"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <option value="day">Daily</option>
                                    <option value="week">Weekly</option>
                                    <option value="month">Monthly</option>
                                    <option value="year">Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <button @click="generateReport"
                                class="flex items-center justify-center text-white bg-teal-700 hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-teal-600 dark:hover:bg-teal-700 focus:outline-none dark:focus:ring-teal-800">
                                Generate Report
                            </button>
                            <button @click="exportToExcel"
                                class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                Export to Excel
                            </button>
                        </div>
                    </div>

                    <div v-if="loading" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Loading report data...
                    </div>

                    <div v-else-if="report" class="p-4 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Revenue</h3>
                                <div class="text-2xl font-bold text-green-800 dark:text-green-100">${{
                                    formatNumber(report.totals.revenue) }}</div>
                            </div>
                            <div class="p-4 bg-red-100 dark:bg-red-900 rounded-lg">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">COGS</h3>
                                <div class="text-2xl font-bold text-red-800 dark:text-red-100">${{
                                    formatNumber(report.totals.cogs) }}</div>
                            </div>
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Gross Profit</h3>
                                <div class="text-2xl font-bold text-blue-800 dark:text-blue-100">${{
                                    formatNumber(report.totals.gross_profit) }}</div>
                                <div class="text-sm text-blue-700 dark:text-blue-300">Margin: {{
                                    report.totals.gross_margin }}%</div>
                            </div>
                            <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Expenses</h3>
                                <div class="text-2xl font-bold text-yellow-800 dark:text-yellow-100">${{
                                    formatNumber(report.totals.expenses) }}</div>
                            </div>
                            <div class="p-4"
                                :class="report.totals.net_profit < 0 ? 'bg-red-100 dark:bg-red-900' : 'bg-indigo-100 dark:bg-indigo-900'">
                                <h3 class="text-sm font-medium"
                                    :class="report.totals.net_profit < 0 ? 'text-red-800 dark:text-red-200' : 'text-indigo-800 dark:text-indigo-200'">
                                    Net Profit</h3>
                                <div class="text-2xl font-bold"
                                    :class="report.totals.net_profit < 0 ? 'text-red-800 dark:text-red-100' : 'text-indigo-800 dark:text-indigo-100'">
                                    ${{ formatNumber(report.totals.net_profit) }}</div>
                                <div class="text-sm"
                                    :class="report.totals.net_profit < 0 ? 'text-red-700 dark:text-red-300' : 'text-indigo-700 dark:text-indigo-300'">
                                    Margin: {{ report.totals.net_margin }}%</div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                            <ProfitLossChart :data="report.periods" :group-by="report.group_by" />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Period</th>
                                        <th scope="col" class="px-4 py-3 text-right">Revenue</th>
                                        <th scope="col" class="px-4 py-3 text-right">COGS</th>
                                        <th scope="col" class="px-4 py-3 text-right">Gross Profit</th>
                                        <th scope="col" class="px-4 py-3 text-right">Gross Margin</th>
                                        <th scope="col" class="px-4 py-3 text-right">Expenses</th>
                                        <th scope="col" class="px-4 py-3 text-right">Net Profit</th>
                                        <th scope="col" class="px-4 py-3 text-right">Net Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="period in report.periods" :key="period.period"
                                        class="border-b dark:border-gray-700">
                                        <td class="px-4 py-3">{{ period.period }}</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(period.revenue) }}</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(period.cogs) }}</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(period.gross_profit) }}</td>
                                        <td class="px-4 py-3 text-right">{{ period.gross_margin }}%</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(period.expenses) }}</td>
                                        <td class="px-4 py-3 text-right"
                                            :class="{ 'text-red-600 dark:text-red-400': period.net_profit < 0 }">
                                            ${{ formatNumber(period.net_profit) }}
                                        </td>
                                        <td class="px-4 py-3 text-right"
                                            :class="{ 'text-red-600 dark:text-red-400': period.net_margin < 0 }">
                                            {{ period.net_margin }}%
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-50 dark:bg-gray-800 font-bold">
                                        <td class="px-4 py-3">Total</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(report.totals.revenue) }}</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(report.totals.cogs) }}</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(report.totals.gross_profit) }}
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ report.totals.gross_margin }}%</td>
                                        <td class="px-4 py-3 text-right">${{ formatNumber(report.totals.expenses) }}
                                        </td>
                                        <td class="px-4 py-3 text-right"
                                            :class="{ 'text-red-600 dark:text-red-400': report.totals.net_profit < 0 }">
                                            ${{ formatNumber(report.totals.net_profit) }}
                                        </td>
                                        <td class="px-4 py-3 text-right"
                                            :class="{ 'text-red-600 dark:text-red-400': report.totals.net_margin < 0 }">
                                            {{ report.totals.net_margin }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No report data available. Generate a report to view profit and loss information.
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>