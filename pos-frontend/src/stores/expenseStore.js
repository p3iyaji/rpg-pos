import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';


export const useExpenseStore = defineStore('expense', () => {
    const expenses = ref({});
    const currentExpense = ref(null);
    const errorMessage = ref({});
    const isLoading = ref(false)

    const authStore = useAuthStore();

    const fetchExpenses = async (page = 1) => {
        await authStore.isAuthenticated;

        try {
            isLoading.value = true;
            const response = await axios.get(`/api/expenses?page=${page}`);
            expenses.value = response.data;

        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Failed to fetch expenses';
        } finally {
            isLoading.value = false;
        }
    }

    const createExpense = async (expenseData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};

            const response = await axios.post('/api/expenses', expenseData);
            errorMessage.value = {};

            return {
                success: true,
                data: response.data
            };
        } catch (error) {
            if (error.response?.status === 422) {
                errorMessage.value = error.response.data.errors || {};
            } else {
                errorMessage.value = {
                    general: [error.response?.data?.message || error.message || 'Failed to create expense']
                }
            }
            return {
                success: false,
                error: errorMessage.value
            };
        } finally {
            isLoading.value = false;
        }
    }

    const fetchExpenseById = async (id) => {
        try {
            const response = await axios.get(`/api/expenses/${id}`);
            return response.data;
        } catch (error) {
            errorMessage.value = 'Failed to fetch expense';
            return null;
        }
    }

    const updateExpense = async (expenseId, expenseData) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            const response = await axios.put(`/api/expenses/${expenseId}`, expenseData);

            // update the expense in the local state
            const index = expenses.value.data?.findIndex(e => e.id === expenseId);
            if (index !== -1 && expenses.value.data) {
                expenses.value.data[index] = response.data;
            }
            return response.data;
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                errorMessage.value = error.response.data.errors;
            } else {
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to update expense']
                };
            }
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    const deleteExpense = async (expenseId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/expenses/${expenseId}`);

            //remove deleted expense from local storage
            expenses.value.data = expenses.value.data.filter(expense => expense.id != expenseId);
            expenses.value.total -= 1;
            expenses.value.to -= 1;

            return true;
        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Failed to delete expense';
            throw error;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        expenses,
        currentExpense,
        isLoading,
        errorMessage,
        fetchExpenses,
        createExpense,
        updateExpense,
        fetchExpenseById,
        deleteExpense,
    }
})