<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: Boolean,
    cartItems: Array
});

const emit = defineEmits(['close', 'apply']);

const discountType = ref('general');
const selectedProductId = ref(null);
const discountCode = ref('');
const error = ref(null);

const close = () => {
    resetForm();
    emit('close');
};

const applyDiscount = () => {
    error.value = null;

    // Validate inputs
    if (!discountCode.value) {
        error.value = 'Please enter a discount code';
        return;
    }

    if (discountType.value === 'product' && !selectedProductId.value) {
        error.value = 'Please select a product for the discount';
        return;
    }

    // Emit event with discount data
    emit('apply', {
        type: discountType.value,
        productId: selectedProductId.value,
        code: discountCode.value
    });

    resetForm();
    close();
};

const resetForm = () => {
    discountType.value = 'general';
    selectedProductId.value = null;
    discountCode.value = '';
    error.value = null;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Apply Discount</h3>

            <div class="space-y-4">
                <!-- Discount Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                    <div class="flex space-x-4">
                        <button @click="discountType = 'general'"
                            :class="{ 'bg-teal-800 text-white': discountType === 'general' }"
                            class="px-4 py-2 border border-teal-800 rounded-lg hover:bg-teal-700 hover:text-white transition">
                            General Discount
                        </button>
                        <button @click="discountType = 'product'"
                            :class="{ 'bg-teal-800 text-white': discountType === 'product' }"
                            class="px-4 py-2 border border-teal-800 rounded-lg hover:bg-teal-700 hover:text-white transition">
                            Product Discount
                        </button>
                    </div>
                </div>

                <!-- Product Selection (only shown for product discounts) -->
                <div v-if="discountType === 'product'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Product</label>
                    <select v-model="selectedProductId" class="w-full p-2 border border-gray-300 rounded-lg">
                        <option :value="null">Select a product</option>
                        <option v-for="item in cartItems" :key="item.product.id" :value="item.product.id">
                            {{ item.product.name }} ({{ formatCurrency(item.product.price) }})
                        </option>
                    </select>
                </div>

                <!-- Discount Code Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Code</label>
                    <input v-model="discountCode" type="text" placeholder="Enter discount code"
                        class="w-full p-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Error Message -->
                <div v-if="error" class="text-red-600 text-sm">
                    {{ error }}
                </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <button @click="close"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button @click="applyDiscount"
                    class="px-4 py-2 bg-teal-800 text-white rounded-lg hover:bg-teal-700 transition">
                    Apply Discount
                </button>
            </div>
        </div>
    </div>
</template>