<script setup>
import AppLayout from '@/components/AppLayout.vue';
import ReceiveOrderModal from './ReceiveOrderModal.vue';
import { ref, computed, onMounted } from 'vue';
import { usePurchaseOrderStore } from '@/stores/purchaseOrderStore';
import Swal from 'sweetalert2';

const props = defineProps({
    id: {
        type: [String, Number],
        required: true
    }
});

const purchaseOrderStore = usePurchaseOrderStore();
const showReceiveModal = ref(false);

const purchaseOrder = computed(() => purchaseOrderStore.currentPurchaseOrder);
const isLoading = computed(() => purchaseOrderStore.isLoading);

const statusBadgeClass = (status) => {
    return {
        'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'ordered': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'received': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    }[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'N/A';
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const updateStatus = async (status) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `You're about to mark this order as ${status}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `Yes, mark as ${status}`
    });

    if (result.isConfirmed) {
        try {
            await purchaseOrderStore.updatePurchaseOrderStatus({
                id: props.id,
                status
            });
            Swal.fire(
                'Updated!',
                `Purchase order status updated to ${status}`,
                'success'
            );
        } catch (error) {
            Swal.fire(
                'Error!',
                'Failed to update purchase order status',
                'error'
            );
        }
    }
};

const onOrderReceived = () => {
    showReceiveModal.value = false;
    purchaseOrderStore.fetchPurchaseOrderById(props.id);
};

onMounted(() => {
    purchaseOrderStore.fetchPurchaseOrderById(props.id);
});
</script>

<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Loading State -->
            <div v-if="isLoading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
            </div>

            <!-- Content -->
            <div v-else-if="purchaseOrder" class="space-y-6">
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Purchase Order: {{ purchaseOrder.po_number }}
                        </h1>
                        <span :class="statusBadgeClass(purchaseOrder.status)"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2">
                            {{ purchaseOrder.status }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <router-link v-if="purchaseOrder.status === 'draft'"
                            :to="`/purchase-orders/${purchaseOrder.id}/edit`"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Edit
                        </router-link>

                        <button v-if="purchaseOrder.status === 'draft'" @click="updateStatus('ordered')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Mark as Ordered
                        </button>

                        <button v-if="purchaseOrder.status === 'ordered'" @click="showReceiveModal = true"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Receive Order
                        </button>

                        <button v-if="purchaseOrder.status === 'draft'" @click="updateStatus('cancelled')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Cancel Order
                        </button>
                    </div>
                </div>

                <!-- Supplier and Order Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Supplier Card -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Supplier Information
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ purchaseOrder.supplier.name
                                        }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                        purchaseOrder.supplier.contact_person || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                        purchaseOrder.supplier.phone || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                        purchaseOrder.supplier.email || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Info Card -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Order Information
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Date</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                        formatDate(purchaseOrder.order_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expected Delivery
                                    </p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                        formatDate(purchaseOrder.expected_delivery_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ purchaseOrder.user.name }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                        formatCurrency(purchaseOrder.total_amount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Items
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Product</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Quantity</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Unit Cost</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total</th>
                                        <th v-if="purchaseOrder.status === 'received'" scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Received Qty</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in purchaseOrder.items" :key="item.id">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ item.product.name }} ({{ item.product.sku }})
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ item.quantity }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatCurrency(item.unit_cost) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatCurrency(item.total_cost) }}
                                        </td>
                                        <td v-if="purchaseOrder.status === 'received'"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ item.received_quantity || item.quantity }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <td colspan="3"
                                            class="px-6 py-3 text-right text-sm font-medium text-gray-500 dark:text-gray-300">
                                            Total:
                                        </td>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatCurrency(purchaseOrder.total_amount) }}
                                        </td>
                                        <td v-if="purchaseOrder.status === 'received'"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Notes
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <p v-if="purchaseOrder.notes" class="text-sm text-gray-900 dark:text-white">{{
                            purchaseOrder.notes }}</p>
                        <p v-else class="text-sm text-gray-500 dark:text-gray-400">No notes for this purchase order</p>
                    </div>
                </div>

                <!-- Receive Order Modal -->
                <ReceiveOrderModal v-if="showReceiveModal" :purchase-order="purchaseOrder"
                    @close="showReceiveModal = false" @order-received="onOrderReceived" />
            </div>
        </div>
    </AppLayout>
</template>
