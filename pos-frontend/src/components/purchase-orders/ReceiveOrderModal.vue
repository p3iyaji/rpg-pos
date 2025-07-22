<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    purchaseOrder: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'order-received']);

const receivingItems = ref([]);
const receivingNotes = ref('');
const isSubmitting = ref(false);

const close = () => {
    emit('close');
};

const submitReceiving = async () => {
    isSubmitting.value = true;
    try {
        const payload = {
            items: receivingItems.value.map(item => ({
                id: item.id,
                received_quantity: item.received_quantity,
                receiving_notes: item.receiving_notes
            })),
            receiving_notes: receivingNotes.value
        };

        await axios.post(`/api/purchase-orders/${props.purchaseOrder.id}/receive`, payload);

        Swal.fire({
            toast: true,
            icon: 'success',
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            title: 'Purchase order received successfully!',
        });

        emit('order-received');
        close();
    } catch (error) {
        console.error('Error receiving order:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to receive purchase order'
        });
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    receivingItems.value = props.purchaseOrder.items.map(item => ({
        ...item,
        received_quantity: item.quantity,
        receiving_notes: ''
    }));
});
</script>
<template>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Receive Purchase Order
                            </h3>
                            <div class="mt-4">
                                <!-- Info Alert -->
                                <div
                                    class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4 dark:bg-blue-900 dark:border-blue-600">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400 dark:text-blue-300"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                                Please verify the received quantities and update any discrepancies.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Product</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Ordered Qty</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Received Qty</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="(item, index) in receivingItems" :key="item.id">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ item.product.name }} ({{ item.product.sku }})
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ item.quantity }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="number" v-model.number="item.received_quantity"
                                                        :max="item.quantity" min="0"
                                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="text" v-model="item.receiving_notes"
                                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Receiving Notes -->
                                <div class="mt-6">
                                    <label for="receiving-notes"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Receiving Notes
                                    </label>
                                    <textarea id="receiving-notes" v-model="receivingNotes" rows="3"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="submitReceiving" :disabled="isSubmitting" :class="{
                        'inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm': true,
                        'opacity-50 cursor-not-allowed': isSubmitting
                    }">
                        <span v-if="!isSubmitting">Complete Receiving</span>
                        <span v-else class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                    <button type="button" @click="close"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-600 dark:hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
