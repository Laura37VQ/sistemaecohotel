<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'

// Props
const props = defineProps({
  habitaciones: {
    type: Object,
    default: () => ({ data: [] })
  }
})

// Función para desactivar habitación
function desactivar(id) {
  if (!confirm('¿Desactivar esta habitación?')) return;

  router.put(`/admin/habitaciones/${id}`, { estado: 'Mantenimiento' }, {
    preserveScroll: true,
    onSuccess: () => {
      alert('Habitación desactivada correctamente')
    }
  })
}
</script>

<template>
  <Head title="Habitaciones" />

  <AdminLayout>
    <div class="p-6">
      <!-- Encabezado -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Habitaciones</h2>
        <a href="/admin/habitaciones/create"
           class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2">
          <span class="text-xl font-bold">+</span> Crear habitación
        </a>
      </div>

      <!-- Tabla de habitaciones -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-lg overflow-hidden">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Número</th>
              <th class="px-4 py-3">Tipo</th>
              <th class="px-4 py-3">Capacidad</th>
              <th class="px-4 py-3">Precio</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Descripción</th>
              <th class="px-4 py-3">Foto</th>
              <th class="px-4 py-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- Mensaje si no hay habitaciones -->
            <tr v-if="!habitaciones.data || !habitaciones.data.length">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                No hay habitaciones registradas.
              </td>
            </tr>

            <!-- Filas de habitaciones -->
            <tr v-else v-for="h in habitaciones.data" :key="h.id"
                class="border-t hover:bg-gray-50 transition duration-200">
              
              <td class="px-4 py-3 font-medium">#{{ h.numero_habitacion }}</td>
              <td class="px-4 py-3">{{ h.tipo }}</td>
              <td class="px-4 py-3">{{ h.capacidad_personas }}</td>
              <td class="px-4 py-3">${{ h.precio }}</td>

              <!-- Estado -->
              <td class="px-4 py-3">
                <span
                  :class="{
                    'px-3 py-1 rounded-full text-white text-sm font-semibold': true,
                    'bg-green-500': h.estado === 'Disponible',
                    'bg-yellow-500': h.estado === 'Ocupada',
                    'bg-red-500': h.estado === 'Mantenimiento'
                  }"
                >
                  {{ h.estado || 'Disponible' }}
                </span>
              </td>

              <td class="px-4 py-3">{{ h.descripcion }}</td>

              <!-- Foto -->
              <td class="px-4 py-3">
                <img
                  v-if="h.foto"
                  :src="`/storage/${h.foto}`"
                  alt="Foto habitación"
                  class="w-16 h-16 object-cover rounded-lg shadow-sm"
                >
                <span v-else class="text-gray-400 italic">Sin foto</span>
              </td>

              <!-- Acciones con íconos -->
              <td class="px-4 py-3 flex gap-2">
                <a :href="`/admin/habitaciones/${h.id}/edit`"
                   class="flex items-center gap-1 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition text-sm font-medium">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536M9 13l6-6 3 3-6 6H9v-3z"/>
                  </svg>
                  Editar
                </a>
                <button @click="desactivar(h.id)"
                        class="flex items-center gap-1 px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm font-medium">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4"/>
                  </svg>
                  Desactivar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-between">
        <button v-if="habitaciones.prev_page_url"
                @click="router.get(habitaciones.prev_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100 transition">
          ← Anterior
        </button>
        <button v-if="habitaciones.next_page_url"
                @click="router.get(habitaciones.next_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100 transition">
          Siguiente →
        </button>
      </div>
    </div>
  </AdminLayout>
</template>
