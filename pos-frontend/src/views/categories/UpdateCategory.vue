<script setup>
import AppLayout from '@/components/AppLayout.vue';

import { useRouter, useRoute } from 'vue-router';
import { useCategoryStore } from '@/stores/categoryStore';
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';

const categoryStore = useCategoryStore();
const router = useRouter();
const route = useRoute();

const form = ref({
    name: '',
    description: ''
});

onMounted(async () => {
    await categoryStore.fetchCategories();
    const categoryId = route.params.id;
    const category = await categoryStore.fetchCategoryById(categoryId);

    if (category) {
        form.value = {
            name: category.name,
            description: category.description
        };
    }
});

const submitForm = async () => {
    categoryStore.errorMessage = {};

    const response = await categoryStore.updateCategory(route.params.id, form.value);

    if (response) {
        Swal.fire({
            toast: true,
            icon: 'success',
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            title: 'Category updated successfully!',
        });
        router.push('/categories');

    } else if (categoryStore.errorMessage.general) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: categoryStore.errorMessage.general[0],
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
                    <div v-if="categoryStore.errorMessage.general"
                        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ categoryStore.errorMessage.general[0] }}
                    </div>

                    <form @submit.prevent="submitForm">
                        <div class="space-y-6">
                            <!-- Category Name -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Category Name
                                </label>
                                <input v-model="form.name" type="text" id="name" :class="{
                                    'border-red-500': categoryStore.errorMessage.name,
                                    'border-gray-300': !categoryStore.errorMessage.name
                                }" class="bg-gray-50 border text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter category name">
                                <p v-if="categoryStore.errorMessage?.name" class="mt-1 text-sm text-red-600">
                                    {{ categoryStore.errorMessage.name[0] }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Description
                                </label>
                                <textarea v-model="form.description" id="description" rows="6" :class="{
                                    'border-red-500': categoryStore.errorMessage.description,
                                    'border-gray-300': !categoryStore.errorMessage.description
                                }" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Enter category description"></textarea>
                                <p v-if="categoryStore.errorMessage?.description" class="mt-1 text-sm text-red-600">
                                    {{ categoryStore.errorMessage.description[0] }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="categoryStore.isLoading"
                                    class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                                    :class="{ 'opacity-50 cursor-not-allowed': categoryStore.isLoading }">
                                    <span v-if="!categoryStore.isLoading">Update Category</span>
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