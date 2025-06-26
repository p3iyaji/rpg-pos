<script setup>
import { useAuthStore } from '@/stores/authStore';
import TopHeader from './TopHeader.vue';
import Sidebar from './Sidebar.vue';
import { RouterView, useRoute } from 'vue-router';
import { onMounted, watch } from 'vue';
import { initFlowbite } from 'flowbite'

const route = useRoute();

//re-run flowbite init after each route change
watch(
    () => route.fullPath,
    () => {
        //add timeout to ensure DOM is rendered
        setTimeout(() => {
            initFlowbite();
        }, 0);
    }
);

// initialize components based on data attribute selectors
//run it on first mount
onMounted(() => {
    initFlowbite();
});


const authStore = useAuthStore();
</script>

<template>
    <div class="antialiased bg-gray-50 dark:bg-gray-900">

        <TopHeader />

        <Sidebar />

        <main class="p-4 h-auto pt-10">
            <div class="sm:ml-64 p-4 mt-5">

                <slot />

            </div>
        </main>

    </div>
</template>