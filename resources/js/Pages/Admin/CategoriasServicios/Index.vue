<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Props desde Laravel
const props = defineProps({
  categorias: Object,
  filtros: Object
})

// Filtros reactivos
const q = ref(props.filtros.q || '')
const estado = ref(props.filtros.estado || '')

// Función buscar
function buscar() {
  router.get('/admin/categorias-servicios', {
    q: q.value,
    estado: estado.value
  }, { preserveState: true })
}

// Limpiar filtros
function limpiar() {
  q.value = ''
  estado.value = ''
  buscar()
}

/*
  Cambiar estado de categoría
  → Ahora llama correctamente a:
    PATCH /admin/categorias-servicios/{id}/toggle
*/
function toggleEstado(id) {
  if (!confirm("¿Desea cambiar el estado de esta categoría?")) return;

  router.patch(`/admin/categorias-servicios/${id}/toggle`, {}, {
    onSuccess: () => {
      // Recargar lista completa
      router.get('/admin/categorias-servicios', {}, {
        preserveState: true,
        preserveScroll: true
      })
    }
  })
}

</script>

<template>
  <Head title="Categorías de Servicios" />

  <AdminLayout>
    <div class="p-6">

      <!-- Título -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-green-700">Categorías de Servicios</h2>

        <a href="/admin/categorias-servicios/create"
           class="px-5 py-2 bg-green-700 text-white rounded-lg shadow hover:bg-green-800">
          + Crear Categoría
        </a>
      </div>

      <!-- Filtros -->
      <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4 flex-wrap">

        <input 
          v-model="q" 
          type="text" 
          placeholder="Buscar..." 
          class="border px-3 py-2 rounded w-60" 
        />

        <select v-model="estado" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los estados</option>
          <option value="Activo">Activos</option>
          <option value="Inactivo">Inactivos</option>
        </select>

        <button @click="buscar"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Buscar
        </button>

        <button @click="limpiar"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
          Limpiar
        </button>

      </div>

      <!-- TABLA -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-lg overflow-hidden">
          
          <thead class="bg-green-50 text-left uppercase">
            <tr>
              <th class="px-4 py-3">Nombre</th>
              <th class="px-4 py-3">Descripción</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!props.categorias.data.length">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">
                No hay categorías registradas.
              </td>
            </tr>

            <tr v-for="cat in props.categorias.data" :key="cat.id" 
                class="border-t hover:bg-gray-50">

              <td class="px-4 py-3">{{ cat.nombre_categoria }}</td>
              <td class="px-4 py-3">{{ cat.descripcion || 'Sin descripción' }}</td>

              <td class="px-4 py-3">
                <span :class="{
                  'px-3 py-1 rounded-full text-white text-sm font-semibold': true,
                  'bg-green-500': cat.estado === 'Activo',
                  'bg-red-500': cat.estado === 'Inactivo'
                }">
                  {{ cat.estado }}
                </span>
              </td>

              <td class="px-4 py-3 flex gap-2">

                <!-- Editar -->
                <a :href="`/admin/categorias-servicios/${cat.id}/edit`"
                   class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                  Editar
                </a>

                <!-- Activar / Inactivar -->
                <button 
                  @click="toggleEstado(cat.id)"
                  :class="cat.estado === 'Activo'
                          ? 'bg-red-500 hover:bg-red-600'
                          : 'bg-green-500 hover:bg-green-600'"
                  class="px-3 py-1 text-white rounded text-sm"
                >
                  {{ cat.estado === 'Activo' ? 'Inactivar' : 'Activar' }}
                </button>

              </td>

            </tr>
          </tbody>

        </table>
      </div>

      <!-- PAGINACIÓN -->
      <div class="mt-6 flex justify-between">
        <button 
          :disabled="!props.categorias.prev_page_url"
          @click="router.get(props.categorias.prev_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 disabled:opacity-50">
          ← Anterior
        </button>

        <button 
          :disabled="!props.categorias.next_page_url"
          @click="router.get(props.categorias.next_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 disabled:opacity-50">
          Siguiente →
        </button>
      </div>

    </div>
  </AdminLayout>
</template>
