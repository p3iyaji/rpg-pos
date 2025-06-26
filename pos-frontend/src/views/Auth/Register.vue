<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { useRouter } from 'vue-router'


const router = useRouter();

const authStore = useAuthStore();


const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});


const handleRegister = async () => {

    await authStore.register({
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation
    });
    authStore.isAuthenticated;
    router.push('/dashboard');

}

</script>

<template>

    <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-10 w-auto" src="/images/realpay-logo.png" alt="RealPay POS" />
            <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-teal-800">Sign in to your account
            </h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm border border-teal-600 rounded-md shadow-md">
            <form class="space-y-6 p-10" @submit.prevent="handleRegister">
                <div>
                    <label for="name" class="block text-sm/6 font-medium text-teal-800">Full Name</label>
                    <div class="mt-2">
                        <input type="text" v-model="form.name" name="name" id="name"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1
                            border border-teal-600 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <span class="text-red-600" v-if="authStore.errorMessage.name">
                            {{ authStore.errorMessage.name[0] }}
                        </span>
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-teal-800">Email address</label>
                    <div class="mt-2">
                        <input type="email" v-model="form.email" name="email" id="email" autocomplete="email"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1
                            border border-teal-600 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <span class="text-red-600" v-if="authStore.errorMessage.email">
                            {{ authStore.errorMessage.email[0] }}
                        </span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm/6 font-medium text-teal-800">Password</label>

                    </div>
                    <div class="mt-2">
                        <input type="password" v-model="form.password" name="password" id="password"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 
                            border border-teal-600 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <span class="text-red-600" v-if="authStore.errorMessage.password">
                            {{ authStore.errorMessage.password[0] }}
                        </span>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm/6 font-medium text-teal-800">Confirm
                            Password</label>
                    </div>
                    <div class="mt-2">
                        <input type="password" v-model="form.password_confirmation" name="password_confirmation"
                            id="password_confirmation"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 
                            border border-teal-600 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="authStore.isLoading"
                        class="flex w-full justify-center rounded-md bg-teal-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        <span v-if="!authStore.isLoading">Register</span>
                        <span v-else>Creating...</span>
                    </button>
                </div>
            </form>
            <p class="mb-5 text-center text-sm/6 text-gray-500">
                Already Registered?
                {{ ' ' }}
                <a href="login" class="font-semibold text-teal-600 hover:text-teal-500">Click here to
                    Login</a>
            </p>


        </div>
    </div>
</template>
