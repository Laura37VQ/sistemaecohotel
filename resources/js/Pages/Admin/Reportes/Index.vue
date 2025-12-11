<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
</script>

<template>
  <Head title="Panel de Reportes" />

  <AdminLayout>
    <div class="max-w-5xl mx-auto mt-8">

      <!-- Título principal -->
      <h2 class="text-4xl font-extrabold text-green-700 mb-8 text-center">
        Panel de Reportes
      </h2>

      <!-- Texto introductorio -->
      <p class="text-center text-gray-600 mb-10">
        Seleccione uno de los reportes disponibles para visualizar información detallada del hotel.
      </p>

      <!-- Contenedor de tarjetas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Tarjeta 1 -->
        <Link
          href="/admin/reportes/ocupacion"
          class="bg-white shadow-md hover:shadow-xl transition rounded-2xl p-6 border border-green-200 flex items-center gap-4 hover:bg-green-50"
        >
          <div class="p-4 rounded-full bg-green-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m12 7h-5m0 11V10" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-green-700">Reporte de Ocupación</h3>
            <p class="text-gray-600 text-sm">Estado actual de habitaciones ocupadas y disponibles.</p>
          </div>
        </Link>

        <!-- Tarjeta 2 -->
        <Link
          href="/admin/reportes/ingresos"
          class="bg-white shadow-md hover:shadow-xl transition rounded-2xl p-6 border border-green-200 flex items-center gap-4 hover:bg-green-50"
        >
          <div class="p-4 rounded-full bg-green-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M12 8c-1.657 0-3 1.343-3 3h6c0-1.657-1.343-3-3-3zm0 0V5m0 6v3m0 3v3m9-7a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-green-700">Reporte de Facturación</h3>
            <p class="text-gray-600 text-sm">Ingresos generados en el hotel por reservas y servicios.</p>
          </div>
        </Link>

        <!-- Tarjeta 3 -->
        <Link
          href="/admin/reportes/clientes"
          class="bg-white shadow-md hover:shadow-xl transition rounded-2xl p-6 border border-green-200 flex items-center gap-4 hover:bg-green-50"
        >
          <div class="p-4 rounded-full bg-green-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5V4H2v16h5m10 0v-6a4 4 0 00-8 0v6m8 0H9" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-green-700">Reporte de Clientes</h3>
            <p class="text-gray-600 text-sm">Listado de clientes y su historial de reservas.</p>
          </div>
        </Link>

      </div>

    </div>
  </AdminLayout>
</template>
