<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { watch, ref } from 'vue'

// Props del controller
const props = defineProps({
  reserva: { type: Object, default: null },
  habitaciones: { type: Array, default: () => [] },
  usuarios: { type: Array, default: () => [] },
  rol: { type: Number, default: 2 } // 1=admin, 2=cliente, 3=recepcionista
})

// Fecha mínima para clientes
const hoy = ref(new Date().toISOString().split('T')[0])

// Formulario
const form = useForm({
  id: props.reserva?.id || null,
  usuario_id: props.reserva?.usuario_id || (props.rol == 2 ? props.usuarios[0].id : ''),
  habitacion_id: props.reserva?.habitacion_id || '',
  fecha_ingreso: props.reserva?.fecha_ingreso || '',
  fecha_salida: props.reserva?.fecha_salida || '',
  estado: props.reserva?.estado || 'Pendiente',
  descripcion: props.reserva?.descripcion || ''
})

watch(
  () => props.reserva,
  (newVal) => { if (newVal) Object.assign(form, newVal) },
  { immediate: true }
)

// Enviar formulario
function submit() {
  if (props.reserva) form.put(`/admin/reservas/${props.reserva.id}`)
  else form.post('/admin/reservas')
}
</script>

<template>
  <Head :title="props.reserva ? 'Editar Reserva' : 'Nueva Reserva'" />
  <AdminLayout>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-[#2E7D32]">
        {{ props.reserva ? 'Editar Reserva' : 'Nueva Reserva' }}
      </h2>

      <form @submit.prevent="submit" class="space-y-4">
        <p class="text-sm text-gray-500 mb-4">
          Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
        </p>
        <!-- Usuario -->
        <div v-if="rol != 2">
          <label class="block text-sm font-medium">
            Usuario <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500">
            Seleccione el cliente asociado a la reserva.
          </p>
          <select v-model="form.usuario_id" class="w-full border p-2 rounded shadow-sm">
            <option value="">Seleccionar usuario</option>
            <option v-for="u in props.usuarios" :key="u.id" :value="u.id">
              {{ u.nombres }} {{ u.apellidos }}
            </option>
          </select>
          <div v-if="form.errors.usuario_id" class="text-red-600 text-sm">{{ form.errors.usuario_id }}</div>
        </div>
        <input v-else type="hidden" v-model="form.usuario_id" />

        <!-- Habitación -->
        <div>
          <label class="block text-sm font-medium">
            Habitación <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500">
            Solo se muestran habitaciones disponibles.
          </p>
          <select v-model="form.habitacion_id" class="w-full border p-2 rounded shadow-sm">
            <option value="">Seleccionar habitación</option>
            <option v-for="h in props.habitaciones" :key="h.id" :value="h.id">
              {{ h.numero_habitacion }} - {{ h.tipo }}
            </option>
          </select>
          <div v-if="form.errors.habitacion_id" class="text-red-600 text-sm">{{ form.errors.habitacion_id }}</div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium">
              Fecha ingreso <span class="text-red-500">*</span>
            </label>
            <!-- Solo para clientes mínimo hoy; admins sin restricción -->
            <input 
              type="date" 
              v-model="form.fecha_ingreso" 
              :min="rol == 2 ? hoy : null" 
              class="w-full border p-2 rounded shadow-sm" 
            />
            <div v-if="form.errors.fecha_ingreso" class="text-red-600 text-sm">{{ form.errors.fecha_ingreso }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">
              Fecha salida <span class="text-red-500">*</span>
            </label>
            <input 
              type="date" 
              v-model="form.fecha_salida" 
              :min="form.fecha_ingreso || (rol == 2 ? hoy : null)" 
              class="w-full border p-2 rounded shadow-sm" 
            />
            <div v-if="form.errors.fecha_salida" class="text-red-600 text-sm">{{ form.errors.fecha_salida }}</div>
          </div>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium">
            Estado <span class="text-red-500">*</span>
          </label>
          <select v-model="form.estado" class="w-full border p-2 rounded shadow-sm">
            <option value="Pendiente">Pendiente</option>
            <option value="Confirmada">Confirmada</option>
            <option value="Cancelada">Cancelada</option>
            <option value="Completada">Completada</option>
          </select>
          <div v-if="form.errors.estado" class="text-red-600 text-sm">{{ form.errors.estado }}</div>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium">
            Descripción <span class="text-gray-400">(opcional)</span>
          </label>
          <p class="text-xs text-gray-500">
            Puede incluir observaciones adicionales sobre la reserva.
          </p>
          <textarea v-model="form.descripcion" class="w-full border p-2 rounded shadow-sm"></textarea>
          <div v-if="form.errors.descripcion" class="text-red-600 text-sm">{{ form.errors.descripcion }}</div>
        </div>

        <!-- Botones -->
        <div class="flex gap-2 mt-4">
          <button type="submit" class="bg-[#2E7D32] text-white px-4 py-2 rounded shadow hover:bg-green-700 transition">
            {{ props.reserva ? 'Actualizar' : 'Guardar' }}
          </button>
          <a href="/admin/reservas" class="px-4 py-2 border rounded shadow hover:bg-gray-100 transition">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
