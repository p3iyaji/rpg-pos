<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: String
});

const emit = defineEmits(['close', 'apply']);

const calculatorValue = ref('');

const calculatorButtons = ref([
    '7', '8', '9', '/',
    '4', '5', '6', '*',
    '1', '2', '3', '-',
    '0', '.', '=', '+',
    'C', '⌫', '%'
]);

const handleInput = (value) => {
    if (value === 'C') {
        calculatorValue.value = '';
    } else if (value === '⌫') {
        calculatorValue.value = calculatorValue.value.slice(0, -1);
    } else if (value === '=') {
        try {
            let expression = calculatorValue.value;

            // Handle `+ B%` and `- B%` (relative to the previous number)
            expression = expression.replace(/([\d.]+)\s*([\+\-])\s*([\d.]+)%/g, (match, a, op, b) => {
                return `${a} ${op} (${a} * ${b} / 100)`;
            });

            // Handle `* B%` or `/ B%` (direct percentage)
            expression = expression.replace(/([\d.]+)\s*([*/])\s*([\d.]+)%/g, (match, a, op, b) => {
                return `${a} ${op} (${b} / 100)`;
            });

            calculatorValue.value = eval(expression).toString();
        } catch {
            calculatorValue.value = 'Error';
        }
    } else if (value === '%') {
        // Only add % if there's a number before it
        if (calculatorValue.value.match(/[\d.]$/)) {
            calculatorValue.value += '%';
        }
    } else {
        calculatorValue.value += value;
    }
};

// Helper function to get the previous number in the expression
const getPreviousNumber = (expression, currentMatch) => {
    const beforeMatch = expression.substring(0, expression.indexOf(currentMatch));
    const numbers = beforeMatch.match(/[\d.]+$/);
    return numbers ? numbers[0] : '1'; // Default to 1 if no previous number found
};

const applyValue = () => {
    emit('apply', calculatorValue.value);
    emit('close');
};
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-64">
            <div class="p-4">
                <input v-model="calculatorValue" type="text"
                    class="w-full p-2 mb-4 border border-gray-300 rounded text-right text-xl" readonly />
                <div class="text-xs text-gray-500 mb-2">
                    Percentage examples: "50+10%" = 55, "100*20%" = 20
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <button v-for="btn in calculatorButtons" @click="handleInput(btn)" :class="{
                        'bg-teal-100 hover:bg-teal-200': !['C', '⌫', '%', '='].includes(btn),
                        'bg-red-100 hover:bg-red-200': btn === 'C',
                        'bg-gray-100 hover:bg-gray-200': ['⌫', '%'].includes(btn),
                        'bg-blue-100 hover:bg-blue-200': btn === '='
                    }" class="p-2 rounded transition">
                        {{ btn }}
                    </button>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button @click="applyValue"
                        class="flex-1 bg-teal-800 text-white py-2 rounded hover:bg-teal-700 transition">
                        Apply to Search
                    </button>
                    <button @click="$emit('close')"
                        class="flex-1 bg-gray-300 py-2 rounded hover:bg-gray-400 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>