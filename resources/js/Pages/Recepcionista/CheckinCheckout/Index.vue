<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  hoy: { type: String, default: '' },
  llegadas: { type: Array, default: () => [] },
  enCasa: { type: Array, default: () => [] },
  salidas: { type: Array, default: () => [] }
})

const pestaña = ref('llegadas') // pestaña activa: 'llegadas' | 'encasa' | 'salidas'

function setEstado(id, estado, msgOk) {
  router.patch(`/recepcionista/reservas/${id}/estado`, { estado }, {
    preserveScroll: true,
    onSuccess: () => alert(msgOk),
    onError: () => alert('No se pudo actualizar el estado.')
  })
}
</script>

<template>
  <Head title="Check-in / Check-out" />

  <RecepcionistaLayout>
    <div class="p-6">
      <!--  Encabezado -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Check-in / Check-out</h2>
        <div class="text-gray-600">Hoy: <strong>{{ props.hoy }}</strong></div>
      </div>

      <!--  Pestañas -->
      <div class="flex gap-2 mb-4">
        <button
          class="px-4 py-2 rounded border"
          :class="pestaña==='llegadas' ? 'bg-[#2E7D32] text-white border-[#2E7D32]' : 'bg-white'"
          @click="pestaña='llegadas'">
          Llegadas (Pendientes)
        </button>
        <button
          class="px-4 py-2 rounded border"
          :class="pestaña==='encasa' ? 'bg-[#2E7D32] text-white border-[#2E7D32]' : 'bg-white'"
          @click="pestaña='encasa'">
          En casa (Confirmadas)
        </button>
        <button
          class="px-4 py-2 rounded border"
          :class="pestaña==='salidas' ? 'bg-[#2E7D32] text-white border-[#2E7D32]' : 'bg-white'"
          @click="pestaña='salidas'">
          Salidas (Hoy)
        </button>
      </div>

      <!--  Tabla genérica -->
      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Código</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Habitación</th>
              <th class="px-4 py-3">Ingreso</th>
              <th class="px-4 py-3">Salida</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <!--  Llegadas -->
            <template v-if="pestaña === 'llegadas'">
              <tr v-if="!props.llegadas.length">
                <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                  No hay llegadas pendientes.
                </td>
              </tr>

              <tr v-for="r in props.llegadas" :key="r.id" class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">#{{ r.codigo_reserva }}</td>
                <td class="px-4 py-3">{{ r.usuario ? `${r.usuario.nombres} ${r.usuario.apellidos}` : '—' }}</td>
                <td class="px-4 py-3">{{ r.habitacion ? `${r.habitacion.numero_habitacion} - ${r.habitacion.tipo}` : '—' }}</td>
                <td class="px-4 py-3">{{ r.fecha_ingreso }}</td>
                <td class="px-4 py-3">{{ r.fecha_salida }}</td>
                <td class="px-4 py-3">
                  <span class="px-3 py-1 rounded-full bg-yellow-500 text-white text-sm">Pendiente</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex gap-2 justify-center">
                    <button
                      @click="setEstado(r.id, 'Confirmada', 'Reserva confirmada (check-in)')"
                      class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 text-sm">
                      Check-in
                    </button>
                    <button
                      @click="setEstado(r.id, 'Cancelada', 'Reserva cancelada')"
                      class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                      Cancelar
                    </button>
                  </div>
                </td>
              </tr>
            </template>

            <!--  En casa -->
            <template v-else-if="pestaña === 'encasa'">
              <tr v-if="!props.enCasa.length">
                <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                  No hay huéspedes en casa.
                </td>
              </tr>

              <tr v-for="r in props.enCasa" :key="r.id" class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">#{{ r.codigo_reserva }}</td>
                <td class="px-4 py-3">{{ r.usuario ? `${r.usuario.nombres} ${r.usuario.apellidos}` : '—' }}</td>
                <td class="px-4 py-3">{{ r.habitacion ? `${r.habitacion.numero_habitacion} - ${r.habitacion.tipo}` : '—' }}</td>
                <td class="px-4 py-3">{{ r.fecha_ingreso }}</td>
                <td class="px-4 py-3">{{ r.fecha_salida }}</td>
                <td class="px-4 py-3">
                  <span class="px-3 py-1 rounded-full bg-green-500 text-white text-sm">Confirmada</span>
                </td>

                <td class="px-4 py-3">
                  <div class="flex gap-2 justify-center">
                    <!-- Ver factura o mensaje -->
                    <a
                      v-if="r.factura"
                      :href="`/recepcionista/facturas/${r.factura.id}`"
                      class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 text-sm">
                      Ver factura / Añadir servicios
                    </a>
                    <span
                      v-else
                      class="px-3 py-1 bg-gray-300 text-gray-600 rounded text-sm cursor-not-allowed">
                      Sin factura
                    </span>

                    <!-- Check-out -->
                    <button
                      @click="setEstado(r.id, 'Completada', 'Reserva completada (check-out)')"
                      class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 text-sm">
                      Check-out
                    </button>
                  </div>
                </td>
              </tr>
            </template>

            <!--  Salidas -->
            <template v-else>
              <tr v-if="!props.salidas.length">
                <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                  No hay salidas pendientes hoy.
                </td>
              </tr>

              <tr v-for="r in props.salidas" :key="r.id" class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">#{{ r.codigo_reserva }}</td>
                <td class="px-4 py-3">{{ r.usuario ? `${r.usuario.nombres} ${r.usuario.apellidos}` : '—' }}</td>
                <td class="px-4 py-3">{{ r.habitacion ? `${r.habitacion.numero_habitacion} - ${r.habitacion.tipo}` : '—' }}</td>
                <td class="px-4 py-3">{{ r.fecha_ingreso }}</td>
                <td class="px-4 py-3">{{ r.fecha_salida }}</td>
                <td class="px-4 py-3">
                  <span class="px-3 py-1 rounded-full bg-green-500 text-white text-sm">Confirmada</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex gap-2 justify-center">
                    <button
                      @click="setEstado(r.id, 'Completada', 'Reserva completada (check-out)')"
                      class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 text-sm">
                      Check-out
                    </button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </RecepcionistaLayout>
</template>
