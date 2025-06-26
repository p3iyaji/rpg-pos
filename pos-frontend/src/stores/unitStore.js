import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import { ref } from 'vue';
import { useAuthStore } from './authStore';



export const useUnitStore = defineStore('unit', () => {

    const units = ref({});

    const currentUnit = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref(null);

    const authstore = useAuthStore();


    const fetchUnits = async (page = 1) => {
        await authstore.isAuthenticated;
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/units?page=${page}`);
            units.value = response.data;

        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to fetch Units';
        } finally {
            isLoading.value = false;
        }
    }

    const createUnit = async (unitData) => {
        try {
            isLoading.value = true;
            const response = await axios.post('/api/units', unitData);
            units.value.push(response.data.unit)
            return response.data;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to create Unit';
        } finally {
            isLoading.value = false;
        }
    }
    //use this with the show function in the controller to get single unit
    const fetchUnitById = async (id) => {
        try {
            const response = await axios.get(`/api/units/${id}`);
            return response.data;
        } catch (err) {
            errorMessage.value = 'Failed to fetch unit';
            return null;
        }
    }


    // Add to your existing store
    const updateUnit = async (unitId, unitData) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            const response = await axios.put(`/api/units/${unitId}`, unitData);

            // Update the unit in the local state
            const index = units.value.data.findIndex(u => u.id === unitId);
            if (index !== -1) {
                units.value.data[index] = response.data;
            }

            return response.data;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to update unit';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }


    // const getUnitById = (unitId) => {
    //     if (!units.value?.data) return null;
    //     console.log(units);
    //     return units.value.data.find(unit => unit.id == unitId);
    // }

    const deleteUnit = async (unitId) => {
        try {
            isLoading.value = true;
            errorMessage.value = null;

            await axios.delete(`/api/units/${unitId}`);

            // Remove the deleted unit from local state
            units.value.data = units.value.data.filter(unit => unit.id != unitId);

            // Update pagination counts
            units.value.total -= 1;
            units.value.to -= 1;

            return true;
        } catch (err) {
            errorMessage.value = err.response?.data?.message || 'Failed to delete unit';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        units,
        currentUnit,
        isLoading,
        errorMessage,
        fetchUnits,
        createUnit,
        updateUnit,
        fetchUnitById,
        deleteUnit,
    }
})
