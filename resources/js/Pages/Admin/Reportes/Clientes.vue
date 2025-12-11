<script setup>
// Importación de librerías necesarias
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import * as XLSX from 'xlsx'

// Gráfico Pie
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'

// Registro de módulos del gráfico
ChartJS.register(ArcElement, Tooltip, Legend)

// Datos enviados desde el backend con Inertia
const page = usePage()
const clientes = page.props.clientes || []
const totalClientes = page.props.totalClientes || 0
const clientesConReservas = page.props.clientesConReservas || 0

// Datos básicos del gráfico
const chartData = {
  labels: ['Con reservas', 'Sin reservas'],
  datasets: [
    {
      data: [clientesConReservas, totalClientes - clientesConReservas],
      backgroundColor: ['#2E7D32', '#A5D6A7']
    }
  ]
}

// Configuración del gráfico
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' }
  }
}

// Exportación en archivo Excel
function exportExcel() {
  const data = clientes.map(c => ({
    ID: c.id,
    Nombre: `${c.nombres} ${c.apellidos}`,
    Correo: c.correo,
    Teléfono: c.telefono,
    Reservas: c.reservas?.length || 0
  }))

  const ws = XLSX.utils.json_to_sheet(data)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Clientes')

  XLSX.writeFile(wb, 'Reporte_Clientes.xlsx')
}

// Exportación del PDF (generado en backend con DomPDF)
function exportPDF() {
  window.open('/admin/reportes/clientes/pdf', '_blank')
}
</script>

<template>
  <Head title="Reporte de Clientes" />

  <AdminLayout>
    <div class="p-6 max-w-7xl mx-auto">

      <!-- Título -->
      <h2 class="text-3xl font-bold text-green-700 mb-4">Reporte de Clientes</h2>

      <p class="text-gray-600 mb-4">
        Total clientes: <b>{{ totalClientes }}</b> |
        Con reservas: <b>{{ clientesConReservas }}</b>
      </p>

      <!-- Gráfico de participación -->
      <div
        class="bg-white p-4 rounded-lg shadow mb-6 w-full md:w-1/2 mx-auto"
        style="height: 350px;"
      >
        <Pie :data="chartData" :options="chartOptions" />
      </div>

      <!-- Botones de exportación -->
      <div class="flex gap-2 mb-6">
        <button
          @click="exportExcel"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          Exportar Excel
        </button>

        <button
          @click="exportPDF"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
        >
          Exportar PDF
        </button>
      </div>

      <!-- Tabla de clientes -->
      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Nombre</th>
              <th class="px-4 py-3">Correo</th>
              <th class="px-4 py-3">Teléfono</th>
              <th class="px-4 py-3">Reservas</th>
            </tr>
          </thead>

          <tbody>
            <!-- Mensaje si no hay datos -->
            <tr v-if="!clientes.length">
              <td colspan="5" class="px-4 py-6 text-center text-gray-500 italic">
                No hay clientes registrados.
              </td>
            </tr>

            <!-- Listado -->
            <tr
              v-for="c in clientes"
              :key="c.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="px-4 py-3">{{ c.id }}</td>
              <td class="px-4 py-3">{{ c.nombres }} {{ c.apellidos }}</td>
              <td class="px-4 py-3">{{ c.correo }}</td>
              <td class="px-4 py-3">{{ c.telefono }}</td>
              <td class="px-4 py-3">{{ c.reservas?.length || 0 }}</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AdminLayout>
</template>
