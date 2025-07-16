<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { useOrderStore } from '@/stores/orderStores';
import { onMounted, computed, ref, watch } from 'vue';
import { TailwindPagination } from 'laravel-vue-pagination';
import { initFlowbite } from 'flowbite';


const orderStore = useOrderStore();
const searchQuery = ref('');
const debouncedSearchQuery = ref('');
const debounceTimeout = ref(null);

orderStore.fetchOrders();

onMounted(() => {
    initFlowbite();
})

const fetchNewPage = (page) => {
    orderStore.fetchOrders(page);
}

const navigateToOrder = (orderId) => {
    orderStore.fetchOrderById(orderId);

};

//need to come back to this as it is not working well yet
const summary = computed(() => {
    return orderStore.summary || {
        total_item_discounts: 0,
        total_general_discount: 0,
        total_sales: 0,
        total_orders: 0
    };
});

const formatCurrency = (amount) => {
    const formattedAmount = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)

    return `₦${formattedAmount}`;
}

watch(searchQuery, (newValue) => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        debouncedSearchQuery.value = newValue;
        orderStore.fetchOrders(1, newValue);
    }, 500);
})

// Define status colors mapping (should match your PHP enum)
const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    refunded: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
};

// Define status labels mapping (should match your PHP enum)
const statusLabels = {
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Completed',
    cancelled: 'Cancelled',
    refunded: 'Refunded',
};


</script>


<template>
    <AppLayout>
        <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
            <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Total Item Discount -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Item Discount</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(summary.total_item_discounts) || 0 }}
                                </p>
                            </div>
                            <div class="p-3 rounded-lg bg-teal-100 dark:bg-teal-900">
                                <span class="w-6 h-6 text-teal-600 dark:text-teal-300 font-bold text-lg">₦</span>

                            </div>
                        </div>
                    </div>

                    <!-- Total General Discount -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total General Discount
                                </p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(summary.total_general_discount) || 0 }}
                                </p>
                            </div>
                            <div class="p-3 rounded-lg bg-yellow-100 dark:bg-yellow-900">
                                <span class="w-6 h-6 text-teal-600 dark:text-teal-300 font-bold text-lg">₦</span>

                            </div>
                        </div>
                    </div>

                    <!-- Total Sales -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sales</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(summary.total_sales) || 0 }}
                                </p>
                            </div>
                            <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900">
                                <span class="w-6 h-6 text-red-600 dark:text-red-300 font-bold text-lg">₦</span>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- Start coding here -->
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <h2 class="p-5 text-lg">All Sales</h2>
                    <div
                        class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                        <div class="w-full md:w-1/2">
                            <form class="flex items-center" @submit.prevent>
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                            fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" id="simple-search" v-model="searchQuery"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        placeholder="Search">
                                </div>
                            </form>
                        </div>
                        <div
                            class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">

                            <div class="flex items-center space-x-3 w-full md:w-auto">
                                <button id="actionsDropdownButton" data-dropdown-toggle="actionsDropdown"
                                    class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                                    type="button">
                                    <svg class="-ml-1 mr-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path clip-rule="evenodd" fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                    </svg>
                                    Actions
                                </button>
                                <div id="actionsDropdown"
                                    class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                    <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                        aria-labelledby="actionsDropdownButton">
                                        <li>
                                            <a href="#"
                                                class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Mass
                                                Edit</a>
                                        </li>
                                    </ul>
                                    <div class="py-1">
                                        <a href="#"
                                            class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Delete
                                            all</a>
                                    </div>
                                </div>
                                <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                                    class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                                    type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                        class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Filter
                                    <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path clip-rule="evenodd" fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                    </svg>
                                </button>
                                <div id="filterDropdown"
                                    class="z-10 hidden w-48 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                                    <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Choose brand</h6>
                                    <ul class="space-y-2 text-sm" aria-labelledby="filterDropdownButton">
                                        <li class="flex items-center">
                                            <input id="apple" type="checkbox" value=""
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="apple"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Apple
                                                (56)</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="fitbit" type="checkbox" value=""
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="fitbit"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Microsoft
                                                (16)</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="razor" type="checkbox" value=""
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="razor"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Razor
                                                (49)</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="nikon" type="checkbox" value=""
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="nikon"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Nikon
                                                (12)</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="benq" type="checkbox" value=""
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="benq"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">BenQ
                                                (74)</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">#ID</th>
                                    <th scope="col" class="px-4 py-3">Order No</th>

                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="px-4 py-3">Item Discount</th>
                                    <th scope="col" class="px-4 py-3">General Discount</th>

                                    <th scope="col" class="px-4 py-3">Subtotal</th>
                                    <th scope="col" class="px-4 py-3">Total</th>
                                    <th scope="col" class="px-4 py-3">Amount Tendered</th>
                                    <th scope="col" class="px-4 py-3">Change</th>
                                    <th scope="col" class="px-4 py-3">Refund</th>
                                    <th scope="col" class="px-4 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr v-for="order in orderStore.orders.data" :key="order.id"
                                    @click="navigateToOrder(order.id)"
                                    class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer transition-colors"
                                    :class="{ 'opacity-50': orderStore.isLoading }">
                                    <td class="px-4 py-3">{{ order.id }}</td>
                                    <td class="px-4 py-3">{{ order.order_no }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="['text-xs font-medium px-2 py-0.5 rounded', statusColors[order.status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300']">
                                            {{ statusLabels[order.status] || order.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ order.item_discounts }}</td>
                                    <td class="px-4 py-3">{{ order.general_discount }}</td>
                                    <td class="px-4 py-3">{{ order.subtotal }}</td>
                                    <td class="px-4 py-3">{{ order.total }}</td>
                                    <td class="px-4 py-3">{{ order.amount_tendered }}</td>
                                    <td class="px-4 py-3">{{ order.change_due }}</td>
                                    <td class="px-4 py-3">{{ order.amount_refunded }}</td>

                                    <td class="px-4 py-3 flex items-center justify-end">

                                        <div class="flex items-center">


                                            <button @click="navigateToOrder(order.id)"
                                                class="block py-1 px-4 mr-2 text-white rounded-md bg-teal-500 hover:bg-teal-600 dark:hover:bg-teal-700">
                                                View
                                            </button>

                                            <div>

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4"
                        aria-label="Table navigation">
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            Showing
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ orderStore.orders.meta.from ?? 0 }} - {{ orderStore.orders.meta.to ?? 0 }}
                            </span>
                            of
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ orderStore.orders.meta.total ?? 0 }}
                            </span>
                        </span>
                        <TailwindPagination :data="{
                            current_page: orderStore.orders.meta.current_page,
                            last_page: orderStore.orders.meta.last_page,
                            per_page: orderStore.orders.meta.per_page,
                            total: orderStore.orders.meta.total,
                            links: orderStore.orders.links
                        }" @pagination-change-page="fetchNewPage" />
                    </nav>
                </div>
            </div>


        </section>
    </AppLayout>
</template>