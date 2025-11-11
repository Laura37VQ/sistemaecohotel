<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import { Bar } from 'vue-chartjs'
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import 'jspdf-autotable'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const { facturas = [], totalIngresos = 0, promedioFactura = 0 } = usePage().props.value || {}
const fechaInicio = ref('')
const fechaFin = ref('')

function filtrarFacturas() {
  router.get('/admin/reportes/ingresos', {
    fecha_inicio: fechaInicio.value,
    fecha_fin: fechaFin.value
  }, { preserveState: true })
}

const chartData = computed(() => ({
  labels: facturas.map(f => f.fecha_emision),
  datasets: [{ label: 'Ingresos', data: facturas.map(f => f.total), backgroundColor: '#2E7D32' }]
}))
const chartOptions = { responsive: true, plugins: { legend: { display: false } } }

function exportExcel() {
  const ws = XLSX.utils.json_to_sheet(facturas)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Facturación')
  XLSX.writeFile(wb, 'Facturacion.xlsx')
}

function exportPDF() {
  const doc = new jsPDF()
  doc.text('Reporte de Facturación', 14, 16)
  const rows = facturas.map(f => [f.id, f.cliente?.nombres + ' ' + f.cliente?.apellidos, f.fecha_emision, f.total])
  doc.autoTable({ head: [['#', 'Cliente', 'Fecha', 'Total']], body: rows, startY: 20 })
  doc.save('Facturacion.pdf')
}
</script>

<template>
  <Head title="Reporte de Facturación" />
  <AdminLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <h2 class="text-3xl font-bold text-green-700 mb-2"> Reporte de Facturación</h2>
      <p class="text-gray-600 mb-4">Total facturado: <b>${{ totalIngresos.toLocaleString() }}</b> | Promedio por factura: <b>${{ promedioFactura }}</b></p>

      <div class="flex gap-2 mb-6 flex-wrap">
        <input type="date" v-model="fechaInicio" class="border px-3 py-2 rounded w-48" />
        <input type="date" v-model="fechaFin" class="border px-3 py-2 rounded w-48" />
        <button @click="filtrarFacturas" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">Filtrar</button>
        <button @click="exportExcel" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Exportar Excel</button>
        <button @click="exportPDF" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">Exportar PDF</button>
      </div>

      <div class="bg-white p-4 rounded-lg shadow mb-6">
        <Bar :chart-data="chartData" :chart-options="chartOptions" />
      </div>

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
              <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">No hay facturas registradas.</td>
            </tr>
            <tr v-for="f in facturas" :key="f.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3">{{ f.id }}</td>
              <td class="px-4 py-3">{{ f.cliente?.nombres }} {{ f.cliente?.apellidos }}</td>
              <td class="px-4 py-3">{{ f.fecha_emision }}</td>
              <td class="px-4 py-3 font-semibold">${{ f.total }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
