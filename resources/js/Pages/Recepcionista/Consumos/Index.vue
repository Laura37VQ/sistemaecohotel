<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  consumos: {
    type: Object,
    default: () => ({ data: [] })
  }
})

function desactivar(id) {
  if (!confirm('¿Desactivar este consumo?')) return
  router.delete(`/recepcionista/consumos/${id}`, {
    preserveScroll: true,
    onSuccess: () => alert('Consumo desactivado correctamente.')
  })
}
</script>

<template>
  <Head title="Consumos" />

  <RecepcionistaLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Consumos</h2>
        <a
          href="/recepcionista/consumos/create"
          class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2"
        >
          <span class="text-xl font-bold">+</span> Nuevo consumo
        </a>
      </div>

      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Fecha</th>
              <th class="px-4 py-3">Reserva</th>
              <th class="px-4 py-3">Servicio</th>
              <th class="px-4 py-3">Cantidad</th>
              <th class="px-4 py-3">Precio</th>
              <th class="px-4 py-3">Total</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!consumos.data || !consumos.data.length">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">
                No hay consumos registrados.
              </td>
            </tr>

            <tr
              v-else
              v-for="c in consumos.data"
              :key="c.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3">{{ c.fecha || '—' }}</td>
              <td class="px-4 py-3">
                {{ c.reserva?.codigo_reserva ? `#${c.reserva.codigo_reserva}` : '—' }}
              </td>
              <td class="px-4 py-3">
                {{ c.servicio?.nombre || '—' }}
              </td>
              <td class="px-4 py-3">{{ c.cantidad }}</td>
              <td class="px-4 py-3">
                {{ new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(c.precio_unitario) }}
              </td>
              <td class="px-4 py-3 font-semibold">
                {{ new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(c.total) }}
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2 justify-center">
                  <a
                    :href="`/recepcionista/consumos/${c.id}/edit`"
                    class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm"
                  >
                    Editar
                  </a>
                  <button
                    @click="desactivar(c.id)"
                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                  >
                    Desactivar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación simple -->
      <div class="mt-6 flex justify-between">
        <button
          v-if="consumos.prev_page_url"
          @click="router.get(consumos.prev_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          ← Anterior
        </button>
        <button
          v-if="consumos.next_page_url"
          @click="router.get(consumos.next_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          Siguiente →
        </button>
      </div>
    </div>
  </RecepcionistaLayout>
</template>
