<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { useOrderStore } from '@/stores/orderStores';
import { useRoute, useRouter } from 'vue-router';
import { ref, onMounted } from 'vue';
import ThermalInvoice from '@/components/ThermalInvoice.vue';
import { initFlowbite } from 'flowbite';

const route = useRoute();
const router = useRouter();
const orderStore = useOrderStore();
const showInvoice = ref(false);
const thermalInvoiceRef = ref(null);


onMounted(() => {
    initFlowbite();
    // Fetch order details when component mounts
    orderStore.fetchOrderById(route.params.id);
});

const handlePrintClick = () => {
    showInvoice.value = true;
};

// Optional: If you need to call print directly from parent
const printInvoiceDirectly = () => {
    if (thermalInvoiceRef.value) {
        thermalInvoiceRef.value.printInvoice();
    }
};

const backToOrders = () => {
    router.go(-1);
}
</script>

<template>
    <AppLayout>
        <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
            <div v-if="orderStore.currentOrder" class="mx-auto max-w-screen-xl px-4 lg:px-12">
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <!-- Order header with print button -->
                    <div class="flex justify-between items-center p-4">
                        <h2 class="text-lg font-semibold">Order #{{ orderStore.currentOrder.order_no }}</h2>
                        <div>
                            <button @click="backToOrders"
                                class="w-32 bg-teal-500 text-white px-4 m-2 py-2 rounded-lg hover:bg-teal-700 transition">
                                Back
                            </button>
                            <button @click="handlePrintClick"
                                class="w-32 bg-teal-800 text-white px-4 m-2 py-2 rounded-lg hover:bg-teal-700 transition">
                                Print Now
                            </button>
                        </div>

                    </div>

                    <!-- Order summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border-b text-gray-700">
                        <div>
                            <h3 class="font-medium dark:text-gray-300">Customer Information</h3>
                            <p class="mt-1">{{ orderStore.currentOrder.customer?.name }}</p>
                            <p v-if="orderStore.currentOrder.customer?.phone">Phone: {{
                                orderStore.currentOrder.customer.phone }}</p>
                            <p v-if="orderStore.currentOrder.customer?.email">Email: {{
                                orderStore.currentOrder.customer.email }}</p>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-700 dark:text-gray-300">Order Information</h3>
                            <p class="mt-1">Status: <span class="capitalize">{{ orderStore.currentOrder.status }}</span>
                            </p>
                            <p>Date: {{ new Date(orderStore.currentOrder.date).toLocaleString() }}</p>
                            <p>Cashier: {{ orderStore.currentOrder.user }}</p>
                        </div>
                    </div>

                    <!-- Order items table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Product</th>
                                    <th scope="col" class="px-4 py-3">Price</th>
                                    <th scope="col" class="px-4 py-3">Qty</th>
                                    <th scope="col" class="px-4 py-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in orderStore.currentOrder.items" :key="item.id"
                                    class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3">{{ item.name || `Product ${item.product_id}` }}</td>
                                    <td class="px-4 py-3">₦{{ parseFloat(item.unit_price).toFixed(2) }}</td>
                                    <td class="px-4 py-3">{{ item.quantity }}</td>
                                    <td class="px-4 py-3">₦{{ parseFloat(item.total).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order totals -->
                    <div class="p-4 border-t">
                        <div class="grid grid-cols-2 gap-4 max-w-md ml-auto">
                            <div class="text-right">Subtotal:</div>
                            <div class="text-right">₦{{ parseFloat(orderStore.currentOrder.subtotal).toFixed(2) }}</div>

                            <div v-if="orderStore.currentOrder.product_discounts > 0" class="text-right text-red-500">
                                Product Discounts:</div>
                            <div v-if="orderStore.currentOrder.product_discounts > 0" class="text-right text-red-500">
                                -₦{{ parseFloat(orderStore.currentOrder.product_discounts).toFixed(2) }}</div>

                            <div v-if="orderStore.currentOrder.general_discount > 0" class="text-right text-red-500">
                                Order Discount:</div>
                            <div v-if="orderStore.currentOrder.general_discount > 0" class="text-right text-red-500">
                                -₦{{ parseFloat(orderStore.currentOrder.general_discount).toFixed(2) }}</div>

                            <div class="text-right font-bold">Total:</div>
                            <div class="text-right font-bold">₦{{ parseFloat(orderStore.currentOrder.total).toFixed(2)
                            }}</div>

                            <div class="text-right">Amount Tendered:</div>
                            <div class="text-right">₦{{ parseFloat(orderStore.currentOrder.amount_tendered).toFixed(2)
                            }}</div>

                            <div v-if="orderStore.currentOrder.change_due > 0" class="text-right">Change Due:</div>
                            <div v-if="orderStore.currentOrder.change_due > 0" class="text-right">₦{{
                                parseFloat(orderStore.currentOrder.change_due).toFixed(2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Show loading state -->
            <div v-else-if="orderStore.isLoading" class="text-center py-8">
                Loading order details...
            </div>

            <!-- Show error state -->
            <div v-else class="text-center py-8 text-red-500">
                Failed to load order details
            </div>

            <!-- Thermal Invoice Modal -->
            <ThermalInvoice v-if="showInvoice && orderStore.currentOrder" :order="orderStore.currentOrder"
                ref="thermalInvoiceRef" @close="showInvoice = false" />

        </section>
    </AppLayout>
</template>