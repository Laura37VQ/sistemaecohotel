<script setup>
/* --------------------------------------------
   Importaciones principales
--------------------------------------------- */
import { ref, computed } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

/* Librería para exportar a Excel */
import * as XLSX from "xlsx"

/* Librerías de Chart.js para el gráfico */
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'

import { Bar } from 'vue-chartjs'

/* Registrar elementos de ChartJS */
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

/* --------------------------------------------
   Datos recibidos desde Laravel mediante Inertia
--------------------------------------------- */
const page = usePage()

const reservas = computed(() => page.props.reservas || [])
const fechaInicio = ref(page.props.fechaInicio || '')
const fechaFin = ref(page.props.fechaFin || '')
const totalHabitaciones = computed(() => page.props.totalHabitaciones || 0)
const tasaOcupacion = computed(() => page.props.tasaOcupacion || 0)

/* --------------------------------------------
   Configuración del gráfico (Ocupadas vs Disponibles)
--------------------------------------------- */
const chartData = computed(() => ({
  labels: ['Ocupadas', 'Disponibles'],
  datasets: [
    {
      label: 'Habitaciones',
      data: [
        reservas.value.length,
        Math.max(totalHabitaciones.value - reservas.value.length, 0)
      ],
      backgroundColor: ['#2E7D32', '#9CCC65']
    }
  ]
}))

const chartOptions = {
  responsive: true,
  plugins: { legend: { display: true } }
}

/* --------------------------------------------
   Filtro por fechas (actualiza datos desde backend)
--------------------------------------------- */
function filtrarReservas() {
  if (fechaInicio.value && fechaFin.value && fechaInicio.value > fechaFin.value) {
    alert("La fecha inicial no puede ser mayor que la final.")
    return
  }

  router.get('/admin/reportes/ocupacion', {
    fecha_inicio: fechaInicio.value,
    fecha_fin: fechaFin.value
  }, { preserveState: true })
}

/* --------------------------------------------
   Exportar reporte a Excel
--------------------------------------------- */
function exportExcel() {
  if (!reservas.value.length) {
    alert("No hay datos para exportar.")
    return
  }

  // Construcción de los datos para Excel
  const data = reservas.value.map(r => ({
    ID: r.id,
    Cliente: `${r.usuario?.nombres || ''} ${r.usuario?.apellidos || ''}`,
    Habitacion: r.habitacion?.numero_habitacion || '',
    Ingreso: r.fecha_ingreso,
    Salida: r.fecha_salida
  }))

  // Crear hoja y archivo Excel
  const ws = XLSX.utils.json_to_sheet(data)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, "Ocupación")

  XLSX.writeFile(wb, "Reporte_Ocupacion.xlsx")
}

/* --------------------------------------------
   Exportar reporte PDF (generado en backend)
--------------------------------------------- */
function exportPDF() {
  const query = new URLSearchParams({
    fecha_inicio: fechaInicio.value || '',
    fecha_fin: fechaFin.value || ''
  }).toString()

  window.open(`/admin/reportes/ocupacion/pdf?${query}`, '_blank')
}
</script>

<template>
  <Head title="Reporte de Ocupación" />

  <AdminLayout>
    <div class="p-6 max-w-7xl mx-auto">

      <!-- Encabezado -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-3xl font-bold text-green-700">Reporte de Ocupación</h2>
        <Link href="/admin/reportes"
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
          ← Volver
        </Link>
      </div>

      <!-- Tasa general -->
      <p class="mb-4 text-gray-600">
        Tasa de ocupación general:
        <b>{{ tasaOcupacion }}%</b>
      </p>
      <p class="text-sm text-gray-500 mb-6">
        La tasa de ocupación representa el porcentaje de habitaciones ocupadas frente al total disponible en el período seleccionado.
      </p>

      <!-- Filtros -->
      <div class="flex gap-2 mb-6 flex-wrap">
        <input type="date" v-model="fechaInicio" class="border px-3 py-2 rounded w-48" />
        <input type="date" v-model="fechaFin" class="border px-3 py-2 rounded w-48" />

        <button @click="filtrarReservas"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
          Filtrar
        </button>

        <button @click="exportExcel"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
          Excel
        </button>

        <button @click="exportPDF"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
          PDF
        </button>
      </div>
      <p class="text-sm text-gray-500 mb-6">
          Seleccione un rango de fechas para analizar la ocupación del hotel. 
          Si no se seleccionan fechas, el sistema mostrará la información general.
        </p>
      <p class="text-sm text-gray-500 mb-6">
        Puede exportar el reporte en formato Excel o PDF para su análisis o respaldo.
      </p>

      <!-- Gráfico -->
      <div class="bg-white p-4 rounded-lg shadow mb-6">
        <Bar :data="chartData" :options="chartOptions" />
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Habitación</th>
              <th class="px-4 py-3">Ingreso</th>
              <th class="px-4 py-3">Salida</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!reservas.length">
              <td colspan="5" class="px-4 py-6 text-center text-gray-500 italic">
                No hay reservas registradas para este rango.
              </td>
            </tr>

            <tr v-for="r in reservas" :key="r.id" class="border-t hover:bg-gray-50 transition">
              <td class="px-4 py-3">{{ r.id }}</td>
              <td class="px-4 py-3">{{ r.usuario?.nombres }} {{ r.usuario?.apellidos }}</td>
              <td class="px-4 py-3">{{ r.habitacion?.numero_habitacion }}</td>
              <td class="px-4 py-3">{{ r.fecha_ingreso }}</td>
              <td class="px-4 py-3">{{ r.fecha_salida }}</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AdminLayout>
</template>
