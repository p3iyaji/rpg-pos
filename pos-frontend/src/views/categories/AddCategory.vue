<script setup>
import AppLayout from '@/components/AppLayout.vue';

import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useCategoryStore } from '@/stores/categoryStore';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const authStore = useAuthStore();
const categoryStore = useCategoryStore();
const router = useRouter();

const form = ref({
    name: '',
    description: '',
    is_active: false,
})


const addCategory = async () => {

    const response = await categoryStore.createCategory({
        name: form.value.name,
        description: form.value.description,
        is_active: form.value.is_active
    });

    if (response) {
        Swal.fire({
            toast: true,
            icon: 'success',
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            title: 'Category created successfully!',
        });
        router.push('/categories');
    } else if (categoryStore.errorMessage.general) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: categoryStore.errorMessage.general[0],
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
                            Add Categories
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
                    <div v-if="categoryStore.errorMessage.general"
                        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ categoryStore.errorMessage.general[0] }}
                    </div>
                    <form @submit.prevent="addCategory">
                        <div class="space-y-6">
                            <!-- Category Name -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Category Name
                                </label>
                                <input v-model="form.name" type="text" id="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter category name">
                                <span v-if="categoryStore.errorMessage.name" class="text-red-600">
                                    {{ categoryStore.errorMessage.name[0] }}
                                </span>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Description
                                </label>
                                <textarea v-model="form.description" id="description" rows="6"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Enter unit description"></textarea>
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
                                    <span v-if="!authStore.isLoading">Create Category</span>
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