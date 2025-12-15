<script setup>
import ClienteLayout from '@/layouts/ClienteLayout.vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

// Acceder a los props actuales de Inertia (vienen desde Laravel)
const page = usePage()
const props = page.props

// Formulario reactivo
const form = useForm({
  fecha_ingreso: props.form?.fecha_ingreso || '',
  fecha_salida: props.form?.fecha_salida || ''
})

// Habitaciones reactivas
const habitaciones = ref(props.habitaciones || [])

//  
watch(
  () => page.props.habitaciones,
  (nuevas) => {
    habitaciones.value = nuevas
  }
)

// Buscar disponibilidad (recarga la misma vista con resultados)
function buscar() {
  router.post('/cliente/buscar-disponibilidad', form)
}
</script>

<template>
  <Head title="Consultar disponibilidad" />
  <ClienteLayout>
    <div class="max-w-6xl mx-auto py-10 px-6">
      <!-- Encabezado -->
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Consultar disponibilidad</h2>
        <p class="text-gray-600 mt-2">
          Ingresa tus fechas para ver las habitaciones disponibles.
        </p>
      </div>
      <p class="text-sm text-gray-500 mt-3">
        Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
      </p>

      <!-- Formulario -->
      <form
        @submit.prevent="buscar"
        class="grid md:grid-cols-2 gap-6 bg-white p-6 rounded-2xl shadow"
      >
        <div>
          <label class="block font-semibold text-gray-700 mb-1">
            Fecha de ingreso <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.fecha_ingreso"
            type="date"
            class="border border-gray-300 rounded-lg w-full p-2 focus:outline-none focus:ring-2 focus:ring-[#2E7D32]"
            required
          />
          <small class="text-gray-500">
            Seleccione la fecha en la que desea ingresar al hotel.
          </small>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 mb-1">
            Fecha de salida <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.fecha_salida"
            type="date"
            class="border border-gray-300 rounded-lg w-full p-2 focus:outline-none focus:ring-2 focus:ring-[#2E7D32]"
            required
          />
        </div>

        <div class="col-span-2 flex justify-center">
          <button
            type="submit"
            class="bg-[#FFCE3E] text-black font-semibold px-6 py-2 rounded-lg hover:bg-[#f1c232] transition shadow"
          >
            Buscar habitaciones
          </button>
        </div>
      </form>

      <!-- Resultados -->
      <div
        v-if="habitaciones && habitaciones.length"
        class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3"
      >
        <div
          v-for="h in habitaciones"
          :key="h.id"
          class="flex flex-col bg-white border border-gray-200 rounded-2xl shadow hover:shadow-lg overflow-hidden transition h-[520px]"
        >
          <img
            v-if="h.foto"
            :src="'/storage/' + h.foto"
            alt="Habitación"
            class="w-full h-48 object-cover"
          />
          <div class="flex flex-col justify-between p-4 flex-1">
            <div>
              <h3 class="text-xl font-semibold text-[#2E7D32]">{{ h.tipo }}</h3>
              <p class="text-gray-600 mt-1 text-sm leading-relaxed line-clamp-6">
                {{ h.descripcion }}
              </p>
              <p class="mt-2 text-lg font-bold text-[#2E7D32]">
                $ {{ Number(h.precio).toLocaleString() }}
              </p>
            </div>

            <button
              @click="$inertia.visit(`/cliente/reservar/${h.id}`)"
              class="mt-6 w-full bg-[#2E7D32] text-white py-2 rounded-lg hover:bg-green-800 transition"
            >
              Reservar
            </button>
          </div>
        </div>
      </div>

      <!-- Sin resultados -->
      <div v-else class="mt-10 text-center text-gray-500 italic">
        No se encontraron habitaciones disponibles para el rango de fechas seleccionado. Intente modificar las fechas de búsqueda.
      </div>
    </div>
  </ClienteLayout>
</template>

<style scoped>
.line-clamp-6 {
  display: -webkit-box;
  -webkit-line-clamp: 6;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 6; 
}
</style>
