<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  facturas: Object,
  clientes: Array,
  filtros: Object
})

// Filtros reactivos
const buscar = ref(props.filtros.buscar || '')
const estado = ref(props.filtros.estado || '')
const cliente = ref(props.filtros.cliente || '')
const fecha_inicio = ref(props.filtros.fecha_inicio || '')
const fecha_fin = ref(props.filtros.fecha_fin || '')

/**
 * Aplica los filtros enviando parámetros por GET a Laravel.
 */
function filtrar() {
  router.get('/recepcionista/facturas', {
    buscar: buscar.value,
    estado: estado.value,
    cliente: cliente.value,
    fecha_inicio: fecha_inicio.value,
    fecha_fin: fecha_fin.value
  }, { preserveState: true, preserveScroll: true })
}

/**
 * Restablece todos los filtros.
 */
function limpiar() {
  buscar.value = ''
  estado.value = ''
  cliente.value = ''
  fecha_inicio.value = ''
  fecha_fin.value = ''
  filtrar()
}

/**
 * Marca una factura como pagada.
 */
function marcarPagada(id) {
  if (confirm('¿Marcar esta factura como pagada?')) {
    router.post(`/recepcionista/facturas/${id}/pagar`)
  }
}
</script>

<template>
  <Head title="Facturación" />
  <RecepcionistaLayout>
    <div class="p-6">

      <!-- Encabezado -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Facturación</h2>
        <Link href="/recepcionista/facturas/create"
              class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition">
          + Nueva Factura
        </Link>
      </div>

      <!-- Filtros -->
      <div class="bg-white p-4 rounded-lg shadow mb-4 grid grid-cols-1 md:grid-cols-5 gap-4">

        <input v-model="buscar" type="text" placeholder="Buscar factura o cliente"
               class="border rounded px-3 py-2 w-full" />

        <select v-model="estado" class="border rounded px-3 py-2">
          <option value="">Estado</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Pagada">Pagada</option>
          <option value="Anulada">Anulada</option>
        </select>

        <select v-model="cliente" class="border rounded px-3 py-2">
          <option value="">Cliente</option>
          <option v-for="c in clientes" :key="c.id" :value="c.id">
            {{ c.nombres }} {{ c.apellidos }}
          </option>
        </select>

        <input v-model="fecha_inicio" type="date" class="border rounded px-3 py-2" />
        <input v-model="fecha_fin" type="date" class="border rounded px-3 py-2" />

        <div class="flex gap-2 col-span-1 md:col-span-5">
          <button @click="filtrar" class="px-4 py-2 bg-green-600 text-white rounded">Buscar</button>
          <button @click="limpiar" class="px-4 py-2 bg-gray-500 text-white rounded">Limpiar</button>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Factura</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Fecha emisión</th>
              <th class="px-4 py-3 text-right">Total</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="f in facturas.data" :key="f.id" class="border-t hover:bg-gray-50">

              <td class="px-4 py-3 font-semibold">{{ f.prefijo }}-{{ f.numero_factura }}</td>

              <td class="px-4 py-3">
                {{ f.cliente?.apellidos }}, {{ f.cliente?.nombres }}
              </td>

              <td class="px-4 py-3">
                {{ new Date(f.fecha_emision).toLocaleDateString() }}
              </td>

              <td class="px-4 py-3 text-right">
                ${{ f.total?.toLocaleString() }}
              </td>

              <td class="px-4 py-3 text-center">
                <span :class="{
                    'px-3 py-1 rounded-full text-white text-sm': true,
                    'bg-yellow-500': f.estado === 'Pendiente',
                    'bg-green-600': f.estado === 'Pagada',
                    'bg-red-600': f.estado === 'Anulada'
                }">
                  {{ f.estado }}
                </span>
              </td>

              <td class="px-4 py-3 text-center flex justify-center gap-3">
                <Link :href="`/recepcionista/facturas/${f.id}`" class="text-blue-600 hover:underline">
                  Ver
                </Link>

                <a :href="`/recepcionista/facturas/${f.id}/pdf`" target="_blank"
                   class="text-green-600 hover:underline">
                  Descargar
                </a>

                <button v-if="f.estado === 'Pendiente'"
                        @click="marcarPagada(f.id)"
                        class="text-yellow-700 hover:underline">
                  Marcar pagada
                </button>
              </td>

            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="facturas.links.length > 3" class="flex justify-center gap-2 mt-6">
        <template v-for="link in facturas.links">
          <Link v-if="link.url"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-2 border rounded hover:bg-green-100"
                :class="{ 'bg-green-600 text-white': link.active }" />
        </template>
      </div>

    </div>
  </RecepcionistaLayout>
</template>
