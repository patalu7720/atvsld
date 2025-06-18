<template>
  <div v-if="visible" class="fixed inset-0 bg-black/30 z-50 flex items-center justify-center">
    <div class="flex flex-col items-center gap-2">
      <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <span class="text-white text-sm">Đang tải dữ liệu...</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  visible: {
    type: Boolean,
    default: false,
  }
});
</script>
