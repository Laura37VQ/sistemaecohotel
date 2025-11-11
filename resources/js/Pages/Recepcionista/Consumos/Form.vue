<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { watch, computed } from 'vue'

const props = defineProps({
  consumo: { type: Object, default: null },
  reservas: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
  reserva_id: { type: [Number, String, null], default: null }, // aceptar string/int
  mensajeAviso: { type: String, default: null }
})

//  Si viene desde Reservas con ?reserva=ID, se convierte a Number
const preseleccion = props.reserva_id != null ? Number(props.reserva_id) : null
const hoyISO = new Date().toISOString().split('T')[0]

//  Normaliza todo a Number para evitar desajustes de tipo
const form = useForm({
  reserva_id: props.consumo?.reserva_id != null
    ? Number(props.consumo.reserva_id)
    : (preseleccion ?? ''),

  servicio_id: props.consumo?.servicio_id != null
    ? Number(props.consumo.servicio_id)
    : '',

  cantidad: props.consumo?.cantidad != null
    ? Number(props.consumo.cantidad)
    : 1,

  precio_unitario: props.consumo?.precio_unitario != null
    ? Number(props.consumo.precio_unitario)
    : 0,

  total: props.consumo?.total != null
    ? Number(props.consumo.total)
    : 0,

  fecha: props.consumo?.fecha ?? hoyISO,
  observacion: props.consumo?.observacion ?? ''
})

watch(
  () => form.servicio_id,
  (id) => {
    if (!id) return
    const s = props.servicios.find(x => Number(x.id) === Number(id))
    form.precio_unitario = s ? Number(s.precio) || 0 : 0
    calcularTotal()
  }
)

function calcularTotal() {
  const cant = Number(form.cantidad) || 0
  const precio = Number(form.precio_unitario) || 0
  form.total = cant * precio
}

const titulo = computed(() => props.consumo ? 'Editar Consumo' : 'Nuevo Consumo')

function submit() {
  calcularTotal()
  if (props.consumo) {
    form.put(`/recepcionista/consumos/${props.consumo.id}`, {
      onSuccess: () => {
        alert('Consumo actualizado correctamente.')
        window.location.href = '/recepcionista/consumos'
      }
    })
  } else {
    form.post('/recepcionista/consumos', {
      onSuccess: () => {
        alert('Consumo registrado correctamente.')
        window.location.href = '/recepcionista/consumos'
      }
    })
  }
}
</script>

<template>
  <Head :title="titulo" />

  <RecepcionistaLayout>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded-lg shadow">

      <h2 class="text-2xl font-bold mb-4 text-[#2E7D32]">{{ titulo }}</h2>

      <div v-if="mensajeAviso" class="mb-4 p-3 rounded bg-yellow-50 text-yellow-800">
        {{ mensajeAviso }}
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Reserva -->
        <div>
          <label class="block text-sm font-medium">Reserva</label>

          <!-- Si viene preseleccionada, bloquea el cambio -->
          <select
            v-model.number="form.reserva_id"
            class="w-full border p-2 rounded"
            :disabled="preseleccion !== null"
            required
          >
            <option value="">Seleccionar reserva</option>
            <option
              v-for="r in reservas"
              :key="r.id"
              :value="Number(r.id)"
            >
              {{ `#${r.codigo_reserva} (Hab. ${r.habitacion_id})` }}
            </option>
          </select>

          <!-- Muestra un chip con la reserva fija cuando está bloqueada -->
          <p v-if="preseleccion !== null" class="mt-1 text-sm text-gray-600">
            Reserva fijada desde Reservas.
          </p>

          <div v-if="form.errors.reserva_id" class="text-red-600 text-sm">
            {{ form.errors.reserva_id }}
          </div>
        </div>

        <!-- Servicio -->
        <div>
          <label class="block text-sm font-medium">Servicio</label>
          <select v-model.number="form.servicio_id" class="w-full border p-2 rounded" required>
            <option value="">Seleccionar servicio</option>
            <option v-for="s in servicios" :key="s.id" :value="Number(s.id)">
              {{ s.nombre }} — {{
                new Intl.NumberFormat('es-CO', {
                  style: 'currency', currency: 'COP', minimumFractionDigits: 0
                }).format(Number(s.precio) || 0)
              }}
            </option>
          </select>
          <div v-if="form.errors.servicio_id" class="text-red-600 text-sm">
            {{ form.errors.servicio_id }}
          </div>
        </div>

        <!-- Cantidad / Precio / Total -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium">Cantidad</label>
            <input type="number" min="1" v-model.number="form.cantidad" @input="calcularTotal"
                   class="w-full border p-2 rounded" required>
            <div v-if="form.errors.cantidad" class="text-red-600 text-sm">
              {{ form.errors.cantidad }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium">Precio unitario</label>
            <input type="number" step="0.01" min="0" v-model.number="form.precio_unitario" @input="calcularTotal"
                   class="w-full border p-2 rounded" required>
            <div v-if="form.errors.precio_unitario" class="text-red-600 text-sm">
              {{ form.errors.precio_unitario }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium">Total</label>
            <input type="number" step="0.01" min="0" v-model.number="form.total"
                   class="w-full border p-2 rounded bg-gray-50" readonly>
          </div>
        </div>

        <!-- Fecha -->
        <div>
          <label class="block text-sm font-medium">Fecha</label>
          <input type="date" v-model="form.fecha" class="w-full border p-2 rounded" required>
          <div v-if="form.errors.fecha" class="text-red-600 text-sm">{{ form.errors.fecha }}</div>
        </div>

        <!-- Observación -->
        <div>
          <label class="block text-sm font-medium">Observación</label>
          <textarea v-model="form.observacion" class="w-full border p-2 rounded"></textarea>
          <div v-if="form.errors.observacion" class="text-red-600 text-sm">{{ form.errors.observacion }}</div>
        </div>

        <!-- Botones -->
        <div class="flex gap-2 mt-4">
          <button type="submit" class="bg-[#2E7D32] text-white px-4 py-2 rounded hover:bg-green-700 transition">
            {{ props.consumo ? 'Actualizar' : 'Guardar' }}
          </button>
          <a href="/recepcionista/consumos" class="px-4 py-2 border rounded hover:bg-gray-100 transition">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </RecepcionistaLayout>
</template>
