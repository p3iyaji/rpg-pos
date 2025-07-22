<script setup>
import AppLayout from '@/components/AppLayout.vue';
import Multiselect from '@vueform/multiselect'

import axios from 'axios';

import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useSupplierStore } from '@/stores/supplierStore';
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';

const authStore = useAuthStore();
const supplierStore = useSupplierStore();
const router = useRouter();

const form = ref({
    name: '',
    contact_person: '',
    email: '',
    address: '',
    phone: '',
    is_active: true,
    product_ids: []

})

const products = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.data.map(product => ({
            value: product.id,  // Can be number or string
            label: product.name
        }));
    } catch (error) {
        console.error('Error loading products:', error);
    }
});

const updateSelection = () => {
    nextTick(() => {
        form.value.product_ids = [...form.value.product_ids];
    });
};


const addSupplier = async () => {

    try {
        const { success, data } = await supplierStore.createSupplier({
            name: form.value.name,
            contact_person: form.value.contact_person,
            address: form.value.address,
            email: form.value.email,
            phone: form.value.phone,
            is_active: form.value.is_active,
            product_ids: form.value.product_ids

        });

        if (success) {
            await Swal.fire({
                toast: true,
                icon: 'success',
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                title: data.message || 'Supplier created successfully!',
            });
            router.push('/suppliers');
        }
    } catch (error) {
        console.error('Supplier creation error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: supplierStore.errorMessage.general?.[0] || 'Failed to create supplier',
        });
    }
}

const goBack = () => {
    router.go(-1);
}

</script>

<template>
    <AppLayout>
        <!-- Container for centering content -->
        <div class="max-w-4xl mx-auto">
            <!-- Card-like container -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <!-- Header section with title and back button -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Add Supplier
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
                    <div v-if="supplierStore.errorMessage.general"
                        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ supplierStore.errorMessage.general[0] }}
                    </div>
                    <form @submit.prevent="addSupplier">
                        <div class="space-y-6">
                            <!-- Supplier Name -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Supplier Name
                                </label>
                                <input v-model="form.name" type="text" id="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter supplier name">
                                <span v-if="supplierStore.errorMessage.name" class="text-red-600">
                                    {{ supplierStore.errorMessage.name[0] }}
                                </span>
                            </div>
                            <!-- Supplier Contact person -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Contact Person
                                </label>
                                <input v-model="form.contact_person" type="text" id="contact_person"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter supplier name">
                                <span v-if="supplierStore.errorMessage.contact_person" class="text-red-600">
                                    {{ supplierStore.errorMessage.contact_person[0] }}
                                </span>
                            </div>
                            <!-- Supplier Email -->
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Supplier Email
                                </label>
                                <input v-model="form.email" type="text" id="email"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter supplier email">
                                <span v-if="supplierStore.errorMessage.email" class="text-red-600">
                                    {{ supplierStore.errorMessage.email[0] }}
                                </span>
                            </div>
                            <!-- Supplier Phone -->
                            <div>
                                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Supplier phone
                                </label>
                                <input v-model="form.phone" type="text" id="phone"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter supplier phone">
                                <span v-if="supplierStore.errorMessage.phone" class="text-red-600">
                                    {{ supplierStore.errorMessage.phone[0] }}
                                </span>
                            </div>
                            <!-- Supplier Address -->
                            <div>
                                <label for="address"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Supplier address
                                </label>
                                <input v-model="form.address" type="text" id="address"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter supplier phone">
                                <span v-if="supplierStore.errorMessage.address" class="text-red-600">
                                    {{ supplierStore.errorMessage.address[0] }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Select Products
                                </label>
                                <Multiselect v-model="form.product_ids" :options="products" mode="tags"
                                    :closeOnSelect="false" :searchable="true" placeholder="Select products"
                                    trackBy="value" label="label" :object="false" class="multiselect" />
                                <p v-if="form.product_ids && form.product_ids.length > 0"
                                    class="text-xs text-gray-500 dark:text-gray-400">
                                    Selected: {{ form.product_ids.length }} product(s)
                                </p>
                                <span v-if="supplierStore.errorMessage.product_ids" class="text-red-600 text-sm">
                                    {{ supplierStore.errorMessage.product_ids[0] }}
                                </span>
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
                                    <span v-if="!authStore.isLoading">Create Supplier</span>
                                    <span v-else class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Creating...
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