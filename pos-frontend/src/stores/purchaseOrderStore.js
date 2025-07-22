import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';

export const usePurchaseOrderStore = defineStore('purchaseOrder', () => {
    const purchaseOrders = ref({});
    const currentPurchaseOrder = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref({});

    const authStore = useAuthStore();

    const fetchPurchaseOrders = async (page = 1, status = null) => {
        await authStore.isAuthenticated;
        try {
            isLoading.value = true;
            let url = `/api/purchase-orders?page=${page}`;
            if (status) {
                url += `&status=${status}`;
            }
            const response = await axios.get(url);
            purchaseOrders.value = response.data;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to fetch purchase orders';
        } finally {
            isLoading.value = false;
        }
    }

    const fetchPurchaseOrderById = async (id) => {
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/purchase-orders/${id}`);
            currentPurchaseOrder.value = response.data;
            return response.data;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to fetch purchase order';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    const createPurchaseOrder = async (poData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};
            const response = await axios.post('/api/purchase-orders', poData);

            // Add the new PO to the beginning of the list
            if (purchaseOrders.value.data) {
                purchaseOrders.value.data.unshift(response.data.purchaseOrders);
                purchaseOrders.value.total += 1;
                purchaseOrders.value.to += 1;
            }

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
                    general: [error.response?.data?.message || 'Failed to create purchase order']
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

    const updatePurchaseOrderStatus = async (id, status) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};
            const response = await axios.put(`/api/purchase-orders/${id}/status`, { status });

            // Update the PO in the local state
            if (purchaseOrders.value.data) {
                const index = purchaseOrders.value.data.findIndex(po => po.id === id);
                if (index !== -1) {
                    purchaseOrders.value.data[index] = response.data;
                }
            }

            if (currentPurchaseOrder.value?.id === id) {
                currentPurchaseOrder.value = response.data;
            }

            return {
                success: true,
                data: response.data
            };
        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Failed to update purchase order status';
            return {
                success: false,
                error: errorMessage.value
            };
        } finally {
            isLoading.value = false;
        }
    }

    const receivePurchaseOrder = async (id, receivingData) => {
        try {
            isLoading.value = true;
            errorMessage.value = {};
            const response = await axios.post(`/api/purchase-orders/${id}/receive`, receivingData);

            // Update the PO in the local state
            if (purchaseOrders.value.data) {
                const index = purchaseOrders.value.data.findIndex(po => po.id === id);
                if (index !== -1) {
                    purchaseOrders.value.data[index] = response.data;
                }
            }

            if (currentPurchaseOrder.value?.id === id) {
                currentPurchaseOrder.value = response.data;
            }

            return {
                success: true,
                data: response.data
            };
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 422) {
                errorMessage.value = error.response.data.errors;
            } else {
                errorMessage.value = {
                    general: [error.response?.data?.message || 'Failed to receive purchase order']
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

    const deletePurchaseOrder = async (id) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/purchase-orders/${id}`);

            // Remove the deleted PO from local state
            if (purchaseOrders.value.data) {
                purchaseOrders.value.data = purchaseOrders.value.data.filter(po => po.id !== id);
                purchaseOrders.value.total -= 1;
                purchaseOrders.value.to -= 1;
            }

            if (currentPurchaseOrder.value?.id === id) {
                currentPurchaseOrder.value = null;
            }

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete purchase order';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        purchaseOrders,
        currentPurchaseOrder,
        isLoading,
        errorMessage,
        fetchPurchaseOrders,
        fetchPurchaseOrderById,
        createPurchaseOrder,
        updatePurchaseOrderStatus,
        receivePurchaseOrder,
        deletePurchaseOrder
    }
});