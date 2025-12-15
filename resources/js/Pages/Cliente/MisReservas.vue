<script setup>
import ClienteLayout from '@/layouts/ClienteLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { CalendarDays, Pencil, XCircle, Bed } from 'lucide-vue-next'

const props = defineProps({
  reservas: Array
})

//  Cancelar una reserva
function cancelarReserva(id) {
  if (confirm('¿Seguro que deseas cancelar esta reserva?')) {
    router.delete(`/cliente/reservas/${id}`, {
      onSuccess: () => alert('Reserva cancelada correctamente.')
    })
  }
}

//  Editar reserva
function editarReserva(id) {
  router.visit(`/cliente/reservas/${id}/editar`)
}
</script>

<template>
  <Head title="Mis Reservas" />
  <ClienteLayout>
    <div class="max-w-6xl mx-auto py-10 px-6">
      <h2 class="text-4xl font-extrabold text-[#2E7D32] mb-10 text-center tracking-tight">
         Mis Reservas
      </h2>
      <p class="text-gray-600 text-center mb-8">
        Desde aquí puedes consultar, modificar o cancelar tus reservas activas.
      </p>

      <!-- Si hay reservas -->
      <div v-if="reservas.length" class="grid md:grid-cols-2 gap-6">
        <div
          v-for="r in reservas"
          :key="r.id"
          class="bg-white rounded-3xl shadow-md hover:shadow-lg transition-all border border-green-100 p-6 flex flex-col justify-between"
        >
          <!-- Encabezado -->
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-xl font-bold text-[#1B5E20]">
              {{ r.habitacion?.tipo }}
            </h3>
            <span
              class="text-xs px-3 py-1 rounded-full font-semibold"
              :class="{
                'bg-green-100 text-green-700': r.estado === 'Confirmada',
                'bg-yellow-100 text-yellow-700': r.estado === 'Pendiente',
                'bg-red-100 text-red-700': r.estado === 'Cancelada'
              }"
            >
              {{ r.estado }}
            </span>
          </div>

          <!-- Código y fechas -->
          <div class="text-gray-700 space-y-2 text-sm">
            <p>
              <span class="font-semibold text-[#2E7D32]">Código:</span>
              {{ r.codigo_reserva }}
            </p>
            <p class="flex items-center gap-2">
              <CalendarDays class="w-4 h-4 text-[#2E7D32]" />
              <span>{{ r.fecha_ingreso }} → {{ r.fecha_salida }}</span>
            </p>
          </div>

          <!-- Descripción -->
          <p v-if="r.descripcion" class="mt-3 text-gray-600 italic text-sm">
            "{{ r.descripcion }}"
          </p>

          <!-- Botones -->
          <div class="flex justify-end gap-3 mt-5">
            <button
              @click="editarReserva(r.id)"
              class="flex items-center gap-1 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition text-sm shadow-sm"
            >
              <Pencil class="w-4 h-4" /> Modificar
            </button>

            <button
              v-if="r.estado === 'Confirmada'"
              @click="cancelarReserva(r.id)"
              class="flex items-center gap-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm shadow-sm"
            >
              <XCircle class="w-4 h-4" /> Cancelar
            </button>
          </div>
        </div>
      </div>

      <!-- Si no hay reservas -->
      <div v-else class="text-center text-gray-600 mt-16">
        <div class="flex justify-center mb-4">
          <Bed class="w-12 h-12 text-[#2E7D32]" />
        </div>
        <h3 class="text-2xl font-semibold text-[#2E7D32] mb-2">
          Aún no tienes reservas activas
        </h3>
        <p class="text-gray-600 italic">
          Explora nuestras habitaciones y planifica tu próxima estadía 
        </p>
      </div>
    </div>
  </ClienteLayout>
</template>
