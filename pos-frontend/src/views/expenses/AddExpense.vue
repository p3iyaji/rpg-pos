<script setup>
import AppLayout from '@/components/AppLayout.vue';
import '@vueform/multiselect/themes/default.css'
import Multiselect from '@vueform/multiselect'

import { useRouter } from 'vue-router';
import { useExpenseStore } from '@/stores/expenseStore';
import { ref, onMounted, computed } from 'vue';
import Swal from 'sweetalert2';
import { useExpenseCategoryStore } from '@/stores/expenseCategoryStore';


const expenseStore = useExpenseStore();
const expenseCategoryStore = useExpenseCategoryStore();
const router = useRouter();

const form = ref({
    name: '',
    expense_category_id: '',
    description: '',
    amount: null,
    date: '',

})

onMounted(async () => {
    try {
        await expenseCategoryStore.fetchExpenseCategories();

    } catch (error) {
        console.error('Error fetching data', error);
    }
})

const expenseCategoryOptions = computed(() =>
    Array.isArray(expenseCategoryStore.expense_categories.data) ? expenseCategoryStore.expense_categories.data.map(u => ({ id: u.id, name: u.name })) : []
)

const addExpense = async () => {
    try {
        const { success, data } = await expenseStore.createExpense({
            name: form.value.name,
            expense_category_id: form.value.expense_category_id,
            description: form.value.description,
            amount: form.value.amount,
            date: formatDateForInput(form.value.date),

        });

        if (success) {
            Swal.fire({
                toast: true,
                icon: 'success',
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                title: 'Expense created successfully!',
            });
            router.push('/expenses');
        } else if (expenseStore.errorMessage.general) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: expenseStore.errorMessage.general[0],
            });
        }
    } catch (error) {
        console.error('Error creating expense: ', error);
    }

}


const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    // Convert to local date in YYYY-MM-DD format
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const goBack = () => {
    router.go(-1);
}

</script>

<template>
    <AppLayout>
        <!-- Container for centering content -->
        <div class="max-w-4xl mx-auto">
            <!-- Card-like container -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <!-- Header section with title and back button -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Add Expenses
                        </h2>
                        <button @click="goBack" type="button"
                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Back
                        </button>
                    </div>
                </div>

                <!-- Form section -->
                <div class="p-6">

                    <!-- General error message -->
                    <div v-if="expenseStore.errorMessage.general"
                        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ expenseStore.errorMessage.general[0] }}
                    </div>

                    <form @submit.prevent="addExpense">

                        <div class="space-y-6">
                            <!-- Expense Name -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Expense Title
                                </label>
                                <input v-model="form.name" type="text" id="name" :class="{
                                    'border-red-500': expenseStore.errorMessage.name,
                                    'border-gray-300': !expenseStore.errorMessage.name
                                }" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Enter unit name">
                                <span v-if="expenseStore.errorMessage?.name" class="text-red-600">
                                    {{ expenseStore.errorMessage.name[0] }}
                                </span>
                            </div>

                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Expense Category
                                </label>
                                <Multiselect v-model="form.expense_category_id" id="expense_category_id"
                                    :options="expenseCategoryOptions" label="name" valueProp="id" :searchable="true"
                                    placeholder="Select a expense category" :filterResults="true" :minChars="1"
                                    :resolveOnLoad="true" trackBy="name" />
                                <span v-if="expenseStore.errorMessage.expense_id" class="text-red-600">
                                    {{ expenseStore.errorMessage.expense_id[0] }}
                                </span>
                            </div>
                            <!-- Description -->
                            <div>
                                <label for="description"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Description
                                </label>
                                <textarea v-model="form.description" id="description" rows="6"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Enter product description"></textarea>
                            </div>
                            <!-- Date -->
                            <div>
                                <label for="description"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Date
                                </label>
                                <input v-model="form.date" type="date" id="date" min="0" step="0.01"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <span v-if="expenseStore.errorMessage.date" class="text-red-600">
                                    {{ expenseStore.errorMessage.date[0] }}
                                </span>
                            </div>
                            <!-- Amount -->
                            <div>
                                <label for="description"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Amount
                                </label>
                                <input v-model="form.amount" type="number" id="amount" min="0" step="0.01"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <span v-if="expenseStore.errorMessage.amount" class="text-red-600">
                                    {{ expenseStore.errorMessage.amount[0] }}
                                </span>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="expenseStore.isLoading"
                                    class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                                    :class="{ 'opacity-50 cursor-not-allowed': expenseStore.isLoading }">
                                    <span v-if="!expenseStore.isLoading">Create Expense</span>
                                    <span v-else class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Creating...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>