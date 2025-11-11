<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import type { PageProps as InertiaPageProps } from '@inertiajs/core'

// 🔹 1. Definimos la interfaz de la información del hotel
interface HotelInfo {
  logo?: string
  nombre?: string
}

// 🔹 2. Extendemos las props base de Inertia (para evitar el error TS2344)
interface CustomPageProps extends InertiaPageProps {
  info?: HotelInfo
}

// 🔹 3. Indicamos a usePage que usará este tipo extendido
const page = usePage<CustomPageProps>()

// 🔹 4. Props opcionales para el título y descripción del layout
defineProps<{
  title?: string
  description?: string
}>()
</script>


<template>
  <div class="grid lg:grid-cols-2 min-h-screen">
    <!-- 🌿 Lado izquierdo -->
    <div class="hidden lg:flex flex-col items-center justify-center bg-[#2E7D32] text-white p-10">
      <div
        v-if="page?.props?.info?.logo"
        class="w-28 h-28 mb-6 rounded-full bg-white flex items-center justify-center shadow-lg border-4 border-[#FFCE3E]"
      >
      <img
        :src="'/storage/' + page.props.info.logo"
        alt="Logo EcoHotel"
        class="w-24 h-24 rounded-full object-cover"
      />
      </div>
      <h1 class="text-3xl font-bold mb-2">
        {{ page?.props?.info?.nombre || 'EcoHotel Villa del Sol' }}
      </h1>
      <p class="text-lg text-center opacity-80">Turismo rural y sostenible</p>

      <Link
        href="/"
        class="mt-8 px-6 py-2 bg-[#FFCE3E] text-[#2E7D32] font-semibold rounded-lg hover:bg-[#FFD95B] transition"
      >
        Ir al inicio
      </Link>
    </div>

    <!-- 🌞 Lado derecho -->
    <div class="flex flex-col justify-center items-center bg-white px-6 py-10">
      <div class="w-full max-w-sm space-y-6">
        <div class="text-center">
          <h2 v-if="title" class="text-2xl font-bold text-[#2E7D32]">{{ title }}</h2>
          <p v-if="description" class="text-gray-500 mt-2">{{ description }}</p>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>
