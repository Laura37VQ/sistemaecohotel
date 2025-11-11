<script setup>
import ClienteLayout from '@/layouts/ClienteLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

//  Props recibidas desde Laravel
const props = defineProps({
  reserva: Object,
  servicios: Array,
  error: String
})

//  Arreglo reactivo con los servicios seleccionados
const seleccionados = ref([])

//  Verifica si un servicio está seleccionado
function estaSeleccionado(servicio) {
  return seleccionados.value.some((s) => s.servicio_id === servicio.id)
}

//  Agregar o quitar un servicio del carrito
function toggleServicio(servicio) {
  const index = seleccionados.value.findIndex((s) => s.servicio_id === servicio.id)
  if (index >= 0) {
    seleccionados.value.splice(index, 1)
  } else {
    seleccionados.value.push({ servicio_id: servicio.id, cantidad: 1 })
  }
}

//  Cambiar cantidad del servicio
function cambiarCantidad(servicio, valor) {
  const item = seleccionados.value.find((s) => s.servicio_id === servicio.id)
  if (item) {
    const nueva = item.cantidad + valor
    if (nueva >= 1) item.cantidad = nueva
  }
}

//  Calcular el total seleccionado
const totalSeleccionado = computed(() => {
  return seleccionados.value.reduce((acc, item) => {
    const serv = props.servicios.find((s) => s.id === item.servicio_id)
    return serv ? acc + serv.precio * item.cantidad : acc
  }, 0)
})

//  Generar o actualizar factura
function generarFactura() {
  if (!props.reserva) {
    alert('No hay una reserva válida para generar factura.')
    return
  }

  router.post(
    `/cliente/servicios/${props.reserva.id}`,
    { items: seleccionados.value },
    {
      onSuccess: () => {
        alert('Factura generada o actualizada correctamente.')
        router.visit('/cliente/facturacion')
      },
      onError: (error) => {
        console.error(error)
        alert('Ocurrió un error al generar la factura.')
      }
    }
  )
}
</script>

<template>
  <Head title="Seleccionar servicios" />
  <ClienteLayout>
    <div class="max-w-6xl mx-auto py-10 px-6">
      <h2 class="text-3xl font-bold text-[#2E7D32] text-center mb-10">
        Servicios adicionales disponibles 
      </h2>

      <!--  Si hay error -->
      <div v-if="error" class="text-center bg-red-50 border border-red-200 text-red-700 p-6 rounded-2xl shadow-sm">
        <p class="font-semibold mb-3">{{ error }}</p>
        <a
          href="/cliente/disponibilidad"
          class="bg-[#2E7D32] text-white px-6 py-2 rounded-lg hover:bg-green-800 transition shadow"
        >
          Ir a crear una reserva
        </a>
      </div>

      <!--  Si hay reserva válida -->
      <div v-else>
        <!--  Alerta de reserva activa -->
        <div
          v-if="reserva"
          class="bg-green-50 border border-green-200 text-green-700 p-5 rounded-2xl mb-8 text-center shadow-sm"
        >
          <p class="font-semibold text-lg">
             Reserva confirmada: <span class="font-bold">{{ reserva.codigo_reserva }}</span>
          </p>
          <p class="text-sm text-green-800">
            Ahora puedes añadir servicios adicionales para mejorar tu estadía.
          </p>
        </div>

        <!-- Si no hay servicios -->
        <div v-if="!servicios.length" class="text-center text-gray-500 italic">
          No hay servicios disponibles en este momento.
        </div>

        <!-- Lista de servicios -->
        <div v-else class="grid md:grid-cols-3 gap-6">
          <div
            v-for="serv in servicios"
            :key="serv.id"
            class="bg-white border rounded-2xl overflow-hidden shadow hover:shadow-lg transition relative flex flex-col"
            :class="estaSeleccionado(serv) ? 'border-[#2E7D32] ring-2 ring-green-200' : 'border-gray-200'"
          >
            <img
              v-if="serv.foto"
              :src="'/storage/' + serv.foto"
              alt="Servicio"
              class="h-44 w-full object-cover"
            />

            <div class="p-4 flex-1 flex flex-col justify-between">
              <div>
                <h3 class="text-lg font-semibold text-[#2E7D32]">{{ serv.nombre }}</h3>
                <p class="text-gray-600 text-sm mt-1">{{ serv.descripcion }}</p>
                <p class="text-[#2E7D32] font-bold mt-3">$ {{ serv.precio.toLocaleString() }}</p>
              </div>

              <div class="mt-4 flex justify-between items-center">
                <!-- Control de cantidad -->
                <div v-if="estaSeleccionado(serv)" class="flex items-center space-x-2">
                  <button
                    @click="cambiarCantidad(serv, -1)"
                    class="bg-green-100 text-green-800 px-2 rounded hover:bg-green-200"
                  >−</button>

                  <input
                    v-model="seleccionados.find(s => s.servicio_id === serv.id).cantidad"
                    type="number"
                    min="1"
                    class="w-14 border border-gray-300 rounded text-center"
                  />

                  <button
                    @click="cambiarCantidad(serv, 1)"
                    class="bg-green-100 text-green-800 px-2 rounded hover:bg-green-200"
                  >+</button>
                </div>

                <!-- Botón agregar/quitar -->
                <button
                  @click="toggleServicio(serv)"
                  class="px-3 py-1 rounded-lg font-semibold transition"
                  :class="estaSeleccionado(serv)
                    ? 'bg-red-500 text-white hover:bg-red-600'
                    : 'bg-[#2E7D32] text-white hover:bg-green-800'"
                >
                  {{ estaSeleccionado(serv) ? 'Quitar' : 'Agregar' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Total y botón de factura -->
        <div v-if="servicios.length" class="text-center mt-10">
          <p v-if="totalSeleccionado > 0" class="text-lg text-gray-700 mb-4">
            Total seleccionado:
            <span class="font-bold text-[#2E7D32]">
              $ {{ totalSeleccionado.toLocaleString() }}
            </span>
          </p>

          <button
            @click="generarFactura"
            class="bg-[#2E7D32] text-white px-8 py-3 rounded-xl text-lg hover:bg-green-800 transition shadow"
          >
            {{ seleccionados.length > 0 ? 'Generar / Actualizar factura' : 'Generar factura' }}
          </button>
        </div>
      </div>
    </div>
  </ClienteLayout>
</template>
