<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const info = page.props?.info || {}
</script>

<template>
  <div class="h-screen w-64 bg-[#2E7D32] text-white flex flex-col">

    <!-- Logo y nombre -->
    <div class="p-4 flex items-center gap-3 border-b border-white/20">
      <img
        v-if="info.logo"
        :src="'/storage/' + info.logo"
        alt="Logo EcoHotel"
        class="w-10 h-10 rounded-full object-cover bg-white"
      />
      <h1 class="text-lg font-bold leading-tight">
        {{ info.nombre || 'EcoHotel Villa del Sol' }}
      </h1>
    </div>

    <!-- Menú -->
    <nav class="flex-1 p-4">
      <ul class="space-y-2">

        <li>
          <Link href="/dashboard/recepcionista"
            class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">
            Dashboard
          </Link>
        </li>

        <li>
          <Link href="/recepcionista/reservas"
            class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">
            Reservas
          </Link>
        </li>

        <li>
          <Link href="/recepcionista/clientes"
            class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">
            Clientes
          </Link>
        </li>

        <li>
          <Link href="/recepcionista/checkin-checkout"
            class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">
            Check-in / Check-out
          </Link>
        </li>

        <li>
          <Link href="/recepcionista/facturas"
            class="block px-4 py-2 rounded-lg hover:bg-[#FFCE3E] hover:text-black transition">
            Facturas
          </Link>
        </li>

      </ul>
    </nav>

    <!-- Pie -->
    <div class="p-4 text-xs text-center text-white/80 border-t border-white/20">
      © 2025 {{ info.nombre || 'EcoHotel Villa del Sol' }}
    </div>

  </div>
</template>
