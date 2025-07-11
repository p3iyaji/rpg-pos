<script setup>
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

import axios from 'axios';
import AppLayout from '@/components/AppLayout.vue';
import { useAuthStore } from '@/stores/authStore';
import { useRouter, useRoute } from 'vue-router';
import { useDiscountStore } from '@/stores/discountStore';
import { ref, onMounted, nextTick } from 'vue';
import Swal from 'sweetalert2';

const discountStore = useDiscountStore();
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({
    name: '',
    code: '',
    type: 'percentage',
    value: null,
    start_date: null,
    end_date: null,
    min_quantity: null,
    min_amount: null,
    scope: '',
    usage_limit: null,
    apply_to_all_products: true,
    is_active: true,
    product_ids: []
});

const products = ref([]);
const isLoading = ref(true);

// Options for <select>
const discountTypeOptions = [
    { value: 'percentage', label: 'Percentage Discount' },
    { value: 'fixed', label: 'Fixed Amount Discount' },
    { value: 'buy_x_get_y', label: 'Buy X Get Y Offer' },
];

const scopeTypeOptions = [
    { value: 'general', label: 'General' },
    { value: 'product', label: 'Product' },
];


onMounted(async () => {
    try {
        isLoading.value = true;

        // 1. Load products FIRST and ensure they are reactive options for Multiselect
        const productsResponse = await axios.get('/api/products');
        products.value = productsResponse.data.data.map(product => ({
            value: product.id.toString(),
            label: product.name
        }));

        const discountId = route.params.id;
        const response = await axios.get(`/api/discounts/${discountId}`);

        const discount = response.data.discount;

        form.value = {
            name: discount.name,
            code: discount.code,
            type: discount.type,
            value: discount.value,
            start_date: discount.start_date ? formatDateForInput(discount.start_date) : null,
            end_date: discount.end_date ? formatDateForInput(discount.end_date) : null,
            min_quantity: discount.min_quantity,
            min_amount: discount.min_amount,
            usage_limit: discount.usage_limit,
            scope: discount.scope,
            apply_to_all_products: discount.apply_to_all_products,
            is_active: discount.is_active,

            product_ids: response.data.products.map(p => p.id.toString())

        };

        console.log('form.value.product_ids:', form.value.product_ids);
        console.log('products (Multiselect options):', products.value);

        nextTick(() => {
            form.value.product_ids = [...form.value.product_ids];
        });

    } catch (error) {
        console.error('Error loading data:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error loading discount',
            text: error.message
        });
    } finally {
        isLoading.value = false;
    }
});


const updateSelection = () => {
    nextTick(() => {
        form.value.product_ids = [...form.value.product_ids];
    });
};
const formatDateForInput = (dateString) => {
    const date = new Date(dateString);
    // Convert to local datetime string and remove the timezone info
    return new Date(date.getTime() - (date.getTimezoneOffset() * 60000))
        .toISOString()
        .slice(0, 16);
};

const validateUsageLimit = () => {
    if (form.value.usage_limit !== null && form.value.usage_limit <= 0) {
        discountStore.errorMessage.usage_limit = ['Usage limit must be greater than 0'];
        return false;
    }
    return true;
};

const updateDiscount = async () => {

    if (!validateUsageLimit()) return;

    discountStore.errorMessage = {};

    const response = await discountStore.updateDiscount(route.params.id, form.value);

    if (response) {
        Swal.fire({
            toast: true,
            icon: 'success',
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            title: 'Discount updated successfully!',
        });
        router.push('/discounts');

    } else if (discountStore.errorMessage.general) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: discountStore.errorMessage.general[0],
        });
    }
};


const goBack = () => {
    router.go(-1);
}

