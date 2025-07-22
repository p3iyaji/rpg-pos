<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { usePurchaseOrderStore } from '@/stores/purchaseOrderStore';
import Swal from 'sweetalert2';

const purchaseOrderStore = usePurchaseOrderStore();
const activeTab = ref('all');

const purchaseOrders = computed(() => purchaseOrderStore.purchaseOrders?.data || []);
const pagination = computed(() => purchaseOrderStore.pagination || {});
const isLoading = computed(() => purchaseOrderStore.isLoading);

// Safely compute filtered POs
const filteredPOs = computed(() => {
    if (!purchaseOrders.value) return [];

    switch (activeTab.value) {
        case 'draft': return purchaseOrders.value.filter(po => po.status === 'draft');
        case 'ordered': return purchaseOrders.value.filter(po => po.status === 'ordered');
        case 'received': return purchaseOrders.value.filter(po => po.status === 'received');
        default: return purchaseOrders.value;
    }
});

// Safe counts for tabs
const draftCount = computed(() => purchaseOrders.value.filter(po => po.status === 'draft').length);
const orderedCount = computed(() => purchaseOrders.value.filter(po => po.status === 'ordered').length);
const receivedCount = computed(() => purchaseOrders.value.filter(po => po.status === 'received').length);

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'N/A';
};

const formatCurrency = (amount) => {
    const formattedAmount = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)

    return `₦${formattedAmount}`;
}

const statusBadgeClass = (status) => {
    return {
        'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'ordered': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'received': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    }[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const fetchPage = (page) => {
    purchaseOrderStore.fetchPurchaseOrders({ page });
};

const deletePO = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            await purchaseOrderStore.deletePurchaseOrder(id);
            Swal.fire(
                'Deleted!',
                'The purchase order has been deleted.',
                'success'
            );
        } catch (error) {
            Swal.fire(
                'Error!',
                'Failed to delete purchase order.',
                'error'
            );
        }
    }
};

onMounted(() => {
    purchaseOrderStore.fetchPurchaseOrders();
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Purchase Orders</h2>
                <router-link to="/purchase-orders/new"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="fas fa-plus mr-2"></i> New Purchase Order
                </router-link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px">
                        <li v-for="tab in tabs" :key="tab.value" class="me-2">
                            <button @click="activeTab = tab.value" :class="{
                                'inline-block p-4 border-b-2 rounded-t-lg': true,
                                'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500': activeTab === tab.value,
                                'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== tab.value,
                                'border-transparent': activeTab !== tab.value
                            }">
                                {{ tab.label }} <span v-if="tab.count !== undefined">({{ tab.count }})</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="p-6">
                    <div v-if="isLoading" class="flex justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                    </div>

                    <div v-else>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">PO Number</th>
                                        <th scope="col" class="px-6 py-3">Supplier</th>
                                        <th scope="col" class="px-6 py-3">Order Date</th>
                                        <th scope="col" class="px-6 py-3">Expected Delivery</th>
                                        <th scope="col" class="px-6 py-3">Total Amount</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="po in filteredPOs" :key="po.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ po.po_number }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ po.supplier.name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ formatDate(po.order_date) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ formatDate(po.expected_delivery_date) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ formatCurrency(po.total_amount) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="statusBadgeClass(po.status)"
                                                class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ po.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <router-link :to="`/purchase-orders/${po.id}`"
                                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline mr-3">
                                                View
                                            </router-link>
                                            <button v-if="po.status === 'draft'" @click="deletePO(po.id)"
                                                class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="filteredPOs.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No purchase orders found
                        </div>

                        <div class="flex justify-center mt-6" v-if="pagination.last_page > 1">
                            <nav class="inline-flex rounded-md shadow">
                                <button @click="fetchPage(pagination.current_page - 1)"
                                    :disabled="!pagination.prev_page_url" :class="{
                                        'px-3 py-2 rounded-l-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50': true,
                                        'cursor-not-allowed opacity-50': !pagination.prev_page_url
                                    }">
                                    Previous
                                </button>
                                <button v-for="page in pagination.last_page" :key="page" @click="fetchPage(page)"
                                    :class="{
                                        'px-3 py-2 border-t border-b border-gray-300': true,
                                        'bg-blue-50 text-blue-600 border-blue-500': page === pagination.current_page,
                                        'bg-white text-gray-500 hover:bg-gray-50': page !== pagination.current_page
                                    }">
                                    {{ page }}
                                </button>
                                <button @click="fetchPage(pagination.current_page + 1)"
                                    :disabled="!pagination.next_page_url" :class="{
                                        'px-3 py-2 rounded-r-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50': true,
                                        'cursor-not-allowed opacity-50': !pagination.next_page_url
                                    }">
                                    Next
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
