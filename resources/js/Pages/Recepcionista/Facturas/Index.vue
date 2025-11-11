<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
  facturas: {
    type: Object,
    default: () => ({ data: [], links: [] })
  }
})

//  Marcar factura como pagada
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
        <Link
          href="/recepcionista/facturas/create"
          class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition"
        >
          + Nueva Factura
        </Link>
      </div>

      <!-- Si no hay facturas -->
      <div v-if="!facturas.data.length" class="text-center py-20 text-gray-500 italic">
        No hay facturas registradas aún.
      </div>

      <!-- Tabla de facturas -->
      <div v-else class="overflow-x-auto bg-white rounded-lg shadow">
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
            <tr
              v-for="f in facturas.data"
              :key="f.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 font-semibold">
                {{ f.prefijo }}-{{ f.numero_factura }}
              </td>
              <td class="px-4 py-3">{{ f.cliente?.apellidos }}, {{ f.cliente?.nombres }}</td>
              <td class="px-4 py-3">{{ new Date(f.fecha_emision).toLocaleDateString() }}</td>
              <td class="px-4 py-3 text-right">${{ f.total?.toLocaleString() || '0.00' }}</td>
              <td class="px-4 py-3 text-center">
                <span
                  :class="{
                    'px-3 py-1 rounded-full text-white text-sm': true,
                    'bg-yellow-500': f.estado === 'Pendiente',
                    'bg-green-600': f.estado === 'Pagada',
                    'bg-red-600': f.estado === 'Anulada'
                  }"
                >
                  {{ f.estado }}
                </span>
              </td>

              <!--  ACCIONES -->
              <td class="px-4 py-3 text-center flex justify-center gap-3">
                <Link
                  :href="`/recepcionista/facturas/${f.id}`"
                  class="text-blue-600 hover:underline font-medium"
                >
                  Ver
                </Link>

                <a
                  :href="`/recepcionista/facturas/${f.id}/pdf`"
                  class="text-green-600 hover:underline font-medium"
                  target="_blank"
                >
                  Descargar
                </a>

                <button
                  v-if="f.estado === 'Pendiente'"
                  @click="marcarPagada(f.id)"
                  class="text-yellow-700 hover:underline font-medium"
                >
                  Marcar pagada
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="facturas.links.length > 3" class="flex justify-center items-center gap-2 mt-6">
        <template v-for="(link, index) in facturas.links" :key="index">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            class="px-4 py-2 border rounded-lg hover:bg-green-100 transition"
            :class="{
              'bg-green-600 text-white border-green-600': link.active,
              'text-gray-600': !link.active
            }"
          />
          <span v-else v-html="link.label" class="px-4 py-2 text-gray-400 cursor-not-allowed" />
        </template>
      </div>
    </div>
  </RecepcionistaLayout>
</template>
