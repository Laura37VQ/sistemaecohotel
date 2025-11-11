<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Acceso seguro a las props de Inertia
const page = usePage()
const props = computed(() => page?.props?.value || {})

//  Variables
const reservas = computed(() => props.value?.reservas || [])
const tasaOcupacion = computed(() => props.value?.tasaOcupacion || 0)
const fechaInicio = ref(props.value?.fechaInicio || '')
const fechaFin = ref(props.value?.fechaFin || '')

//  Filtro
function filtrarReservas() {
  router.get('/admin/reportes/ocupacion', {
    fecha_inicio: fechaInicio.value,
    fecha_fin: fechaFin.value
  }, { preserveState: false })
}
</script>

<template>
  <Head title="Reporte de Ocupación" />
  <AdminLayout>
    <div class="p-6 max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-3xl font-bold text-green-700"> Reporte de Ocupación</h2>
        <Link
          href="/admin/reportes"
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
        >
          ← Volver
        </Link>
      </div>

      <p class="mb-4 text-gray-600">
        Tasa de ocupación general:
        <b>{{ tasaOcupacion }}%</b>
      </p>

      <div class="flex gap-2 mb-6 flex-wrap">
        <input type="date" v-model="fechaInicio" class="border px-3 py-2 rounded w-48" />
        <input type="date" v-model="fechaFin" class="border px-3 py-2 rounded w-48" />
        <button
          @click="filtrarReservas"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
        >
          Filtrar
        </button>
      </div>

      <!--  Tabla -->
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
                No hay reservas registradas.
              </td>
            </tr>
            <tr
              v-for="r in reservas"
              :key="r.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3">{{ r.id }}</td>
              <td class="px-4 py-3">
                {{ r.usuario?.nombres || 'Sin nombre' }} {{ r.usuario?.apellidos }}
              </td>
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
