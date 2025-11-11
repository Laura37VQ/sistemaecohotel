<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'

// Props recibidos desde el controlador
const props = defineProps({
  servicios: {
    type: Object,
    default: () => ({ data: [] })
  }
})

// Cambiar estado (activar/desactivar)
function toggleEstado(id, estadoActual) {
  const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo'
  const mensaje = estadoActual === 'Activo'
    ? '¿Deseas desactivar este servicio?'
    : '¿Deseas reactivar este servicio?'

  if (!confirm(mensaje)) return

  router.put(`/admin/servicios/${id}`, { estado: nuevoEstado }, {
    preserveScroll: true,
    onSuccess: () => alert(
      nuevoEstado === 'Activo' ? 'Servicio reactivado correctamente.' : 'Servicio desactivado correctamente.'
    ),
    onError: () => alert('Ocurrió un error al cambiar el estado.')
  })
}

// Formatear precios
function formatearPrecio(precio) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0
  }).format(precio)
}
</script>

<template>
  <Head title="Servicios" />

  <AdminLayout>
    <div class="p-6 bg-gray-50 min-h-screen">
      <!-- Título y botón -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-green-700">Servicios</h2>
        <a
          href="/admin/servicios/create"
          class="px-5 py-2 bg-green-700 text-white rounded-lg shadow hover:bg-green-800 transition flex items-center gap-2"
        >
          <span class="text-xl font-bold">+</span> Crear servicio
        </a>
      </div>

      <!-- Tabla con scroll -->
      <div class="overflow-x-auto rounded-xl shadow border border-gray-200 bg-white">
        <table class="min-w-[900px] w-full border-collapse text-base text-gray-800">
          <thead class="bg-green-100 uppercase text-gray-700 text-sm">
            <tr>
              <th class="px-4 py-3 border-b text-center w-[120px]">Foto</th>
              <th class="px-4 py-3 border-b text-left w-[160px]">Categoría</th>
              <th class="px-4 py-3 border-b text-left w-[180px]">Nombre</th>
              <th class="px-4 py-3 border-b text-left w-[30%]">Descripción</th>
              <th class="px-4 py-3 border-b text-left w-[120px]">Precio</th>
              <th class="px-4 py-3 border-b text-center w-[100px]">Estado</th>
              <th class="px-4 py-3 border-b text-center w-[180px]">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="s in servicios.data"
              :key="s.id"
              :class="[
                s.estado === 'Inactivo' ? 'bg-red-50 text-gray-600' : 'hover:bg-gray-50 transition',
                'border-b align-top'
              ]"
            >
              <!-- Foto -->
              <td class="px-4 py-3 text-center align-middle">
                <img
                  v-if="s.foto_url"
                  :src="s.foto_url"
                  alt="Foto del servicio"
                  class="w-20 h-20 object-cover rounded-lg mx-auto shadow-sm border"
                />
                <span v-else class="text-gray-400 italic">Sin foto</span>
              </td>

              <!-- Categoría -->
              <td class="px-4 py-3 align-middle font-medium">{{ s.categoria || 'Sin categoría' }}</td>

              <!-- Nombre -->
              <td class="px-4 py-3 align-middle font-semibold text-gray-900">
                {{ s.nombre }}
              </td>

              <!-- Descripción -->
              <td class="px-4 py-3 text-gray-700 leading-relaxed text-justify whitespace-pre-wrap break-words">
                {{ s.descripcion || 'Sin descripción.' }}
              </td>

              <!-- Precio -->
              <td class="px-4 py-3 align-middle">{{ formatearPrecio(s.precio) }}</td>

              <!-- Estado -->
              <td
                class="px-4 py-3 align-middle font-semibold text-center"
                :class="s.estado === 'Activo' ? 'text-green-600' : 'text-red-600'"
              >
                {{ s.estado }}
              </td>

              <!-- Acciones -->
              <td class="px-4 py-3 align-middle text-center">
                <div class="flex justify-center gap-2">
                  <a
                    :href="`/admin/servicios/${s.id}/edit`"
                    class="px-4 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm font-medium"
                  >
                    Editar
                  </a>

                  <button
                    @click="toggleEstado(s.id, s.estado)"
                    :class="s.estado === 'Activo'
                      ? 'bg-red-500 hover:bg-red-600'
                      : 'bg-green-600 hover:bg-green-700'"
                    class="px-4 py-1.5 text-white rounded-lg text-sm font-medium"
                  >
                    {{ s.estado === 'Activo' ? 'Desactivar' : 'Reactivar' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-between">
        <button
          v-if="servicios.prev_page_url"
          @click="router.get(servicios.prev_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          ← Anterior
        </button>

        <button
          v-if="servicios.next_page_url"
          @click="router.get(servicios.next_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          Siguiente →
        </button>
      </div>
    </div>
  </AdminLayout>
</template>
