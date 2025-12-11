<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Props desde backend
const props = defineProps({
  reservas: Object,
  filtros: Object
})

// Filtros reactivos
const q      = ref(props.filtros.q || '')
const estado = ref(props.filtros.estado || '')
const desde  = ref(props.filtros.desde || '')
const hasta  = ref(props.filtros.hasta || '')

// Buscar
function buscar() {
  router.get('/admin/reservas', {
    q: q.value,
    estado: estado.value,
    desde: desde.value,
    hasta: hasta.value,
  }, { preserveState: true })
}

// Limpiar filtros
function limpiar() {
  q.value = ''
  estado.value = ''
  desde.value = ''
  hasta.value = ''
  buscar()
}

// Cancelar reserva
function cancelar(id) {
  if (!confirm('¿Cancelar esta reserva?')) return
  router.put(`/admin/reservas/${id}`, { estado: 'Cancelada' })
}
</script>

<template>
  <Head title="Reservas" />

  <AdminLayout>
    <div class="p-6">

      <!-- ENCABEZADO -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-green-700">Reservas</h2>

        <a href="/admin/reservas/create"
           class="px-5 py-2 bg-green-700 text-white rounded-lg shadow hover:bg-green-800">
          + Crear reserva
        </a>
      </div>

      <!-- FILTROS -->
      <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4 flex-wrap">

        <input v-model="q" type="text" placeholder="Buscar cliente / código / habitación"
               class="border px-3 py-2 rounded w-72" />

        <select v-model="estado" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los estados</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Confirmada">Confirmada</option>
          <option value="Completada">Completada</option>
          <option value="Cancelada">Cancelada</option>
        </select>

        <input v-model="desde" type="date" class="border px-3 py-2 rounded" />
        <input v-model="hasta" type="date" class="border px-3 py-2 rounded" />

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
          <thead class="bg-green-50 text-left uppercase text-gray-700">
            <tr>
              <th class="px-4 py-3">Código</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Habitación</th>
              <th class="px-4 py-3">Ingreso</th>
              <th class="px-4 py-3">Salida</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!props.reservas.data.length">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                No hay reservas registradas.
              </td>
            </tr>

            <tr v-for="r in props.reservas.data" :key="r.id"
                class="border-t hover:bg-gray-50">
              
              <td class="px-4 py-3">#{{ r.codigo_reserva }}</td>

              <td class="px-4 py-3">
                {{ r.usuario ? `${r.usuario.nombres} ${r.usuario.apellidos}` : '—' }}
              </td>

              <td class="px-4 py-3">
                {{ r.habitacion ? `${r.habitacion.numero_habitacion} - ${r.habitacion.tipo}` : '—' }}
              </td>

              <td class="px-4 py-3">{{ r.fecha_ingreso }}</td>
              <td class="px-4 py-3">{{ r.fecha_salida }}</td>

              <td class="px-4 py-3">
                <span :class="{
                  'px-3 py-1 rounded-full text-white text-sm': true,
                  'bg-yellow-500': r.estado === 'Pendiente',
                  'bg-green-600': r.estado === 'Confirmada' || r.estado === 'Completada',
                  'bg-red-500': r.estado === 'Cancelada'
                }">
                  {{ r.estado }}
                </span>
              </td>

              <td class="px-4 py-3 flex gap-2">
                <a :href="`/admin/reservas/${r.id}/edit`"
                   class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                  Editar
                </a>

                <button @click="cancelar(r.id)"
                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                  Cancelar
                </button>
              </td>

            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINACIÓN -->
      <div class="mt-6 flex justify-between">
        <button v-if="props.reservas.prev_page_url"
                @click="router.get(props.reservas.prev_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100">
          ← Anterior
        </button>

        <button v-if="props.reservas.next_page_url"
                @click="router.get(props.reservas.next_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100">
          Siguiente →
        </button>
      </div>

    </div>
  </AdminLayout>
</template>
