<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Dashboard Administrador" />

  <AdminLayout>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <!-- Tarjeta Usuarios -->
      <Link
        href="/admin/usuarios"
        class="bg-white shadow rounded-xl p-6 flex flex-col items-center cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold text-[#2E7D32]">Usuarios</h2>
        <p class="text-gray-500 mt-2 text-center">Gestión de roles y clientes</p>
      </Link>

      <!-- Tarjeta Roles -->
      <Link
        href="/admin/roles"
        class="bg-[#FFCE3E] shadow rounded-xl p-6 flex flex-col items-center text-black cursor-pointer hover:bg-[#FFD84C] transition"
      >
        <h2 class="text-xl font-bold">Roles</h2>
        <p class="mt-2 text-center">Control de permisos y accesos</p>
      </Link>

      <!-- Tarjeta Habitaciones -->
      <Link
        href="/admin/habitaciones"
        class="bg-white shadow rounded-xl p-6 flex flex-col items-center cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold text-[#2E7D32]">Habitaciones</h2>
        <p class="text-gray-500 mt-2 text-center">Disponibilidad y estado</p>
      </Link>

      <!-- Tarjeta Reservas -->
      <Link
        href="/admin/reservas"
        class="bg-[#2E7D32] shadow rounded-xl p-6 flex flex-col items-center text-white cursor-pointer hover:bg-[#256429] transition"
      >
        <h2 class="text-xl font-bold">Reservas</h2>
        <p class="mt-2 text-center text-white/90">Crear, modificar y cancelar reservas</p>
      </Link>

      <!-- Tarjeta Servicios -->
      <Link
        href="/admin/servicios"
        class="bg-white shadow rounded-xl p-6 flex flex-col items-center cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold text-[#2E7D32]">Servicios</h2>
        <p class="text-gray-500 mt-2 text-center">Restaurante, spa y más</p>
      </Link>

      <!-- Tarjeta Facturación -->
      <Link
        href="/admin/facturas"
        class="bg-[#388E3C] shadow rounded-xl p-6 flex flex-col items-center text-white cursor-pointer hover:bg-[#2E7D32] transition"
      >
        <h2 class="text-xl font-bold">Facturación</h2>
        <p class="mt-2 text-center text-white/90">Generación y control de facturas</p>
      </Link>

      <!-- Tarjeta Reportes -->
      <Link
        href="/admin/reportes"
        class="bg-white shadow rounded-xl p-6 flex flex-col items-center cursor-pointer hover:shadow-lg transition"
      >
        <h2 class="text-xl font-bold text-[#2E7D32]">Reportes</h2>
        <p class="text-gray-500 mt-2 text-center">Estadísticas e indicadores</p>
      </Link>

      <!-- Tarjeta Información del Hotel -->
      <Link
        href="/admin/informacion-hotel"
        class="bg-[#FFCE3E] shadow rounded-xl p-6 flex flex-col items-center text-black cursor-pointer hover:bg-[#FFD84C] transition"
      >
        <h2 class="text-xl font-bold">Información del Hotel</h2>
        <p class="mt-2 text-center">Datos generales, misión y visión</p>
      </Link>

    </div>
  </AdminLayout>
</template>
