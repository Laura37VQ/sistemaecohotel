<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import { Bar } from 'vue-chartjs'

import * as XLSX from 'xlsx'

// Registrar elementos de Chart.js
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

// Props desde Laravel
const page = usePage()

const facturas = ref(page.props.facturas || [])
const totalIngresos = ref(page.props.totalIngresos || 0)
const promedioFactura = ref(page.props.promedioFactura || 0)

const fechaInicio = ref(page.props.fechaInicio || '')
const fechaFin = ref(page.props.fechaFin || '')

/* FILTRO DE FACTURAS */
function filtrarFacturas() {
  router.get(
    '/admin/reportes/ingresos',
    {
      fecha_inicio: fechaInicio.value,
      fecha_fin: fechaFin.value
    },
    {
      replace: true,
      preserveScroll: true
    }
  )
}

/* DATOS DEL GRÁFICO */
const chartData = computed(() => ({
  labels: facturas.value.map(f =>
    f.fecha_emision ? f.fecha_emision.substring(0, 10) : ''
  ),
  datasets: [
    {
      label: 'Ingresos',
      data: facturas.value.map(f => Number(f.total)),
      backgroundColor: '#2E7D32'
    }
  ]
}))

const chartOptions = {
  responsive: true,
  plugins: { legend: { display: false } }
}

/* EXPORTAR A EXCEL */
function exportExcel() {
  const datos = facturas.value.map(f => ({
    ID: f.id,
    Cliente: `${f.cliente?.nombres || ''} ${f.cliente?.apellidos || ''}`,
    Fecha: f.fecha_emision?.substring(0, 10),
    Total: f.total
  }))

  const ws = XLSX.utils.json_to_sheet(datos)
  const wb = XLSX.utils.book_new()

  XLSX.utils.book_append_sheet(wb, ws, 'Facturación')
  XLSX.writeFile(wb, 'Reporte_Facturacion.xlsx')
}

/* EXPORTAR A PDF (USANDO DOMPDF DEL BACKEND) */
function exportPDF() {
  const query = new URLSearchParams({
    fecha_inicio: fechaInicio.value || '',
    fecha_fin: fechaFin.value || ''
  }).toString()

  // Abrir PDF desde backend
  window.open(`/admin/reportes/ingresos/pdf?${query}`, '_blank')
}
</script>

<template>
  <Head title="Reporte de Facturación" />

  <AdminLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <h2 class="text-3xl font-bold text-green-700 mb-2">Reporte de Facturación</h2>

      <p class="text-gray-600 mb-4">
        Total facturado: <b>${{ totalIngresos.toLocaleString() }}</b> |
        Promedio por factura: <b>${{ promedioFactura.toLocaleString() }}</b>
      </p>

      <!-- FILTROS -->
      <div class="flex gap-2 mb-6 flex-wrap">
        <input type="date" v-model="fechaInicio" class="border px-3 py-2 rounded w-48" />
        <input type="date" v-model="fechaFin" class="border px-3 py-2 rounded w-48" />

        <button @click="filtrarFacturas" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Filtrar
        </button>

        <button @click="exportExcel" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Excel
        </button>

        <button @click="exportPDF" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
          PDF
        </button>
      </div>
      <p class="text-sm text-gray-500 mb-6">
        Seleccione un rango de fechas para consultar los ingresos del hotel. Si no se seleccionan fechas, se mostrarán los ingresos generales registrados.
      </p>
      <p class="text-gray-600 mb-4">
        Total facturado: <b>${{ totalIngresos.toLocaleString() }}</b> |
        Promedio por factura: <b>${{ promedioFactura.toLocaleString() }}</b>
      </p>
      <p class="text-sm text-gray-500 mb-6">
        El total facturado corresponde a la suma de todas las facturas emitidas en el período seleccionado, mientras que el promedio por factura representa el valor medio de cada transacción.
      </p>
      <!-- GRÁFICO -->
      <div class="bg-white p-4 rounded-lg shadow mb-6">
        <Bar :data="chartData" :options="chartOptions" />
      </div>

      <!-- TABLA -->
      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Fecha</th>
              <th class="px-4 py-3">Total</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!facturas.length">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">
                No hay facturas registradas.
              </td>
            </tr>

            <tr v-for="f in facturas" :key="f.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3">{{ f.id }}</td>
              <td class="px-4 py-3">{{ f.cliente?.nombres }} {{ f.cliente?.apellidos }}</td>
              <td class="px-4 py-3">{{ f.fecha_emision?.substring(0, 10) }}</td>
              <td class="px-4 py-3 font-semibold">${{ Number(f.total).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
