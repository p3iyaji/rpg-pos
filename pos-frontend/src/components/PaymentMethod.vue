<script setup>
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    totalAmount: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['payment-completed']);

const paymentMethods = ref([
    { id: 1, name: 'Cash', value: 'cash' },
    { id: 2, name: 'Card', value: 'card' },
    { id: 3, name: 'Transfer', value: 'transfer' }
]);

const selectedMethod = ref(null);
const amountTendered = ref(0);
const showPaymentModal = ref(false);

const changeDue = computed(() => {
    if (!amountTendered.value || !selectedMethod.value) return 0;
    return amountTendered.value - props.totalAmount;
});

const handlePayment = () => {
    if (!selectedMethod.value) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a payment method',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (selectedMethod.value.value === 'cash' && amountTendered.value < props.totalAmount) {
        Swal.fire({
            title: 'Error!',
            text: `Amount tendered (${formatCurrency(amountTendered.value)}) is less than total (${formatCurrency(props.totalAmount)})`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    emit('payment-completed', {
        method: selectedMethod.value.value,
        amountTendered: amountTendered.value,
        changeDue: changeDue.value
    });

    // Reset for next payment
    selectedMethod.value = null;
    amountTendered.value = 0;
};

const formatCurrency = (amount) => {
    const formattedAmount = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
    return `₦${formattedAmount}`;
};
</script>

<template>
    <div class="payment-method-container">
        <button @click="showPaymentModal = true"
            class="w-full bg-teal-800 text-white py-3 rounded-lg font-bold hover:bg-teal-700 transition">
            Select Payment Method
        </button>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Select Payment Method</h3>

                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button v-for="method in paymentMethods" :key="method.id" @click="selectedMethod = method" :class="{
                        'bg-teal-800 text-white': selectedMethod?.id === method.id,
                        'bg-gray-100 hover:bg-gray-200': selectedMethod?.id !== method.id
                    }" class="p-2 rounded-lg text-center transition">
                        {{ method.name }}
                    </button>
                </div>

                <!-- Amount Tendered for Cash -->
                <div v-if="selectedMethod?.value === 'cash'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Tendered</label>
                    <input v-model="amountTendered" type="number" min="0" step="0.01"
                        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500"
                        @input="amountTendered = parseFloat($event.target.value) || 0">
                    <div v-if="changeDue > 0" class="text-green-600 mt-1">
                        Change Due: {{ formatCurrency(changeDue) }}
                    </div>
                    <div v-else-if="changeDue < 0" class="text-red-600 mt-1">
                        Amount insufficient ({{ formatCurrency(-changeDue) }} needed)
                    </div>
                </div>

                <!-- Additional fields for card/transfer can be added here -->

                <div class="flex justify-end space-x-2 mt-4">
                    <button @click="showPaymentModal = false" class="px-4 py-2 bg-gray-200 rounded-lg">
                        Cancel
                    </button>
                    <button @click="handlePayment" class="px-4 py-2 bg-teal-800 text-white rounded-lg">
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.payment-method-container {
    margin-top: 1rem;
}
</style>