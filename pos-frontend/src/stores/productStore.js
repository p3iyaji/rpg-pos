import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';



export const useProductStore = defineStore('product', () => {

    const products = ref({});

    const currentProduct = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref({});

    const authstore = useAuthStore();


    const fetchProducts = async (page = 1) => {
        await authstore.isAuthenticated;
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/products?page=${page}`);
            products.value = response.data;

        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to fetch products';
        } finally {
            isLoading.value = false;
        }
    }

    const createProduct = async (productData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};
            const response = await axios.post('/api/products', productData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            // to clear any previous errors on success
            errorMessage.value = {};

            return {
                success: true,
                data: response.data
            };

        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                errorMessage.value = error.response.data.errors;
            } else {
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to create product']
                };
            }
            return {
                success: false,
                error: errorMessage.value
            };
        } finally {
            isLoading.value = false;
        }
    }
    //use this with the show function in the controller to get single product
    const fetchProductById = async (id) => {
        try {
            const response = await axios.get(`/api/products/${id}`);
            return response.data;
        } catch (err) {
            errorMessage.value = 'Failed to fetch product';
            return null;
        }
    }


    // Add to your existing store
    const updateProduct = async (productId, productData) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            const response = await axios.post(`/api/products/${productId}`, productData);

            // Update the product in the local state
            const index = products.value.data.findIndex(p => p.id === productId);
            if (index !== -1) {
                products.value.data[index] = response.data;
            }

            return response.data;
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                // Only handle API validation errors
                errorMessage.value = error.response.data.errors;
            } else {
                // For other errors, just show a general message
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to update category']
                };
            }
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    const deleteProduct = async (productId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/products/${productId}`);

            // Remove the deleted product from local state
            products.value.data = products.value.data.filter(product => product.id != productId);

            // Update pagination counts
            products.value.total -= 1;
            products.value.to -= 1;

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete product';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        products,
        currentProduct,
        isLoading,
        errorMessage,
        fetchProducts,
        createProduct,
        updateProduct,
        fetchProductById,
        deleteProduct,
    }
})
