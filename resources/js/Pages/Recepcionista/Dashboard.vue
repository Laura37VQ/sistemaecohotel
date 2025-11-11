<script setup lang="ts">
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Dashboard Recepcionista" />

  <RecepcionistaLayout>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <!-- Tarjeta Clientes -->
      <Link
        href="/recepcionista/clientes"
        class="bg-[#FFCE3E] shadow rounded-xl p-6 flex flex-col items-center text-black cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold">Gestión de Clientes</h2>
        <p class="mt-2 text-center">
          Registra nuevos huéspedes y actualiza la información de los clientes.
        </p>
      </Link>

      <!-- Tarjeta Reservas -->
      <Link
        href="/recepcionista/reservas"
        class="bg-white shadow rounded-xl p-6 flex flex-col items-center cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold text-[#2E7D32]">Gestión de Reservas</h2>
        <p class="text-gray-500 mt-2 text-center">
          Registra, actualiza o consulta las reservas de los huéspedes.
        </p>
      </Link>

      <!-- Tarjeta Check-in / Check-out -->
      <Link
        href="/recepcionista/checkin-checkout"
        class="bg-[#2E7D32] shadow rounded-xl p-6 flex flex-col items-center text-white cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold">Check-in / Check-out</h2>
        <p class="mt-2 text-center text-white/90">
          Administra el ingreso y salida de los huéspedes del hotel.
        </p>
      </Link>

      <!-- Tarjeta Facturación -->
      <Link
        href="/recepcionista/facturas"
        class="bg-[#388E3C] shadow rounded-xl p-6 flex flex-col items-center text-white cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold">Facturación</h2>
        <p class="mt-2 text-center text-white/90">
          Genera facturas de los servicios utilizados por los huéspedes.
        </p>
      </Link>
    </div>
  </RecepcionistaLayout>
</template>
