<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
</script>

<template>
  <Head title="Reportes del Hotel" />
  <AdminLayout>
    <div class="p-8 bg-white rounded-2xl shadow max-w-3xl mx-auto">
      <h2 class="text-3xl font-bold mb-6 text-green-700"> Panel de Reportes</h2>
      <ul class="space-y-4">
        <li>
          <Link
            href="/admin/reportes/ocupacion"
            class="block bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition text-lg"
          >
             Reporte de Ocupación de Habitaciones
          </Link>
        </li>
        <li>
          <Link
            href="/admin/reportes/ingresos"
            class="block bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition text-lg"
          >
             Reporte de Facturación del Hotel
          </Link>
        </li>
        <li>
          <Link
            href="/admin/reportes/clientes"
            class="block bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition text-lg"
          >
             Reporte de Clientes y Reservas
          </Link>
        </li>
      </ul>
    </div>
  </AdminLayout>
</template>
