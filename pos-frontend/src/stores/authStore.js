import { defineStore } from 'pinia';
import axios, { AxiosError } from 'axios';
import router from '@/router';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  // dfine our state and methods

  const user = ref();
  const isAuthenticated = ref(false);
  const errorMessage = ref({});
  const isLoading = ref(false);

  const getToken = async () => {
    await axios.get('/sanctum/csrf-cookie');
  }

  const register = async (credentials) => {
    await this.getToken();
    try {
      await axios.post('/api/register', credentials);
      await getUser();
      router.push('/dashboard');
    } catch (error) {
      if (error instanceof AxiosError && error.response?.status === 422) {
        //
      }
    }

  }

  const login = async (credentials) => {
    await getToken();
    isLoading.value = true;
    try {
      await axios.post('/api/login', credentials);
      await getUser();
      errorMessage.value = {};

    } catch (error) {
      isLoading.value = false;

      if (error instanceof AxiosError && error.response?.status === 422) {
        const errors = error.response.data.errors;

        //normalizing the errors into object format
        if (Array.isArray(errors)) {
          //if it is a flat array store in a generic key
          errorMessage.value = { general: errors };

        } else {
          errorMessage.value = errors;
        }
      } else {
        errorMessage.value = { general: ['Login failed (Invalid credentials)'] };

      }
    }
  }

  const getUser = async () => {
    await getToken();
    if (isAuthenticated.value) return;
    try {
      const response = await axios.get('/api/user');
      user.value = response.data;
      isAuthenticated.value = true;
    } catch (error) {
      console.error(error);
    }
  }

  const cleanState = () => {
    user.value = null;
    isAuthenticated.value = false;
  }

  const logout = async () => {
    try {
      await axios.post('/api/logout');
      router.push('/login')
      user.value = null;
      isAuthenticated.value = false;

    } catch (error) {
      console.error(error);
    }
  }

  return {
    user,
    isAuthenticated,
    errorMessage,
    isLoading,
    register,
    login,
    getUser,
    logout,
    cleanState,
  }

},
  {
    persist: {
      storage: sessionStorage,
      pick: ['user', 'isAuthenticated'],
    }
  })