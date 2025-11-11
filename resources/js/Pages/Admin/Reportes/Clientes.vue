<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import * as XLSX from 'xlsx'
import jsPDF from 'jspdf'
import 'jspdf-autotable'

const { clientes = [], totalClientes = 0, clientesConReservas = 0 } = usePage().props.value || {}

function exportExcel() {
  const data = clientes.map(c => ({
    ID: c.id,
    Nombre: `${c.nombres} ${c.apellidos}`,
    Correo: c.correo,
    Teléfono: c.telefono,
    Reservas: (c.reservas || []).map(r => `${r.habitacion?.numero_habitacion} (${r.fecha_ingreso} - ${r.fecha_salida})`).join('; ')
  }))
  const ws = XLSX.utils.json_to_sheet(data)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Clientes')
  XLSX.writeFile(wb, 'Clientes.xlsx')
}

function exportPDF() {
  const doc = new jsPDF()
  doc.text('Reporte de Clientes', 14, 16)
  const rows = clientes.map(c => [
    c.id,
    `${c.nombres} ${c.apellidos}`,
    c.correo,
    c.telefono,
    (c.reservas || []).map(r => `${r.habitacion?.numero_habitacion} (${r.fecha_ingreso} - ${r.fecha_salida})`).join('; ')
  ])
  doc.autoTable({ head: [['ID', 'Nombre', 'Correo', 'Teléfono', 'Reservas']], body: rows, startY: 20 })
  doc.save('Clientes.pdf')
}
</script>

<template>
  <Head title="Reporte de Clientes" />
  <AdminLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <h2 class="text-3xl font-bold text-green-700 mb-2"> Reporte de Clientes</h2>
      <p class="text-gray-600 mb-4">Total clientes: <b>{{ totalClientes }}</b> | Con reservas: <b>{{ clientesConReservas }}</b></p>

      <div class="flex gap-2 mb-6">
        <button @click="exportExcel" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Exportar Excel</button>
        <button @click="exportPDF" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">Exportar PDF</button>
      </div>

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
            <tr v-if="!clientes.length">
              <td colspan="5" class="px-4 py-6 text-center text-gray-500 italic">No hay clientes registrados.</td>
            </tr>
            <tr v-for="c in clientes" :key="c.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3">{{ c.id }}</td>
              <td class="px-4 py-3">{{ c.nombres }} {{ c.apellidos }}</td>
              <td class="px-4 py-3">{{ c.correo }}</td>
              <td class="px-4 py-3">{{ c.telefono }}</td>
              <td class="px-4 py-3">
                <span v-for="r in c.reservas" :key="r.id">
                  {{ r.habitacion?.numero_habitacion }} ({{ r.fecha_ingreso }} - {{ r.fecha_salida }})<br>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
