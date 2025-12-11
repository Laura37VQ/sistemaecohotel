<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const page = usePage()
const serviciosOpen = ref(false)

function toggleServiciosSubmenu() {
  serviciosOpen.value = !serviciosOpen.value
}
</script>

<template>
  <div class="min-h-screen w-64 bg-[#2E7D32] text-white flex flex-col overflow-y-auto">
    <!-- Logo y nombre del hotel -->
    <div class="p-4 flex items-center gap-3 border-b border-white/20">
      <img
        v-if="page.props?.info?.logo"
        :src="'/storage/' + page.props.info.logo"
        alt="Logo EcoHotel"
        class="w-10 h-10 rounded-full object-cover bg-white"
      />
      <h1 class="text-lg font-bold leading-tight">
        {{ page.props?.info?.nombre || 'EcoHotel Villa del Sol' }}
      </h1>
    </div>

    <!-- Menú principal -->
    <nav class="flex-1 p-4">
      <ul class="space-y-2">
        <li><Link href="/dashboard/admin" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Dashboard</Link></li>
        <li><Link href="/admin/usuarios" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Usuarios</Link></li>
        <li><Link href="/admin/roles" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Roles</Link></li>
        <li><Link href="/admin/habitaciones" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Habitaciones</Link></li>
        <li><Link href="/admin/reservas" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Reservas</Link></li>

        <!-- Submenú Servicios -->
        <li>
          <button
            @click="toggleServiciosSubmenu"
            class="flex justify-between items-center w-full px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition"
          >
            Servicios
            <svg
              :class="{ 'rotate-90': serviciosOpen }"
              class="w-4 h-4 transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <ul v-if="serviciosOpen" class="pl-4 mt-2 space-y-1">
            <li><Link href="/admin/servicios" class="block px-4 py-2 rounded-lg hover:bg-green-600 transition">Servicios</Link></li>
            <li><Link href="/admin/categorias-servicios" class="block px-4 py-2 rounded-lg hover:bg-green-600 transition">Categorías</Link></li>
          </ul>
        </li>

        <li><Link href="/admin/facturas" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Facturación</Link></li>
        <li><Link href="/admin/reportes" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Reportes</Link></li>
        <li><Link href="/admin/informacion-hotel" class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">Información del Hotel</Link></li>
      </ul>
    </nav>

    <!-- Pie -->
    <div class="p-4 text-xs text-center text-white/80 border-t border-white/20">
      © 2025 {{ page.props?.info?.nombre || 'EcoHotel Villa del Sol' }}
    </div>
  </div>
</template>
