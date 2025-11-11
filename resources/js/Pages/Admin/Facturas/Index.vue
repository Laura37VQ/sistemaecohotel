<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
  facturas: {
    type: Object,
    default: () => ({ data: [], links: [] })
  }
})

//  Anular factura (no se elimina)
function anularFactura(id) {
  if (confirm('¿Seguro que desea anular esta factura? Esta acción no se puede revertir.')) {
    router.put(`/admin/facturas/${id}/anular`, {}, { preserveScroll: true })
  }
}
</script>

<template>
  <Head title="Facturas" />

  <AdminLayout>
    <div class="p-6">
      <!--  ENCABEZADO -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Gestión de Facturas</h2>

        <Link
          href="/admin/facturas/create"
          class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2"
        >
          + Nueva Factura
        </Link>
      </div>

      <!--  SIN FACTURAS -->
      <div v-if="!facturas.data.length" class="text-center py-20 text-gray-500 italic">
        No hay facturas registradas.
      </div>

      <!--  TABLA -->
      <div v-else class="overflow-x-auto bg-white rounded-lg shadow-lg">
        <table class="min-w-full border-collapse">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">N° Factura</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Fecha Emisión</th>
              <th class="px-4 py-3 text-right">Total</th>
              <th class="px-4 py-3 text-center">Método Pago</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
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
                ${{ f.total?.toLocaleString() || '0.00' }}
              </td>
              <td class="px-4 py-3 text-center">{{ f.metodo_pago }}</td>
              <td class="px-4 py-3 text-center">
                <span
                  :class="{
                    'px-3 py-1 rounded-full text-white text-sm font-semibold': true,
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
                  :href="`/admin/facturas/${f.id}`"
                  class="text-blue-600 hover:underline"
                >
                  Ver
                </Link>

                <Link
                  :href="`/admin/facturas/${f.id}/edit`"
                  class="text-yellow-600 hover:underline"
                  v-if="f.estado !== 'Anulada'"
                >
                  Editar
                </Link>

                <button
                  v-if="f.estado !== 'Anulada'"
                  @click.prevent="anularFactura(f.id)"
                  class="text-red-600 hover:underline"
                >
                  Anular
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!--  Paginación -->
        <div
          v-if="facturas.links.length > 3"
          class="flex justify-center items-center gap-2 mt-6 pb-6"
        >
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
            <span
              v-else
              v-html="link.label"
              class="px-4 py-2 text-gray-400 cursor-not-allowed"
            />
          </template>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
