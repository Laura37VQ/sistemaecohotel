<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'

// Props desde Laravel
const props = defineProps({
  reservas: {
    type: Object,
    default: () => ({ data: [] })
  }
})

// Función para cancelar (desactivar) reserva
function cancelar(id) {
  if (!confirm('¿Cancelar esta reserva?')) return

  router.put(`/admin/reservas/${id}`, { estado: 'Cancelada' }, {
    preserveScroll: true,
    onSuccess: () => {
      alert('Reserva cancelada correctamente')
    }
  })
}
</script>

<template>
  <Head title="Reservas" />

  <AdminLayout>
    <div class="p-6">
      <!-- Encabezado -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Reservas</h2>
        <a href="/admin/reservas/create"
           class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2">
          <span class="text-xl font-bold">+</span> Crear reserva
        </a>
      </div>

      <!-- Tabla de reservas -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-lg overflow-hidden">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Código</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Habitación</th>
              <th class="px-4 py-3">Fecha ingreso</th>
              <th class="px-4 py-3">Fecha salida</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- Si no hay reservas -->
            <tr v-if="!reservas.data || !reservas.data.length">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                No hay reservas registradas.
              </td>
            </tr>

            <!-- Filas de reservas -->
            <tr v-else v-for="r in reservas.data" :key="r.id"
                class="border-t hover:bg-gray-50 transition duration-200">
              
              <td class="px-4 py-3 font-medium">#{{ r.codigo_reserva }}</td>

              <!-- Nombre completo del cliente -->
              <td class="px-4 py-3">
                {{ r.usuario ? `${r.usuario.nombres} ${r.usuario.apellidos}` : '—' }}
              </td>

              <!-- Número y tipo de habitación -->
              <td class="px-4 py-3">
                {{ r.habitacion ? `${r.habitacion.numero_habitacion} - ${r.habitacion.tipo}` : '—' }}
              </td>

              <td class="px-4 py-3">{{ r.fecha_ingreso }}</td>
              <td class="px-4 py-3">{{ r.fecha_salida }}</td>

              <!-- Estado con color -->
              <td class="px-4 py-3">
                <span
                  :class="{
                    'px-3 py-1 rounded-full text-white text-sm font-semibold': true,
                    'bg-green-500': r.estado === 'Confirmada' || r.estado === 'Completada',
                    'bg-yellow-500': r.estado === 'Pendiente',
                    'bg-red-500': r.estado === 'Cancelada'
                  }"
                >
                  {{ r.estado || 'Pendiente' }}
                </span>
              </td>

              <!-- Acciones -->
              <td class="px-4 py-3 flex gap-2">
                <a :href="`/admin/reservas/${r.id}/edit`"
                   class="flex items-center gap-1 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition text-sm font-medium">
                  Editar
                </a>
                <button @click="cancelar(r.id)"
                        class="flex items-center gap-1 px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm font-medium">
                  Cancelar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-between">
        <button v-if="reservas.prev_page_url"
                @click="router.get(reservas.prev_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100 transition">
          ← Anterior
        </button>
        <button v-if="reservas.next_page_url"
                @click="router.get(reservas.next_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100 transition">
          Siguiente →
        </button>
      </div>
    </div>
  </AdminLayout>
</template>
