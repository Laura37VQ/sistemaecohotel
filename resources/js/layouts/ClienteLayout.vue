<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const info = page.props?.info || {}
const menuOpen = ref(false)
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#E8EFEB]">
    <!-- HEADER -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
        <!-- Logo y nombre -->
        <div class="flex items-center gap-3">
          <img
            v-if="info.logo"
            :src="'/storage/' + info.logo"
            alt="Logo EcoHotel"
            class="w-10 h-10 rounded-full object-cover border border-[#2E7D32]"
          />
          <h1 class="text-xl font-extrabold text-[#2E7D32] tracking-tight">
            {{ info.nombre || 'ECO HOTEL VILLA DEL SOL' }}
          </h1>
        </div>

        <!-- Menú desktop -->
        <nav class="hidden md:flex space-x-8 text-gray-700 font-medium">
          <Link href="/cliente/dashboard" class="hover:text-[#2E7D32] transition">Inicio</Link>
          <Link href="/cliente/disponibilidad" class="hover:text-[#2E7D32] transition">Habitaciones</Link>
          <Link href="/cliente/servicios/1" class="hover:text-[#2E7D32] transition">Servicios</Link>
          <Link href="/cliente/facturacion" class="hover:text-[#2E7D32] transition">Facturación</Link>

          <!--  Cierre de sesión seguro -->
          <form @submit.prevent="$inertia.post('/logout')" class="inline">
            <button type="submit" class="text-red-600 hover:text-red-700 transition">
              Cerrar sesión
            </button>
          </form>
        </nav>

        <!-- Botón menú móvil -->
        <button
          class="md:hidden text-[#2E7D32] focus:outline-none"
          @click="menuOpen = !menuOpen"
        >
          <svg
            v-if="!menuOpen"
            xmlns="http://www.w3.org/2000/svg"
            class="w-7 h-7"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="w-7 h-7"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Menú móvil -->
      <transition name="fade">
        <div
          v-if="menuOpen"
          class="md:hidden bg-white shadow-inner border-t border-gray-100"
        >
          <nav class="flex flex-col px-6 py-4 space-y-3 text-gray-700 font-medium">
            <Link href="/cliente/dashboard" @click="menuOpen = false" class="hover:text-[#2E7D32] transition">Inicio</Link>
            <Link href="/cliente/disponibilidad" @click="menuOpen = false" class="hover:text-[#2E7D32] transition">Habitaciones</Link>
            <Link href="/cliente/servicios/1" @click="menuOpen = false" class="hover:text-[#2E7D32] transition">Servicios</Link>
            <Link href="/cliente/facturacion" @click="menuOpen = false" class="hover:text-[#2E7D32] transition">Facturación</Link>

            <!--  Logout seguro en móvil -->
            <form @submit.prevent="$inertia.post('/logout')" class="inline">
              <button
                type="submit"
                class="text-red-600 hover:text-red-700 transition"
                @click="menuOpen = false"
              >
                Cerrar sesión
              </button>
            </form>
          </nav>
        </div>
      </transition>
    </header>

    <!-- CONTENIDO -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- FOOTER -->
    <footer class="bg-[#2E7D32] text-white text-center py-4 mt-auto">
      <p class="text-sm">
        © 2025 {{ info.nombre || 'EcoHotel Villa del Sol' }} — Todos los derechos reservados.
      </p>
    </footer>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
