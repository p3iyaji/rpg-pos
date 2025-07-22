<script setup>
import AppLayout from '@/components/AppLayout.vue';
import ProductSelector from './ProductSelector.vue';
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { usePurchaseOrderStore } from '@/stores/purchaseOrderStore';
import Swal from 'sweetalert2';

const router = useRouter();
const purchaseOrderStore = usePurchaseOrderStore();

const props = defineProps({
    po: {
        type: Object,
        default: null
    }
});

const form = ref({
    supplier_id: '',
    order_date: new Date().toISOString().split('T')[0],
    expected_delivery_date: '',
    notes: '',
    items: []
});

const suppliers = ref([]);
const showProductSelector = ref(false);
const isSubmitting = ref(false);

const isEditMode = computed(() => props.po !== null);
const poNumber = computed(() => props.po ? props.po.po_number : 'Will be generated on save');
const totalAmount = computed(() => form.value.items.reduce((total, item) => total + (item.total_cost || 0), 0));
const existingProductIds = computed(() => form.value.items.map(item => item.product_id));

const fetchSuppliers = async () => {
    try {
        const response = await axios.get('/api/suppliers');
        suppliers.value = response.data;
    } catch (error) {
        console.error('Error fetching suppliers:', error);
    }
};

const loadPOData = () => {
    form.value = {
        supplier_id: props.po.supplier_id,
        order_date: props.po.order_date.split('T')[0],
        expected_delivery_date: props.po.expected_delivery_date ? props.po.expected_delivery_date.split('T')[0] : '',
        notes: props.po.notes || '',
        items: props.po.items.map(item => ({
            product_id: item.product_id,
            product: item.product,
            quantity: item.quantity,
            unit_cost: item.unit_cost,
            total_cost: item.total_cost
        }))
    };
};

const addProducts = (selectedProducts) => {
    selectedProducts.forEach(product => {
        form.value.items.push({
            product_id: product.id,
            product: product,
            quantity: 1,
            unit_cost: product.cost_price || 0,
            total_cost: product.cost_price || 0
        });
    });
    showProductSelector.value = false;
};

const removeItem = (index) => {
    form.value.items.splice(index, 1);
};

const updateItemTotal = (index) => {
    const item = form.value.items[index];
    item.total_cost = item.quantity * item.unit_cost;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
};

const submitForm = async () => {
    isSubmitting.value = true;
    try {
        const payload = {
            ...form.value,
            items: form.value.items.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_cost: item.unit_cost
            }))
        };

        if (isEditMode.value) {
            await purchaseOrderStore.updatePurchaseOrder({ id: props.po.id, data: payload });
            Swal.fire({
                toast: true,
                icon: 'success',
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                title: 'Purchase order updated successfully!',
            });
        } else {
            await purchaseOrderStore.createPurchaseOrder(payload);
            Swal.fire({
                toast: true,
                icon: 'success',
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                title: 'Purchase order created successfully!',
            });
        }

        router.push('/purchase-orders');
    } catch (error) {
        console.error('Error saving purchase order:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save purchase order'
        });
    } finally {
        isSubmitting.value = false;
    }
};

const cancel = () => {
    if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
        router.push('/purchase-orders');
    }
};

onMounted(() => {
    fetchSuppliers();
    if (isEditMode.value) {
        loadPOData();
    }
});
</script>

<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ isEditMode ? 'Edit Purchase Order' : 'Create Purchase Order' }}
                        </h2>
                        <button @click="cancel" type="button"
                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Cancel
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <form @submit.prevent="submitForm">
                        <div class="space-y-6">
                            <!-- Basic Information Card -->
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Supplier Selection -->
                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Supplier *
                                        </label>
                                        <select v-model="form.supplier_id" class="form-select" required>
                                            <option value="">Select Supplier</option>
                                            <option v-for="supplier in suppliers" :key="supplier.id"
                                                :value="supplier.id">
                                                {{ supplier.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- PO Number (readonly) -->
                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            PO Number
                                        </label>
                                        <input type="text" class="form-control" :value="poNumber" readonly>
                                    </div>

                                    <!-- Order Date -->
                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Order Date *
                                        </label>
                                        <input type="date" v-model="form.order_date" class="form-control" required>
                                    </div>

                                    <!-- Expected Delivery Date -->
                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Expected Delivery Date
                                        </label>
                                        <input type="date" v-model="form.expected_delivery_date" class="form-control">
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mt-4">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Notes
                                    </label>
                                    <textarea v-model="form.notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <!-- Items Card -->
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Items</h3>
                                    <button type="button" @click="showProductSelector = true"
                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                        <i class="fas fa-plus mr-2"></i> Add Product
                                    </button>
                                </div>

                                <div v-if="form.items.length === 0"
                                    class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    No items added to this purchase order
                                </div>

                                <div v-else class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead
                                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr>
                                                <th scope="col" class="px-6 py-3">Product</th>
                                                <th scope="col" class="px-6 py-3">Quantity</th>
                                                <th scope="col" class="px-6 py-3">Unit Cost</th>
                                                <th scope="col" class="px-6 py-3">Total</th>
                                                <th scope="col" class="px-6 py-3 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in form.items" :key="index"
                                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <td
                                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ item.product.name }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="number" v-model.number="item.quantity" min="1"
                                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                        @change="updateItemTotal(index)">
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="number" v-model.number="item.unit_cost" min="0.01"
                                                        step="0.01"
                                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                        @change="updateItemTotal(index)">
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ formatCurrency(item.total_cost) }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button type="button" @click="removeItem(index)"
                                                        class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <td colspan="3"
                                                    class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">
                                                    Total:
                                                </td>
                                                <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                                    {{ formatCurrency(totalAmount) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" :disabled="isSubmitting"
                                    class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                                    :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }">
                                    <span v-if="!isSubmitting">{{ isEditMode ? 'Update' : 'Save' }}</span>
                                    <span v-else class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ isEditMode ? 'Updating...' : 'Creating...' }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <ProductSelector v-if="showProductSelector" :existing-products="existingProductIds"
            @close="showProductSelector = false" @add-products="addProducts" />
    </AppLayout>
</template>
