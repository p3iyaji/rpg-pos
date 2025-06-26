<script setup>
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



</script>

<template>
  <main>
    <RouterView />
  </main>
</template>
