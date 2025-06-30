<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';



const router = useRouter();
const authStore = useAuthStore();

console.log(authStore.errorMessage);


const form = ref({
    email: '',
});



const handleForgotpassword = async () => {

    await authStore.forgotPassword({
        email: form.value.email,
    });

}


</script>

<template>

    <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-10 w-auto" src="/images/realpay-logo.png" alt="Your Company" />
            <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-teal-800">Forgot Password?
            </h2>
            <p class="mb-5 text-center text-sm/6 text-gray-500">

                <a href="login" class="font-semibold text-teal-600 hover:text-teal-500"> <- go back to Login</a>
            </p>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm border border-teal-600 rounded-md shadow-md">
            <form class="space-y-6 p-10" @submit.prevent="handleForgotpassword">
                <span v-if="authStore.errorMessage.general" class="text-red-600">
                    {{ authStore.errorMessage.general[0] }}
                </span>
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-teal-800">Email address</label>
                    <div class="mt-2">
                        <input type="email" v-model="form.email" name="email" id="email" autocomplete="email"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1
                            border border-teal-600 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <span v-if="authStore.errorMessage.email" class="text-red-600">{{
                            authStore.errorMessage.email[0] }}</span>
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="loading"
                        class="flex w-full justify-center rounded-md bg-teal-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        <span v-if="!authStore.isLoading">Send email</span>
                        <span v-else>Logging in...</span>
                    </button>
                </div>
            </form>


        </div>
    </div>
</template>
