import { defineStore } from 'pinia';
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './authStore';


export const useOrderStore = defineStore('order', () => {
    const orders = ref({});
    const currentOrder = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref({});
    const summary = ref(null);

    const authStore = useAuthStore();
    const router = useRouter();

    const fetchOrders = async (page = 1, search = '') => {
        authStore.isAuthenticated;

        try {
            isLoading.value = true;
            const response = await axios.get(`api/orders`, {
                params: {
                    page: page,
                    search: search
                }
            });
            orders.value = {
                data: response.data.data,
                meta: response.data.meta,
                links: response.data.links
            };
            summary.value = response.data.summary;
        } catch (error) {
            errorMessage.value = error.response?.data?.message || 'Failed to fetch orders';
        } finally {
            isLoading.value = false;
        }
    }

    const fetchOrderById = async (id) => {
        authStore.isAuthenticated;
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/orders/${id}`);
            currentOrder.value = response.data.data;
            router.push({ name: 'order-details', params: { id } });
        } catch (error) {
            errorMessage.value = 'Failed to fetch order';
            return null;
        } finally {
            isLoading.value = false;
        }
    }



    return {
        isLoading,
        orders,
        currentOrder,
        summary,
        fetchOrders,
        fetchOrderById,
    }
})