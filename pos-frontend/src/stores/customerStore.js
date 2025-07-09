import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';

export const useCustomerStore = defineStore('customer', () => {
    const customers = ref({});

    const currentCustomer = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref({});

    const authStore = useAuthStore();

    const fetchCustomers = async (page = 1) => {
        authStore.isAuthenticated;

        try {
            isLoading.value = true;
            const response = await axios.get(`/api/customers?page=${page}`);
            customers.value = response.data;

        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Failed to fetch customers';
        } finally {
            isLoading.value = false;
        }
    }

    const createCustomer = async (customerData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};

            const response = await axios.post('/api/customers', customerData);

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
                    general: [error.response?.data?.message || error.message || 'Failed to create customer']
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

    const fetchCustomerById = async (id) => {
        try {
            const response = await axios.get(`/api/customers/${id}`);
            return response.data;
        } catch (error) {
            errorMessage.value = 'Failed to fetch customer';
            return null;
        }
    }


    const updateCustomer = async (customerId, customerData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};

            const response = await axios.put(`/api/customers/${customerId}`, customerData);

            // Update the customer in the local state
            const index = customers.value.data?.findIndex(c => c.id === customerId);
            if (index !== -1 && customers.value.data) {
                customers.value.data[index] = response.data.data;
            }

            return response.data;
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                // Only handle API validation errors
                errorMessage.value = error.response.data.errors;
            } else {
                // For other errors, just show a general message
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to update customer']
                };
            }
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    const deleteCustomer = async (customerId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/customers/${customerId}`);

            // Remove the deleted customer from local state
            customers.value.data = customers.value.data.filter(customer => customer.id != customerId);

            // Update pagination counts
            customers.value.total -= 1;
            customers.value.to -= 1;

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete customer';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        customers,
        currentCustomer,
        isLoading,
        errorMessage,
        fetchCustomers,
        fetchCustomerById,
        createCustomer,
        updateCustomer,
        deleteCustomer

    }
});