</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <!-- Header section -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Update Category
                        </h2>
                        <button @click="goBack" type="button"
                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Back
                        </button>
                    </div>
                </div>

                <!-- Form section -->
                <div class="p-6">
                    <!-- General error message -->
                    <div v-if="discountStore.errorMessage.general"
                        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ discountStore.errorMessage.general[0] }}
                    </div>
                    <form @submit.prevent="updateDiscount">
                        <div class="space-y-6">
                            <!-- Discount Name -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Discount Name
                                </label>
                                <input v-model="form.name" type="text" id="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter discount name">
                                <span v-if="discountStore.errorMessage.name" class="text-red-600">
                                    {{ discountStore.errorMessage.name[0] }}
                                </span>
                            </div>
                            <!-- Discount Code -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Code
                                </label>
                                <input v-model="form.code" type="text" id="code"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter discount code">
                                <span v-if="discountStore.errorMessage.code" class="text-red-600">
                                    {{ discountStore.errorMessage.code[0] }}
                                </span>
                            </div>

                            <!-- Discount Type -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Discount Type
                                </label>
                                <select v-model="form.type" id="type"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <option v-for="option in discountTypeOptions" :key="option.value"
                                        :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <span v-if="discountStore.errorMessage.type" class="text-red-600">
                                    {{ discountStore.errorMessage.type[0] }}
                                </span>
                            </div>

                            <!-- Discount value -->
                            <div>
                                <label for="value" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Value
                                </label>
                                <input v-model="form.value" type="text" id="value"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <span v-if="discountStore.errorMessage.value" class="text-red-600">
                                    {{ discountStore.errorMessage.value[0] }}
                                </span>
                            </div>

                            <!-- Discount Start Date -->
                            <div>
                                <label for="start_date"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Start Date
                                </label>
                                <input v-model="form.start_date" type="datetime-local" id="start_date"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <span v-if="discountStore.errorMessage.start_date" class="text-red-600">
                                    {{ discountStore.errorMessage.start_date[0] }}
                                </span>
                            </div>

                            <!-- Discount End Date -->
                            <div>
                                <label for="end_date"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    End Date
                                </label>
                                <input v-model="form.end_date" type="datetime-local" id="end_date"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <span v-if="discountStore.errorMessage.end_date" class="text-red-600">
                                    {{ discountStore.errorMessage.end_date[0] }}
                                </span>
                            </div>

                            <!-- min quantity -->
                            <div>
                                <label for="min_quantity"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Min Quantity
                                </label>
                                <input v-model="form.min_quantity" type="number" id="min_quantity"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <span v-if="discountStore.errorMessage.min_quantity" class="text-red-600">
                                    {{ discountStore.errorMessage.min_quantity[0] }}
                                </span>
                            </div>

                            <!-- min Amount -->
                            <div>
                                <label for="min_amount"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Min Amount
                                </label>
                                <input v-model="form.min_amount" type="number" id="min_amount"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <span v-if="discountStore.errorMessage.min_amount" class="text-red-600">
                                    {{ discountStore.errorMessage.min_amount[0] }}
                                </span>
                            </div>
                            <!-- Discount Scope -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Discount Scope
                                </label>
                                <select v-model="form.scope" id="scope"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <option v-for="option in scopeTypeOptions" :key="option.value"
                                        :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <span v-if="discountStore.errorMessage.scope" class="text-red-600">
                                    {{ discountStore.errorMessage.scope[0] }}
                                </span>
                            </div>

                            <!-- Product-specific options -->
                            <div v-if="form.scope === 'product'"
                                class="space-y-4 p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <input v-model="form.apply_to_all_products" type="checkbox"
                                        id="apply_to_all_products"
                                        class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 dark:focus:ring-teal-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="apply_to_all_products"
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        Apply to all products
                                    </label>
                                </div>

                                <div v-if="!form.apply_to_all_products" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                        Select Products
                                    </label>
                                    <Multiselect v-model="form.product_ids" :options="products" mode="tags"
                                        :closeOnSelect="false" :searchable="true" placeholder="Select products"
                                        trackBy="value" label="label" :object="false" class="multiselect"
                                        @select="updateSelection" />
                                    <p v-if="form.product_ids && form.product_ids.length > 0"
                                        class="text-xs text-gray-500 dark:text-gray-400">
                                        Selected: {{ form.product_ids.length }} product(s)
                                    </p>
                                    <span v-if="discountStore.errorMessage.product_ids" class="text-red-600 text-sm">
                                        {{ discountStore.errorMessage.product_ids[0] }}
                                    </span>
                                </div>
                            </div>


                            <!-- Active Status -->
                            <div class="flex items-center">
                                <input v-model="form.is_active" type="checkbox" id="is_active"
                                    class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 dark:focus:ring-teal-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="is_active"
                                    class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Active
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="authStore.isLoading"
                                    class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                                    :class="{ 'opacity-50 cursor-not-allowed': authStore.isLoading }">
                                    <span v-if="!authStore.isLoading">Update Discount</span>
                                    <span v-else class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Updating...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>