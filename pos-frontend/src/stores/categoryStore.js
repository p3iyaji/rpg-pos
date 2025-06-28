import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';



export const useCategoryStore = defineStore('category', () => {

    const categories = ref({});

    const currentCategory = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref(null);

    const authstore = useAuthStore();


    const fetchCategories = async (page = 1) => {
        await authstore.isAuthenticated;
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/categories?page=${page}`);
            categories.value = response.data.data;

        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to fetch categories';
        } finally {
            isLoading.value = false;
        }
    }

    const createCategory = async (categoryData) => {
        try {
            isLoading.value = true;
            const response = await axios.post('/api/categories', categoryData);
            categories.value.push(response.data.category)
            return response.data;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to create categories';
        } finally {
            isLoading.value = false;
        }
    }
    //use this with the show function in the controller to get single category
    const fetchCategoryById = async (id) => {
        try {
            const response = await axios.get(`/api/categories/${id}`);
            return response.data;
        } catch (err) {
            errorMessage.value = 'Failed to fetch category';
            return null;
        }
    }


    // Add to your existing store
    const updateCategory = async (categoryId, categoryData) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            const response = await axios.put(`/api/categories/${categoryId}`, categoryData);

            // Update the category in the local state
            const index = categories.value.data.findIndex(c => c.id === categoryId);
            if (index !== -1) {
                categories.value.data[index] = response.data;
            }

            return response.data;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to update category';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    const deleteCategory = async (categoryId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/categories/${categoryId}`);

            // Remove the deleted category from local state
            categories.value.data = categories.value.data.filter(category => category.id != categoryId);

            // Update pagination counts
            categories.value.total -= 1;
            categories.value.to -= 1;

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete category';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        categories,
        currentCategory,
        isLoading,
        errorMessage,
        fetchCategories,
        createCategory,
        updateCategory,
        fetchCategoryById,
        deleteCategory,
    }
})
