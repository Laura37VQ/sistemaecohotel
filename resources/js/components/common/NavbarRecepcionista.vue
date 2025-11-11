<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { Menu } from 'lucide-vue-next'

const emit = defineEmits(['toggleSidebar'])
const page = usePage()
const user = page.props.auth?.user || { nombres: 'Recepcionista' }
const info = page.props?.info || {}
function logout() {
  router.post('/logout')
}
</script>

<template>
  <header class="flex justify-between items-center bg-white shadow px-6 py-3 border-b border-gray-200">
    <!-- IZQUIERDA -->
    <div class="flex items-center gap-3">
      <!-- Botón hamburguesa (solo en móvil) -->
      <button
        @click="emit('toggleSidebar')"
        class="text-[#2E7D32] hover:text-green-800 transition lg:hidden"
        title="Mostrar / Ocultar menú"
      >
        <Menu class="w-6 h-6" />
      </button>

      <!-- Logo y nombre -->
      <div class="flex items-center gap-3">
        <img
          v-if="info.logo"
          :src="'/storage/' + info.logo"
          alt="Logo"
          class="w-8 h-8 rounded-full object-cover bg-white border"
        />
        <h1 class="font-bold text-gray-700 text-lg">
          {{ info.nombre || 'EcoHotel Villa del Sol' }}
        </h1>
      </div>
    </div>

    <!-- DERECHA -->
    <div class="flex items-center space-x-4">
      <span class="text-gray-600">Hola, {{ user.nombres }}</span>
      <button
        @click="logout"
        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg transition"
      >
        Cerrar sesión
      </button>
    </div>
  </header>
</template>
