import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';



export const useExpenseCategoryStore = defineStore('expense_category', () => {

    const expense_categories = ref({});

    const currentExpenseCategory = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref({});


    const authstore = useAuthStore();


    const fetchExpenseCategories = async (page = 1) => {
        authstore.isAuthenticated;
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/expense-categories?page=${page}`);
            expense_categories.value = response.data;

        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to fetch expense categories';
        } finally {
            isLoading.value = false;
        }
    }

    const createExpenseCategory = async (expenseCategoryData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};

            const response = await axios.post('/api/expense-categories', expenseCategoryData);
            expense_categories.value.push(response.data.expense_category)

            return response.data;

        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                errorMessage.value = error.response.data.errors;
            } else {
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to create expense category']
                };
            }
            return null;

        } finally {
            isLoading.value = false;
        }
    }
    //use this with the show function in the controller to get single expense category
    const fetchExpenseCategoryById = async (id) => {
        try {
            const response = await axios.get(`/api/expense_categories/${id}`);
            return response.data;
        } catch (err) {
            errorMessage.value = 'Failed to fetch expense category';
            return null;
        }
    }


    const updateExpenseCategory = async (expenseCategoryId, expenseCategoryData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {}; // Clear previous errors

            const response = await axios.put(`/api/expense-categories/${expenseCategoryId}`, categoryData);

            // Update the category in the local state
            const index = expense_categories.value.data?.findIndex(c => c.id === expenseCategoryId);
            if (index !== -1 && expense_categories.value.data) {
                expense_categories.value.data[index] = response.data.data;
            }

            return response.data;
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                // Only handle API validation errors
                errorMessage.value = error.response.data.errors;
            } else {
                // For other errors, just show a general message
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to update expense category']
                };
            }
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    const deleteExpenseCategory = async (expenseCategoryId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/expense-categories/${expenseCategoryId}`);

            // Remove the deleted category from local state
            expense_categories.value.data = expense_categories.value.data.filter(expenseCategory => expenseCategory.id != expenseCategoryId);

            // Update pagination counts
            expense_categories.value.total -= 1;
            expense_categories.value.to -= 1;

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete expense category';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        expense_categories,
        currentExpenseCategory,
        isLoading,
        errorMessage,
        fetchExpenseCategories,
        createExpenseCategory,
        updateExpenseCategory,
        fetchExpenseCategoryById,
        deleteExpenseCategory,
    }
})
