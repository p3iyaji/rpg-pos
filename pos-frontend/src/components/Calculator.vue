<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: String
});

const emit = defineEmits(['close', 'apply']);

const calculatorValue = ref('');
const MAX_INPUT_LENGTH = 15; // Prevent excessively long inputs

const calculatorButtons = ref([
    '7', '8', '9', '/',
    '4', '5', '6', '*',
    '1', '2', '3', '-',
    '0', '.', '=', '+',
    'C', '⌫', '%'
]);

// More sophisticated expression parser
const parseExpression = (expr) => {
    // Step 1: Basic sanitization
    let cleanExpr = expr.replace(/[^0-9+\-*/.%()]/g, '');

    // Step 2: Handle percentages with proper operator precedence
    cleanExpr = cleanExpr.replace(/([\d.]+)%/g, (match, num) => {
        return `(${num}/100)`;
    });

    // Step 3: Replace × and ÷ with * and /
    cleanExpr = cleanExpr.replace(/×/g, '*').replace(/÷/g, '/');

    return cleanExpr;
};

// Validate the calculation result
const validateResult = (result) => {
    if (typeof result !== 'number' || !isFinite(result)) {
        return 'Error';
    }

    // Round to 8 decimal places to avoid floating point weirdness
    const rounded = Math.round(result * 100000000) / 100000000;

    // Return as string, removing trailing .0 if needed
    return rounded.toString().replace(/\.0+$/, '');
};

const safeCalculate = (expr) => {
    if (!expr || expr.length > MAX_INPUT_LENGTH) {
        return 'Error';
    }

    try {
        const parsedExpr = parseExpression(expr);
        if (!parsedExpr) return 'Error';

        // eslint-disable-next-line no-new-func
        const result = new Function(`return ${parsedExpr}`)();
        return validateResult(result);
    } catch {
        return 'Error';
    }
};

const handleInput = (value) => {
    if (value === 'C') {
        calculatorValue.value = '';
    } else if (value === '⌫') {
        calculatorValue.value = calculatorValue.value.slice(0, -1);
    } else if (value === '=') {
        calculatorValue.value = safeCalculate(calculatorValue.value);
    } else {
        // Prevent input from growing too large
        if (calculatorValue.value.length < MAX_INPUT_LENGTH) {
            calculatorValue.value += value;
        }
    }
};

const applyValue = () => {
    // Only apply if we have a valid number
    if (calculatorValue.value && calculatorValue.value !== 'Error') {
        emit('apply', calculatorValue.value);
        emit('close');
    }
};

// Disable apply button when result is invalid
const canApply = computed(() => {
    return calculatorValue.value &&
        calculatorValue.value !== 'Error' &&
        !calculatorValue.value.endsWith('=');
});
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-64">
            <div class="p-4">
                <input v-model="calculatorValue" type="text"
                    class="w-full p-2 mb-2 border border-gray-300 rounded text-right text-xl font-mono" readonly />
                <div class="text-xs text-gray-500 mb-2 h-6">
                    <span v-if="calculatorValue.length >= MAX_INPUT_LENGTH" class="text-red-500">
                        Max length reached
                    </span>
                    <span v-else>
                        Operators: + - * / %
                    </span>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <button v-for="btn in calculatorButtons" @click="handleInput(btn)"
                        :disabled="btn !== 'C' && calculatorValue.length >= MAX_INPUT_LENGTH" :class="{
                            'bg-teal-100 hover:bg-teal-200': !['C', '⌫', '%', '='].includes(btn),
                            'bg-red-100 hover:bg-red-200': btn === 'C',
                            'bg-gray-100 hover:bg-gray-200': ['⌫', '%'].includes(btn),
                            'bg-blue-100 hover:bg-blue-200': btn === '=',
                            'opacity-50': btn !== 'C' && calculatorValue.length >= MAX_INPUT_LENGTH
                        }" class="p-2 rounded transition disabled:opacity-50">
                        {{ btn }}
                    </button>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button @click="applyValue" :disabled="!canApply" :class="{
                        'bg-teal-800 text-white': canApply,
                        'bg-teal-300 text-white cursor-not-allowed': !canApply
                    }" class="flex-1 py-2 rounded hover:bg-teal-700 transition">
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