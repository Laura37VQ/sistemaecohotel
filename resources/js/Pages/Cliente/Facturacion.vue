<script setup>
import ClienteLayout from '@/layouts/ClienteLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import {
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  FileDownIcon,
  ReceiptTextIcon
} from 'lucide-vue-next'

const props = defineProps({
  facturas: Array
})
</script>

<template>
  <Head title="Facturación" />
  <ClienteLayout>
    <div class="min-h-screen bg-[#E8EFEB] py-12">
      <div class="max-w-6xl mx-auto bg-white rounded-3xl shadow-lg px-8 py-10">

        <!--  Encabezado -->
        <div class="text-center mb-10">
          <h2 class="text-4xl font-extrabold text-[#2E7D32] tracking-tight">
            Mis Facturas
          </h2>
          <p class="text-sm text-gray-500 mt-3">
            Desde esta sección puedes consultar el estado de tus facturas y descargar los comprobantes de pago disponibles.
          </p>

          <p class="text-gray-600 mt-2 text-base">
            Consulta y descarga tus comprobantes de pago del
            <b>Eco Hotel Villa del Sol</b>.
          </p>
          <div class="h-1 w-24 bg-[#FFCE3E] mx-auto mt-4 rounded-full"></div>
        </div>

        <!--  Botón volver -->
        <div class="mb-6">
          <Link
            href="/dashboard/cliente"
            class="inline-flex items-center gap-2 bg-[#2E7D32] text-white px-4 py-2 rounded-lg hover:bg-green-800 transition-all shadow-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver al panel
          </Link>
        </div>

        <!--  Tabla de facturas -->
        <div
          v-if="facturas.length"
          class="overflow-x-auto bg-white rounded-2xl shadow border border-gray-200"
        >
          <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-[#A5D6A7] text-[#1B5E20] uppercase text-xs font-semibold">
              <tr>
                <th class="p-3 text-left">Factura</th>
                <th class="p-3 text-left">Habitación</th>
                <th class="p-3 text-left">Fecha emisión</th>
                <th class="p-3 text-left">Estado</th>
                <th class="p-3 text-right">Total</th>
                <th class="p-3 text-center">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="f in facturas"
                :key="f.id"
                class="border-t hover:bg-[#F1F8E9] transition"
              >
                <td class="p-3 font-semibold text-gray-800">
                  {{ f.prefijo }}{{ f.numero_factura }}
                </td>
                <td class="p-3">{{ f.reserva?.habitacion?.tipo ?? '—' }}</td>
                <td class="p-3">{{ new Date(f.fecha_emision).toLocaleDateString() }}</td>

                <!-- Estado con ícono -->
                <td class="p-3 flex items-center gap-2">
                  <template v-if="f.estado === 'Pagada'">
                    <CheckCircleIcon class="w-5 h-5 text-green-600" />
                    <span class="text-green-700 font-semibold">Pagada</span>
                  </template>
                  <template v-else-if="f.estado === 'Pendiente'">
                    <ClockIcon class="w-5 h-5 text-yellow-600" />
                    <span class="text-yellow-700 font-semibold">Pendiente</span>
                  </template>
                  <template v-else>
                    <XCircleIcon class="w-5 h-5 text-red-600" />
                    <span class="text-red-700 font-semibold">Anulada</span>
                  </template>
                </td>

                <!-- Total -->
                <td
                  class="p-3 text-right font-bold"
                  :class="{
                    'text-green-700': f.estado === 'Pagada',
                    'text-yellow-700': f.estado === 'Pendiente',
                    'text-red-500': f.estado === 'Anulada'
                  }"
                >
                  $ {{ f.total.toLocaleString() }}
                </td>

                <!-- Acción -->
                <td class="p-3 text-center">
                  <a
                    v-if="f.estado !== 'Anulada'"
                    :href="'/cliente/factura/descargar/' + f.id"
                    class="inline-flex items-center gap-2 bg-[#FFCE3E] text-[#1B5E20] font-medium px-4 py-2 rounded-lg hover:bg-[#FFD95E] transition-all shadow-sm"
                  >
                    <FileDownIcon class="w-4 h-4" />
                    Descargar
                  </a>
                  <span v-else class="text-gray-400 italic">No disponible</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!--  Si no hay facturas -->
        <div v-else class="text-center text-gray-600 mt-16">
          <div class="flex justify-center mb-4">
            <ReceiptTextIcon class="w-12 h-12 text-[#2E7D32]" />
          </div>
          <h3 class="text-2xl font-semibold text-[#2E7D32] mb-2">
            Aún no tienes facturas generadas
          </h3>
          <p class="text-gray-600 italic">
            Cuando completes una reserva, aquí aparecerán tus comprobantes de pago 
          </p>
        </div>
      </div>
    </div>
  </ClienteLayout>
</template>
