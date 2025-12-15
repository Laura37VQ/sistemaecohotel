<script setup>
import ClienteLayout from '@/layouts/ClienteLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  habitacion: Object
})

// Formulario reactivo
const form = useForm({
  habitacion_id: props.habitacion.id,
  fecha_ingreso: '',
  fecha_salida: '',
  descripcion: ''
})

// Enviar reserva
function guardarReserva() {
  form.post('/cliente/reservar', {
    onSuccess: () => {
      alert('Reserva creada correctamente. Ahora puedes agregar servicios adicionales.')
    }
  })
}
</script>

<template>
  <Head title="Reservar habitación" />
  <ClienteLayout>
    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
      <!-- Imagen principal -->
      <img
        v-if="habitacion.foto"
        :src="'/storage/' + habitacion.foto"
        alt="Habitación seleccionada"
        class="w-full h-72 object-cover"
      />

      <div class="p-8">
        <!-- Encabezado -->
        <h2 class="text-3xl font-bold text-[#2E7D32]">{{ habitacion.tipo }}</h2>
        <p class="text-gray-600 mt-2 leading-relaxed">{{ habitacion.descripcion }}</p>
        <p class="text-[#2E7D32] font-bold mt-3 text-lg">
          $ {{ Number(habitacion.precio).toLocaleString() }} / noche
        </p>
        <p class="text-sm text-gray-500 mt-4">
          Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
        </p>

        <!-- Formulario -->
        <form @submit.prevent="guardarReserva" class="mt-8 space-y-5">
          <!-- Fechas -->
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-gray-700 font-medium mb-1">
                Fecha de ingreso <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.fecha_ingreso"
                type="date"
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#2E7D32] focus:outline-none"
                required
              />
              <small class="text-gray-500">
                Seleccione la fecha en la que desea ingresar al hotel.
              </small>
            </div>

            <div>
              <label class="block text-gray-700 font-medium mb-1">
                Fecha de salida <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.fecha_salida"
                type="date"
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#2E7D32] focus:outline-none"
                required
              />
            </div>
          </div>

          <!-- Observaciones -->
          <div>
            <label class="block text-gray-700 font-medium mb-1">
              Observaciones (opcional)
            </label>
            <textarea
              v-model="form.descripcion"
              rows="3"
              class="w-full border border-gray-300 rounded-lg p-2 resize-none focus:ring-2 focus:ring-[#2E7D32] focus:outline-none"
              placeholder="Escribe alguna solicitud especial..."
            ></textarea>
          </div>

          <!-- Botón -->
          <div class="pt-4 flex justify-end">
            <button
              type="submit"
              class="bg-[#2E7D32] text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-800 transition shadow-md"
              :disabled="form.processing"
            >
              Confirmar reserva
            </button>
          </div>
        </form>
      </div>
    </div>
  </ClienteLayout>
</template>
