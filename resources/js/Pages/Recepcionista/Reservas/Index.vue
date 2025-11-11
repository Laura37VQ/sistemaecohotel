<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  reservas: {
    type: Object,
    default: () => ({ data: [] })
  }
})

// Filtros UI
const estados = ['Todas', 'Pendiente', 'Confirmada', 'Completada', 'Cancelada']
const estadoActivo = ref('Todas')
const q = ref('')

// Filtrado rápido en cliente
const filas = computed(() => {
  const data = props.reservas?.data ?? []
  return data.filter(r => {
    const okEstado = estadoActivo.value === 'Todas' || r.estado === estadoActivo.value
    const full = `${r.codigo_reserva ?? ''} ${(r.usuario?.nombres ?? '')} ${(r.usuario?.apellidos ?? '')} ${(r.habitacion?.numero_habitacion ?? '')} ${(r.habitacion?.tipo ?? '')}`.toLowerCase()
    const okQ = !q.value || full.includes(q.value.toLowerCase())
    return okEstado && okQ
  })
})

// Acciones de estado (usa la ruta PATCH dedicadas a estado)
function setEstado(id, estado, msgOk) {
  router.patch(`/recepcionista/reservas/${id}/estado`, { estado }, {
    preserveScroll: true,
    onSuccess: () => alert(msgOk),
    onError: () => alert('No se pudo actualizar el estado.')
  })
}

function cancelar(id) {
  if (!confirm('¿Cancelar esta reserva?')) return
  setEstado(id, 'Cancelada', 'Reserva cancelada correctamente')
}
</script>

<template>
  <Head title="Reservas (Recepcionista)" />
  <RecepcionistaLayout>
    <div class="p-6">
      <!-- Encabezado / Controles -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Reservas</h2>

        <div class="flex flex-col md:flex-row gap-3 md:items-center">
          <!-- Filtro por estado -->
          <div class="flex flex-wrap gap-2">
            <button
              v-for="e in estados" :key="e"
              :class="[
                'px-3 py-1 rounded-full text-sm border transition',
                estadoActivo === e
                  ? 'bg-[#2E7D32] text-white border-[#2E7D32]'
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
              ]"
              @click="estadoActivo = e"
            >
              {{ e }}
            </button>
          </div>

          <!-- Buscador -->
          <input
            v-model="q"
            type="text"
            placeholder="Buscar: código, cliente, habitación…"
            class="border rounded-lg px-3 py-2 w-full md:w-80"
          />
        </div>

        <a
          href="/recepcionista/reservas/create"
          class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2 self-start md:self-auto"
        >
          <span class="text-xl font-bold">+</span> Crear reserva
        </a>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-lg overflow-hidden">
          <thead class="bg-yellow-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Código</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Habitación</th>
              <th class="px-4 py-3">Ingreso</th>
              <th class="px-4 py-3">Salida</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <!-- Sin filas -->
            <tr v-if="!filas.length">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                No hay reservas para mostrar.
              </td>
            </tr>

            <!-- Filas -->
            <tr v-else v-for="r in filas" :key="r.id" class="border-t hover:bg-gray-50 transition">
              <td class="px-4 py-3 font-medium">#{{ r.codigo_reserva }}</td>

              <td class="px-4 py-3">
                {{ r.usuario ? `${r.usuario.nombres} ${r.usuario.apellidos}` : '—' }}
              </td>

              <td class="px-4 py-3">
                {{ r.habitacion ? `${r.habitacion.numero_habitacion} - ${r.habitacion.tipo}` : '—' }}
              </td>

              <td class="px-4 py-3">{{ r.fecha_ingreso }}</td>
              <td class="px-4 py-3">{{ r.fecha_salida }}</td>

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

              <td class="px-4 py-3 flex flex-wrap gap-2">
                <a
                  :href="`/recepcionista/reservas/${r.id}/edit`"
                  class="px-3 py-1 bg-[#FFCE3E] text-black rounded hover:brightness-95 transition text-sm font-medium"
                >
                  Editar
                </a>

                <!-- Confirmar / Check-in -->
                <button
                  v-if="r.estado === 'Pendiente'"
                  @click="setEstado(r.id, 'Confirmada', 'Reserva confirmada (check-in)')"
                  class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 transition text-sm font-medium"
                >
                  Confirmar / Check-in
                </button>

                <!-- Check-out -->
                <button
                  v-if="r.estado === 'Confirmada'"
                  @click="setEstado(r.id, 'Completada', 'Reserva completada (check-out)')"
                  class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 transition text-sm font-medium"
                >
                  Check-out
                </button>

                <!-- Cancelar -->
                <button
                  v-if="r.estado !== 'Cancelada' && r.estado !== 'Completada'"
                  @click="cancelar(r.id)"
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm font-medium"
                >
                  Cancelar
                </button>

                <!-- Agregar consumo (SIEMPRE dentro del v-for) -->
                <a
                  v-if="r.factura"
                  :href="`/recepcionista/facturas/${r.factura.id}`"
                  class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 transition text-sm font-medium"
                >
                 Ver factura / Añadir servicios
                </a>
                <span
                  v-else
                  class="px-3 py-1 bg-gray-300 text-gray-600 rounded text-sm font-medium cursor-not-allowed"
                >
                  Sin factura
              </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación (Inertia) -->
      <div class="mt-6 flex justify-between">
        <button
          v-if="props.reservas?.prev_page_url"
          @click="router.get(props.reservas.prev_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          ← Anterior
        </button>
        <button
          v-if="props.reservas?.next_page_url"
          @click="router.get(props.reservas.next_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          Siguiente →
        </button>
      </div>
    </div>
  </RecepcionistaLayout>
</template>
