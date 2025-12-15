<script setup>
import ClienteLayout from '@/layouts/ClienteLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
  reserva: Object
})

const form = useForm({
  fecha_ingreso: props.reserva.fecha_ingreso,
  fecha_salida: props.reserva.fecha_salida,
  descripcion: props.reserva.descripcion || ''
})

function actualizar() {
  form.put(`/cliente/reservas/${props.reserva.id}`, {
    onSuccess: () => alert('Reserva actualizada correctamente.')
  })
}
</script>

<template>
  <Head title="Editar Reserva" />
  <ClienteLayout>
    <div class="min-h-screen bg-[#E8EFEB] py-12">
      <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-lg px-8 py-10">
        <h2 class="text-3xl font-bold text-[#2E7D32] mb-8 text-center">
          Editar Reserva
        </h2>
        <p class="text-gray-600 text-center mb-8">
          Desde esta pantalla puedes modificar las fechas de tu reserva y agregar observaciones adicionales.
        </p>

        <form @submit.prevent="actualizar" class="space-y-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Habitación</label>
            <input
              type="text"
              :value="reserva.habitacion.tipo"
              disabled
              class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-600"
            />
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha ingreso</label>
              <input
                v-model="form.fecha_ingreso"
                type="date"
                class="w-full border rounded-lg px-3 py-2"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha salida</label>
              <input
                v-model="form.fecha_salida"
                type="date"
                class="w-full border rounded-lg px-3 py-2"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción / observaciones</label>
            <textarea
              v-model="form.descripcion"
              rows="3"
              class="w-full border rounded-lg px-3 py-2"
            ></textarea>
          </div>

          <div class="flex justify-between items-center mt-6">
            <Link
              href="/cliente/reservas"
              class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition"
            >
              Cancelar
            </Link>

            <button
              type="submit"
              class="bg-[#2E7D32] text-white px-6 py-2 rounded-lg hover:bg-green-800 transition"
              :disabled="form.processing"
            >
              Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </ClienteLayout>
</template>
