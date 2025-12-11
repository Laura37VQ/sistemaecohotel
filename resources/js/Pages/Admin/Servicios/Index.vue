<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Props desde backend
const props = defineProps({
  servicios: Object,
  categorias: Array,
  filtros: Object
})

// Filtros reactivos
const q         = ref(props.filtros.q || '')
const categoria = ref(props.filtros.categoria || '')
const estado    = ref(props.filtros.estado || '')
const precioMin = ref(props.filtros.precio_min || '')
const precioMax = ref(props.filtros.precio_max || '')

// Buscar
function buscar() {
  router.get('/admin/servicios', {
    q: q.value,
    categoria: categoria.value,
    estado: estado.value,
    precio_min: precioMin.value,
    precio_max: precioMax.value
  }, { preserveState: true })
}

// Limpiar filtros
function limpiar() {
  q.value = ''
  categoria.value = ''
  estado.value = ''
  precioMin.value = ''
  precioMax.value = ''
  buscar()
}

// Cambiar estado
function toggleEstado(id, estadoActual) {
  const nuevo = estadoActual === 'Activo' ? 'Inactivo' : 'Activo'
  if (!confirm(`¿Deseas cambiar el estado a ${nuevo}?`)) return

  router.put(`/admin/servicios/${id}`, { estado: nuevo }, { preserveScroll: true })
}

// Formatear precio
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
    <div class="p-6 bg-gray-50">

      <!-- Título -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-green-700">Servicios</h2>
        <a href="/admin/servicios/create"
           class="px-5 py-2 bg-green-700 text-white rounded-lg shadow hover:bg-green-800 transition">
          + Crear servicio
        </a>
      </div>

      <!-- FILTROS -->
      <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4 flex-wrap">

        <input v-model="q" type="text" placeholder="Buscar servicio..."
               class="border px-3 py-2 rounded w-60" />

        <select v-model="categoria" class="border px-3 py-2 rounded w-48">
          <option value="">Todas las categorías</option>
          <option v-for="c in categorias" :value="c.id" :key="c.id">
            {{ c.nombre_categoria }}
          </option>
        </select>

        <select v-model="estado" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los estados</option>
          <option value="Activo">Activos</option>
          <option value="Inactivo">Inactivos</option>
        </select>

        <input v-model="precioMin" type="number" placeholder="Precio mínimo"
               class="border px-3 py-2 rounded w-40" />

        <input v-model="precioMax" type="number" placeholder="Precio máximo"
               class="border px-3 py-2 rounded w-40" />

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
      <div class="overflow-x-auto rounded-xl shadow bg-white border">
        <table class="min-w-[900px] w-full text-sm text-gray-700">
          <thead class="bg-green-100 uppercase">
            <tr>
              <th class="px-4 py-3 text-center">Foto</th>
              <th class="px-4 py-3">Categoría</th>
              <th class="px-4 py-3">Nombre</th>
              <th class="px-4 py-3 w-[30%]">Descripción</th>
              <th class="px-4 py-3">Precio</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="s in servicios.data" :key="s.id"
                :class="[
                  s.estado === 'Inactivo' ? 'bg-red-50 text-gray-600' : 'hover:bg-gray-50',
                  'border-b'
                ]">

              <!-- FOTO -->
              <td class="px-4 py-3 text-center">
                <img v-if="s.foto_url" :src="s.foto_url"
                     class="w-20 h-20 object-cover rounded-lg mx-auto" />
                <span v-else class="text-gray-400 italic">Sin foto</span>
              </td>

              <!-- CATEGORÍA -->
              <td class="px-4 py-3 font-medium">{{ s.categoria }}</td>

              <!-- NOMBRE -->
              <td class="px-4 py-3 font-semibold">{{ s.nombre }}</td>

              <!-- DESCRIPCIÓN -->
              <td class="px-4 py-3 whitespace-pre-wrap break-words">
                {{ s.descripcion || 'Sin descripción.' }}
              </td>

              <!-- PRECIO -->
              <td class="px-4 py-3">{{ formatearPrecio(s.precio) }}</td>

              <!-- ESTADO -->
              <td class="px-4 py-3 text-center"
                  :class="s.estado === 'Activo' ? 'text-green-600' : 'text-red-600'">
                {{ s.estado }}
              </td>

              <!-- ACCIONES -->
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">

                  <a :href="`/admin/servicios/${s.id}/edit`"
                     class="px-4 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm">
                    Editar
                  </a>

                  <button @click="toggleEstado(s.id, s.estado)"
                          :class="s.estado === 'Activo'
                            ? 'bg-red-500 hover:bg-red-600'
                            : 'bg-green-600 hover:bg-green-700'"
                          class="px-4 py-1.5 text-white rounded-lg text-sm">
                    {{ s.estado === 'Activo' ? 'Desactivar' : 'Reactivar' }}
                  </button>

                </div>
              </td>

            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINACIÓN -->
      <div class="mt-6 flex justify-between">
        <button v-if="servicios.prev_page_url"
                @click="router.get(servicios.prev_page_url)"
                class="px-4 py-2 border rounded">
          ← Anterior
        </button>

        <button v-if="servicios.next_page_url"
                @click="router.get(servicios.next_page_url)"
                class="px-4 py-2 border rounded">
          Siguiente →
        </button>
      </div>

    </div>
  </AdminLayout>
</template>
