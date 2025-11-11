<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  categorias: {
    type: Object,
    default: () => ({ data: [] })
  }
})

// Cambiar estado (Activo/Inactivo)
function toggleEstado(id, estadoActual) {
  const nuevo = estadoActual === 'Activo' ? 'Inactivo' : 'Activo'
  if (!confirm(`¿Desea cambiar el estado a ${nuevo}?`)) return

  router.put(`/admin/categorias-servicios/${id}`, { estado: nuevo }, {
    preserveScroll: true,
    onSuccess: () => router.reload()
  })
}
</script>

<template>
  <Head title="Categorías de Servicios" />

  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Categorías de Servicios</h2>
        <a
          href="/admin/categorias-servicios/create"
          class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2"
        >
          <span class="text-xl font-bold">+</span> Crear Categoría
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-lg overflow-hidden">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Nombre</th>
              <th class="px-4 py-3">Descripción</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!categorias.data.length">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">
                No hay categorías registradas.
              </td>
            </tr>

            <tr v-for="cat in categorias.data" :key="cat.id" class="border-t hover:bg-gray-50 transition duration-200">
              <td class="px-4 py-3 font-medium">{{ cat.nombre_categoria }}</td>
              <td class="px-4 py-3">{{ cat.descripcion || 'Sin descripción' }}</td>
              <td class="px-4 py-3">
                <span
                  :class="{
                    'px-3 py-1 rounded-full text-white text-sm font-semibold': true,
                    'bg-green-500': cat.estado === 'Activo',
                    'bg-red-500': cat.estado === 'Inactivo'
                  }"
                >
                  {{ cat.estado }}
                </span>
              </td>
              <td class="px-4 py-3 flex gap-2">
                <!-- Editar -->
                <a
                  :href="`/admin/categorias-servicios/${cat.id}/edit`"
                  class="flex items-center gap-1 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition text-sm font-medium"
                >
                  Editar
                </a>

                <!-- Activar/Inactivar -->
                <button
                  @click="toggleEstado(cat.id, cat.estado)"
                  class="flex items-center gap-1 px-3 py-1 rounded text-white text-sm font-medium transition"
                  :class="cat.estado === 'Activo' ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'"
                >
                  {{ cat.estado === 'Activo' ? 'Inactivar' : 'Activar' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-between">
        <button
          :disabled="!categorias.prev_page_url"
          @click="router.get(categorias.prev_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          ← Anterior
        </button>

        <button
          :disabled="!categorias.next_page_url"
          @click="router.get(categorias.next_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Siguiente →
        </button>
      </div>
    </div>
  </AdminLayout>
</template>
