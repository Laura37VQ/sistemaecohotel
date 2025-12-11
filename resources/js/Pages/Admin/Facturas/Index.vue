<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Props desde Laravel
const props = defineProps({
  facturas: Object,
  filtros: Object
})

// Filtros reactivos
const q      = ref(props.filtros.q || '')
const estado = ref(props.filtros.estado || '')
const metodo = ref(props.filtros.metodo || '')
const desde  = ref(props.filtros.desde || '')
const hasta  = ref(props.filtros.hasta || '')

// Buscar
function buscar() {
  router.get('/admin/facturas', {
    q: q.value,
    estado: estado.value,
    metodo: metodo.value,
    desde: desde.value,
    hasta: hasta.value
  }, { preserveState: true })
}

// Limpiar filtros
function limpiar() {
  q.value = ''
  estado.value = ''
  metodo.value = ''
  desde.value = ''
  hasta.value = ''
  buscar()
}

// Anular factura
function anularFactura(id) {
  if (confirm('¿Seguro que desea anular esta factura?')) {
    router.put(`/admin/facturas/${id}/anular`)
  }
}
</script>

<template>
  <Head title="Facturas" />

  <AdminLayout>
    <div class="p-6">

      <!-- Título -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-green-700">Gestión de Facturas</h2>

        <Link
          href="/admin/facturas/create"
          class="px-5 py-2 bg-green-700 text-white rounded-lg shadow hover:bg-green-800"
        >
          + Nueva Factura
        </Link>
      </div>

      <!-- FILTROS -->
      <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4 flex-wrap">

        <input
          v-model="q"
          type="text"
          placeholder="Buscar número o cliente..."
          class="border px-3 py-2 rounded w-60"
        />

        <select v-model="estado" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los estados</option>
          <option value="Pendiente">Pendientes</option>
          <option value="Pagada">Pagadas</option>
          <option value="Anulada">Anuladas</option>
        </select>

        <select v-model="metodo" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los métodos</option>
          <option value="Efectivo">Efectivo</option>
          <option value="Tarjeta">Tarjeta</option>
          <option value="Transferencia">Transferencia</option>
          <option value="Pendiente">Pendiente</option>
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
      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
          <thead class="bg-green-50">
            <tr>
              <th class="px-4 py-3">N° Factura</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Fecha</th>
              <th class="px-4 py-3 text-right">Total</th>
              <th class="px-4 py-3 text-center">Método</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!facturas.data.length">
              <td colspan="7" class="text-center py-6 text-gray-500 italic">
                No hay facturas registradas.
              </td>
            </tr>

            <tr
              v-for="f in facturas.data"
              :key="f.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="px-4 py-3 font-medium">
                {{ f.prefijo }}-{{ f.numero_factura }}
              </td>
              <td class="px-4 py-3">
                {{ f.cliente?.nombres }} {{ f.cliente?.apellidos }}
              </td>
              <td class="px-4 py-3">
                {{ new Date(f.fecha_emision).toLocaleDateString() }}
              </td>
              <td class="px-4 py-3 text-right">
                ${{ f.total?.toLocaleString() }}
              </td>

              <td class="px-4 py-3 text-center">{{ f.metodo_pago }}</td>

              <td class="px-4 py-3 text-center">
                <span
                  class="px-3 py-1 rounded-full text-white text-sm font-semibold"
                  :class="{
                    'bg-yellow-500': f.estado === 'Pendiente',
                    'bg-green-600': f.estado === 'Pagada',
                    'bg-red-600': f.estado === 'Anulada'
                  }"
                >
                  {{ f.estado }}
                </span>
              </td>

              <td class="px-4 py-3 text-center flex justify-center gap-3">

                <Link
                  :href="`/admin/facturas/${f.id}`"
                  class="text-blue-600 hover:underline"
                >
                  Ver
                </Link>

                <Link
                  v-if="f.estado !== 'Anulada'"
                  :href="`/admin/facturas/${f.id}/edit`"
                  class="text-yellow-600 hover:underline"
                >
                  Editar
                </Link>

                <button
                  v-if="f.estado !== 'Anulada'"
                  class="text-red-600 hover:underline"
                  @click="anularFactura(f.id)"
                >
                  Anular
                </button>

              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINACIÓN -->
      <div class="mt-6 flex justify-between">
        <button v-if="facturas.prev_page_url"
                @click="router.get(facturas.prev_page_url)"
                class="px-4 py-2 border rounded">
          ← Anterior
        </button>

        <button v-if="facturas.next_page_url"
                @click="router.get(facturas.next_page_url)"
                class="px-4 py-2 border rounded">
          Siguiente →
        </button>
      </div>

    </div>
  </AdminLayout>
</template>
