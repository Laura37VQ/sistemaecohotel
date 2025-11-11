<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const show = ref(false)
const message = ref('')
const type = ref('success')
const timeout = ref(null)

const page = usePage()

watch(
  () => page.props?.flash || {},
  (flash) => {
    if (!flash) return

    clearTimeout(timeout.value)

    if (flash.success) {
      show.value = true
      message.value = flash.success
      type.value = 'success'
    } else if (flash.error) {
      show.value = true
      message.value = flash.error
      type.value = 'error'
    } else if (flash.info) {
      show.value = true
      message.value = flash.info
      type.value = 'info'
    } else {
      show.value = false
      return
    }

    timeout.value = setTimeout(() => (show.value = false), 4000)
  },
  { deep: true, immediate: true }
)

const closeAlert = () => {
  show.value = false
  clearTimeout(timeout.value)
}
</script>

<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed top-5 left-1/2 transform -translate-x-1/2 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold text-sm flex items-center gap-3 min-w-[280px] justify-between"
      :class="{
        'bg-green-600': type === 'success',
        'bg-red-600': type === 'error',
        'bg-blue-600': type === 'info'
      }"
    >
      <div class="flex items-center gap-2">
        <svg
          v-if="type === 'success'"
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 text-white"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>

        <svg
          v-if="type === 'error'"
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 text-white"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>

        <svg
          v-if="type === 'info'"
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 text-white"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20h.01" />
        </svg>

        <span>{{ message }}</span>
      </div>

      <button @click="closeAlert" class="text-white hover:text-gray-200 focus:outline-none">
        ✕
      </button>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(-15px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
