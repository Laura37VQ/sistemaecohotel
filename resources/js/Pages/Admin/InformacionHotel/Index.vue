<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

const props = defineProps({
  info: Object
})
</script>

<template>
  <Head title="Información del Hotel" />
  <AdminLayout>
    <div class="p-6 bg-white rounded-lg shadow">
      <h2 class="text-2xl font-bold mb-4 text-green-700">Información del Hotel</h2>

      <div v-if="info" class="space-y-4">
        <!-- Logo -->
        <div v-if="info.logo" class="flex justify-center mb-4">
          <img :src="`/storage/${info.logo}`" alt="Logo del Hotel" class="h-24 object-contain rounded" />
        </div>

        <!-- Datos -->
        <div class="space-y-2">
          <p><strong>Nombre:</strong> {{ info.nombre }}</p>
          <p><strong>NIT:</strong> {{ info.nit }}</p>
          <p><strong>Régimen Tributario:</strong> {{ info.regimen_tributario }}</p>
          <p><strong>Dirección:</strong> {{ info.direccion }}</p>
          <p><strong>Teléfono:</strong> {{ info.telefono }}</p>
          <p><strong>Email:</strong> {{ info.email }}</p>
          <p><strong>Ciudad:</strong> {{ info.ciudad }}</p>
          <p><strong>País:</strong> {{ info.pais }}</p>
          <p><strong>Actividad Económica:</strong> {{ info.actividad_economica }}</p>
          <p><strong>Misión:</strong> {{ info.mision }}</p>
          <p><strong>Misión:</strong> {{ info.mision }}</p>
        </div>

        <div class="mt-6">
          <Link href="/admin/informacion-hotel/edit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Editar Información
          </Link>
        </div>
      </div>

      <div v-else class="text-center">
        <p>No hay información registrada todavía.</p>
        <Link href="/admin/informacion-hotel/edit" class="bg-green-600 text-white px-4 py-2 rounded mt-4 inline-block hover:bg-green-700">
          Registrar Información
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>
