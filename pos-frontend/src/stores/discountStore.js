import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';



export const useDiscountStore = defineStore('discount', () => {

    const discounts = ref({});

    const currentDiscount = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref({});

    const authstore = useAuthStore();


    const fetchDiscounts = async (page = 1) => {
        authstore.isAuthenticated;
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/discounts?page=${page}`);
            discounts.value = response.data;

        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to fetch discounts';
        } finally {
            isLoading.value = false;
        }
    }

    const createDiscount = async (discountData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};

            const response = await axios.post('/api/discounts', discountData);

            errorMessage.value = {}; // Clear errors on success

            return {
                success: true,
                data: response.data
            };

        } catch (error) {
            if (error.response?.status === 422) {
                errorMessage.value = error.response.data.errors || {};
            } else {
                errorMessage.value = {
                    general: [error.response?.data?.message || error.message || 'Failed to create discount']
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

    //use this with the show function in the controller to get single discount
    const fetchDiscountById = async (id) => {
        try {
            const response = await axios.get(`/api/discounts/${id}`);
            return response.data;
        } catch (err) {
            errorMessage.value = 'Failed to fetch discount';
            return null;
        }
    }


    // Add to your existing store
    const updateDiscount = async (discountId, discountData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {}; // Clear previous errors

            const response = await axios.put(`/api/discounts/${discountId}`, discountData);

            // Update the discount in the local state
            const index = discounts.value.data?.findIndex(c => c.id === discountId);
            if (index !== -1 && discounts.value.data) {
                discounts.value.data[index] = response.data.data;
            }

            return response.data;
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                // Only handle API validation errors
                errorMessage.value = error.response.data.errors;
            } else {
                // For other errors, just show a general message
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to update discount']
                };
            }
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    const deleteDiscount = async (discountId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/discounts/${discountId}`);

            // Remove the deleted discount from local state
            discounts.value.data = discounts.value.data.filter(discount => dicount.id != discountId);

            // Update pagination counts
            discounts.value.total -= 1;
            discounts.value.to -= 1;

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete discount';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        discounts,
        currentDiscount,
        isLoading,
        errorMessage,
        fetchDiscounts,
        createDiscount,
        updateDiscount,
        fetchDiscountById,
        deleteDiscount,
    }
})
