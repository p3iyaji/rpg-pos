<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useProductStore } from '@/stores/productStore';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const authStore = useAuthStore();
const unitStore = useProductStore();
const router = useRouter();

const form = ref({
    name: '',
    barcode: '',
    description: '',
    image: null,
    unit_id: null,
    category_id: null,
    price: 0,
    cost_price: 0,
    quantity: 0,
    is_active: true,


});

// image handling
const fileInput = ref(null);
const previewImage = ref(null);
const image = ref(null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Set form data (assuming your form has an 'image' field)
    form.image = file;

    // Create preview
    previewImage.value = {
        name: file.name,
        url: URL.createObjectURL(file)
    };

    // Reset the input to allow selecting the same file again
    e.target.value = '';

}

const removePreviewImage = () => {
    // Remove preview
    previewImage.value = null;
    // Remove from form data
    form.image = null;
};

const deleteImage = () => {
    // For existing image (when editing)
    image.value = null;
    // You would typically also make an API call to delete from server
};

const addUnit = async () => {
    try {
        const newProduct = await productStore.createProduct({
            name: form.value.name,
            barcode: form.value.barcode,
            description: form.value.description,
            image: form.value.image,
            unit_id: form.value.unit_id,
            category_id: form.value.category_id,
            price: form.value.price,
            cost_price: form.value.cost_price,
            quantity: form.value.quantity,
            is_active: form.value.is_active
        });


        Swal.fire({
            toast: true,
            icon: 'success',
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            title: 'Product created successfully!',
        });
        router.push('/units');


    } catch (err) {
        Swal.fire({
            toast: true,
            icon: 'error',
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            title: 'An unexpected error occurred.',
        });
    }
};

const goBack = () => {
    router.push('products');
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
                            Add Product
                        </h2>
                        <button @click="goBack" type="button"
                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Back
                        </button>
                    </div>
                </div>

                <!-- Form section -->
                <div class="p-6">
                    <form @submit.prevent="addProduct">
                        <div class="space-y-6">
                            <!-- Unit Name -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Product Name
                                </label>
                                <input v-model="form.name" type="text" id="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter unit name" required>
                            </div>

                            <!-- image handling -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Product Image
                                </label>
                                <!-- Current Image (if editing) -->
                                <div v-if="image" class="mb-4">
                                    <div class="relative inline-block">
                                        <img class="w-24 h-24 rounded-sm object-cover" :src="`/${image}`" alt="">
                                        <span
                                            class="absolute top-0 right-0 transform -translate-y-1/2 w-5 h-5 bg-red-400 border-2 border-white dark:border-gray-800 rounded-full cursor-pointer">
                                            <span @click="deleteImage"
                                                class="text-white text-xs font-bold absolute flex items-center justify-center w-full h-full">×</span>
                                        </span>
                                    </div>
                                </div>
                                <!-- Image Upload Component -->
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                                    <input type="file" id="image" @change="handleFileChange" accept="image/*"
                                        class="hidden" ref="fileInput">
                                    <label for="image" class="flex flex-col items-center justify-center cursor-pointer">
                                        <svg class="w-8 h-8 mb-2 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        <p class="text-sm text-gray-500">
                                            <span class="font-semibold">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                    </label>

                                    <!-- Preview of selected file before upload -->
                                    <div v-if="previewImage" class="mt-4">
                                        <div class="relative inline-block">
                                            <img :src="previewImage.url" class="w-24 h-24 object-cover rounded">
                                            <button @click="removePreviewImage"
                                                class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>
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

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="authStore.isLoading"
                                    class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                                    :class="{ 'opacity-50 cursor-not-allowed': authStore.isLoading }">
                                    <span v-if="!authStore.isLoading">Create Unit</span>
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