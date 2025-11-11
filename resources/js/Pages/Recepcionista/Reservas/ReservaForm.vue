<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  reserva: { type: Object, default: null },
  habitaciones: { type: Array, default: () => [] },
  usuarios: { type: Array, default: () => [] },
  rol: { type: Number, default: 3 } // recepcionista
})

const hoy = ref(new Date().toISOString().split('T')[0])

const form = useForm({
  id: props.reserva?.id || null,
  usuario_id: props.reserva?.usuario_id || '',
  habitacion_id: props.reserva?.habitacion_id || '',
  fecha_ingreso: props.reserva?.fecha_ingreso || '',
  fecha_salida: props.reserva?.fecha_salida || '',
  estado: props.reserva?.estado || 'Pendiente',
  descripcion: props.reserva?.descripcion || ''
})

watch(
  () => props.reserva,
  (r) => { if (r) Object.assign(form, r) },
  { immediate: true }
)

function submit() {
  if (props.reserva) {
    form.put(`/recepcionista/reservas/${props.reserva.id}`)
  } else {
    form.post('/recepcionista/reservas')
  }
}
</script>

<template>
  <Head :title="props.reserva ? 'Editar Reserva' : 'Nueva Reserva'" />

  <RecepcionistaLayout>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-[#2E7D32]">
        {{ props.reserva ? 'Editar Reserva' : 'Nueva Reserva' }}
      </h2>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Cliente -->
        <div>
          <div class="flex justify-between items-center">
            <label class="block text-sm font-medium">Cliente</label>
            <!-- (Opcional) habilitar alta rápida luego -->
            <!-- <a href="/recepcionista/clientes/create" class="text-sm text-[#2E7D32] hover:underline">Nuevo cliente</a> -->
          </div>
          <select v-model="form.usuario_id" class="w-full border p-2 rounded shadow-sm">
            <option value="">Seleccionar cliente</option>
            <option v-for="u in props.usuarios" :key="u.id" :value="u.id">
              {{ u.nombres }} {{ u.apellidos }}
            </option>
          </select>
          <div v-if="form.errors.usuario_id" class="text-red-600 text-sm">
            {{ form.errors.usuario_id }}
          </div>
        </div>

        <!-- Habitación -->
        <div>
          <label class="block text-sm font-medium">Habitación</label>
          <select v-model="form.habitacion_id" class="w-full border p-2 rounded shadow-sm">
            <option value="">Seleccionar habitación</option>
            <option v-for="h in props.habitaciones" :key="h.id" :value="h.id">
              {{ h.numero_habitacion }} - {{ h.tipo }}
            </option>
          </select>
          <div v-if="form.errors.habitacion_id" class="text-red-600 text-sm">
            {{ form.errors.habitacion_id }}
          </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium">Fecha ingreso</label>
            <input
              type="date"
              v-model="form.fecha_ingreso"
              class="w-full border p-2 rounded shadow-sm"
              :min="hoy"
            />
            <div v-if="form.errors.fecha_ingreso" class="text-red-600 text-sm">
              {{ form.errors.fecha_ingreso }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium">Fecha salida</label>
            <input
              type="date"
              v-model="form.fecha_salida"
              :min="form.fecha_ingreso || hoy"
              class="w-full border p-2 rounded shadow-sm"
            />
            <div v-if="form.errors.fecha_salida" class="text-red-600 text-sm">
              {{ form.errors.fecha_salida }}
            </div>
          </div>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium">Estado</label>
          <select v-model="form.estado" class="w-full border p-2 rounded shadow-sm">
            <option value="Pendiente">Pendiente</option>
            <option value="Confirmada">Confirmada</option>
            <option value="Cancelada">Cancelada</option>
            <option value="Completada">Completada</option>
          </select>
          <div v-if="form.errors.estado" class="text-red-600 text-sm">
            {{ form.errors.estado }}
          </div>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium">Descripción</label>
          <textarea v-model="form.descripcion" class="w-full border p-2 rounded shadow-sm"></textarea>
          <div v-if="form.errors.descripcion" class="text-red-600 text-sm">
            {{ form.errors.descripcion }}
          </div>
        </div>

        <!-- Botones -->
        <div class="flex gap-2 mt-4">
          <button
            type="submit"
            class="bg-[#2E7D32] text-white px-4 py-2 rounded shadow hover:bg-green-700 transition"
          >
            {{ props.reserva ? 'Actualizar' : 'Guardar' }}
          </button>
          <a
            href="/recepcionista/reservas"
            class="px-4 py-2 border rounded shadow hover:bg-gray-100 transition"
          >
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </RecepcionistaLayout>
</template>
