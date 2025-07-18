import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';

export const useSupplierStore = defineStore('supplier', () => {
    const suppliers = ref({});

    const currentSupplier = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref({});

    const authStore = useAuthStore();

    const fetchSuppliers = async (page = 1) => {
        authStore.isAuthenticated;

        try {
            isLoading.value = true;
            const response = await axios.get(`/api/suppliers?page=${page}`);
            suppliers.value = response.data;

        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Failed to fetch suppliers';
        } finally {
            isLoading.value = false;
        }
    }

    const createSupplier = async (supplierData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};

            const response = await axios.post('/api/suppliers', supplierData);

            errorMessage.value = {};

            return {
                success: true,
                data: response.data
            }

        } catch (error) {
            if (error.response?.status === 422) {
                errorMessage.value = error.response.data.errors || {};
            } else {
                errorMessage.value = {
                    general: [error.response?.data?.message || error.message || 'Failed to create supplier']
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

    const fetchSupplierById = async (id) => {
        try {
            const response = await axios.get(`/api/suppliers/${id}`);
            return response.data;
        } catch (error) {
            errorMessage.value = 'Failed to fetch supplier';
            return null;
        }
    }


    const updateSupplier = async (supplierId, supplierData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};

            const response = await axios.put(`/api/suppliers/${supplierId}`, supplierData);

            // Update the supplier in the local state
            const index = suppliers.value.data?.findIndex(c => c.id === supplierId);
            if (index !== -1 && suppliers.value.data) {
                suppliers.value.data[index] = response.data.data;
            }

            return response.data;
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                // Only handle API validation errors
                errorMessage.value = error.response.data.errors;
            } else {
                // For other errors, just show a general message
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to update supplier']
                };
            }
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    const deleteSupplier = async (supplierId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/suppliers/${supplierId}`);

            // Remove the deleted supplier from local state
            suppliers.value.data = suppliers.value.data.filter(supplier => supplier.id != supplierId);

            // Update pagination counts
            suppliers.value.total -= 1;
            suppliers.value.to -= 1;

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete supplier';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        suppliers,
        currentSupplier,
        isLoading,
        errorMessage,
        fetchSuppliers,
        fetchSupplierById,
        createSupplier,
        updateSupplier,
        deleteSupplier

    }
});