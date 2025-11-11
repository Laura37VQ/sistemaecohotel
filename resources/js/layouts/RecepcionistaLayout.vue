<script setup>
import { ref } from 'vue'
import SidebarRecepcionista from '@/components/common/SidebarRecepcionista.vue'
import NavbarRecepcionista from '@/components/common/NavbarRecepcionista.vue'
import GlobalAlert from '@/components/GlobalAlert.vue'

const sidebarVisible = ref(true)

function toggleSidebar() {
  sidebarVisible.value = !sidebarVisible.value
}
</script>

<template>
  <div class="flex h-screen bg-[#F5F7F8] overflow-hidden relative">
    <!-- SIDEBAR -->
    <aside
      :class="[
        'bg-[#2E7D32] text-white transition-all duration-300 z-40 flex flex-col shadow-lg',
        sidebarVisible ? 'w-64' : 'w-0'
      ]"
    >
      <SidebarRecepcionista v-show="sidebarVisible" />
    </aside>

    <!-- BOTÓN FLOTANTE PARA OCULTAR/MOSTRAR MENÚ -->
    <button
      @click="toggleSidebar"
      class="absolute top-1/2 -translate-y-1/2 left-0 z-50 bg-white text-[#2E7D32] shadow-md rounded-r-full p-2 hover:bg-[#FFCE3E] hover:text-black transition"
      title="Mostrar / Ocultar menú"
    >
      <svg
        v-if="sidebarVisible"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        class="w-5 h-5"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      <svg
        v-else
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        class="w-5 h-5"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="flex-1 flex flex-col transition-all duration-300">
      <NavbarRecepcionista @toggleSidebar="toggleSidebar" />
      <main class="p-4 overflow-y-auto flex-1">
        <slot />
      </main>
      <GlobalAlert />
    </div>
  </div>
</template>
