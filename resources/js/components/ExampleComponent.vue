<template>
  <div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-lime-600 mb-4">{{ title }}</h2>
    <p class="text-gray-700">{{ message }}</p>
    <button 
      @click="count++"
      class="mt-4 px-4 py-2 bg-lime-500 text-white rounded hover:bg-lime-600 transition"
    >
      カウント: {{ count }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const title = ref('Vue.js 3 コンポーネント');
const message = ref('Laravel + Vue.js + Vite + Tailwind CSS の組み合わせで開発しています。');
const count = ref(0);
</script>
