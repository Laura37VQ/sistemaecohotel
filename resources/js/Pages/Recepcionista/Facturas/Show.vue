<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const props = defineProps({
  factura: Object,
  detalles: Array,
  servicios: Array
})

const form = useForm({
  servicio_id: '',
  cantidad: 1,
  precio_unitario: ''
})

//  Estado de alertas
const showAlert = ref(false)
const alertMessage = ref('')
const alertType = ref('success')

//  Captura mensajes flash
const page = usePage()
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success || flash?.error) {
      alertMessage.value = flash.success || flash.error
      alertType.value = flash.success ? 'success' : 'error'
      showAlert.value = true
      setTimeout(() => (showAlert.value = false), 3500)
    }
  },
  { deep: true }
)

//  Autoasignar precio al cambiar servicio
watch(
  () => form.servicio_id,
  (nuevoId) => {
    const seleccionado = props.servicios.find(s => s.id === Number(nuevoId))
    if (seleccionado) {
      form.precio_unitario = seleccionado.precio
    } else {
      form.precio_unitario = ''
    }
  }
)

//  Calcular subtotal dinámico
const subtotalParcial = computed(() => {
  const precio = Number(form.precio_unitario) || 0
  const cantidad = Number(form.cantidad) || 0
  return precio * cantidad
})

//  Agregar detalle
function agregarDetalle() {
  if (!form.servicio_id || !form.precio_unitario) {
    alertMessage.value = 'Debe seleccionar un servicio y un precio válido.'
    alertType.value = 'error'
    showAlert.value = true
    setTimeout(() => (showAlert.value = false), 3500)
    return
  }

  form.post(`/recepcionista/facturas/${props.factura.id}/detalles`, {
    preserveScroll: true,
    onSuccess: () => form.reset()
  })
}

//  Eliminar detalle
function eliminarDetalle(id) {
  if (!confirm('¿Eliminar este ítem de la factura?')) return
  router.delete(`/recepcionista/facturas/detalles/${id}`, {
    preserveScroll: true
  })
}
</script>

<template>
  <Head :title="`Factura ${factura.prefijo}-${factura.numero_factura}`" />
  <RecepcionistaLayout>
    <div class="p-8 max-w-4xl mx-auto bg-white shadow rounded-2xl relative">

      <!--  ALERTA -->
      <transition name="fade">
        <div
          v-if="showAlert"
          class="absolute top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-lg text-white font-semibold shadow-lg"
          :class="{
            'bg-green-600': alertType === 'success',
            'bg-red-600': alertType === 'error'
          }"
        >
          {{ alertMessage }}
        </div>
      </transition>

      <!--  Encabezado -->
      <div class="border-b pb-4 mb-4 text-center">
        <h1 class="text-xl font-bold text-gray-800">ECO HOTEL VILLA DEL SOL</h1>
        <p class="text-sm text-gray-600">
          Factura No. {{ factura.prefijo }}-{{ factura.numero_factura }}
        </p>
      </div>

      <!--  Botón PDF -->
      <div class="text-right mb-6 flex justify-end gap-3">
        <a
          :href="`/recepcionista/facturas/${factura.id}/pdf`"
          target="_blank"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition"
        >
          Descargar PDF
        </a>
      </div>

      <!--  Info cliente -->
      <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
        <div>
          <p><strong>Cliente:</strong> {{ factura.cliente.nombres }} {{ factura.cliente.apellidos }}</p>
          <p><strong>Fecha:</strong> {{ new Date(factura.fecha_emision).toLocaleDateString() }}</p>
        </div>
        <div>
          <p><strong>Método de pago:</strong> {{ factura.metodo_pago }}</p>
          <p><strong>Estado:</strong> {{ factura.estado }}</p>
        </div>
      </div>

      <!--  Tabla de detalles -->
      <table class="min-w-full border border-gray-300 text-sm mb-6">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 border">Servicio</th>
            <th class="px-4 py-2 border text-center">Cantidad</th>
            <th class="px-4 py-2 border text-right">Precio</th>
            <th class="px-4 py-2 border text-right">Subtotal</th>
            <th class="px-4 py-2 border text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="d in detalles" :key="d.id">
            <td class="border px-4 py-2">{{ d.servicio?.nombre ?? d.descripcion }}</td>
            <td class="border px-4 py-2 text-center">{{ d.cantidad }}</td>
            <td class="border px-4 py-2 text-right">${{ d.precio_unitario.toLocaleString() }}</td>
            <td class="border px-4 py-2 text-right">
              ${{ (d.cantidad * d.precio_unitario).toLocaleString() }}
            </td>
            <td class="border px-4 py-2 text-center">
              <button
                @click="eliminarDetalle(d.id)"
                class="text-red-600 hover:underline text-sm"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!--  Agregar servicio -->
      <form @submit.prevent="agregarDetalle" class="mt-6 flex flex-wrap items-end gap-3">
        <!-- Servicio -->
        <div class="flex flex-col">
          <label class="text-xs text-gray-600 mb-1">Servicio</label>
          <select
            v-model="form.servicio_id"
            class="border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500"
          >
            <option value="">Seleccione servicio</option>
            <option v-for="s in servicios" :key="s.id" :value="s.id">
              {{ s.nombre }}
            </option>
          </select>
        </div>

        <!-- Cantidad -->
        <div class="flex flex-col">
          <label class="text-xs text-gray-600 mb-1">Cantidad</label>
          <input
            type="number"
            v-model="form.cantidad"
            min="1"
            class="border-gray-300 rounded-xl w-20 text-center focus:ring-green-500 focus:border-green-500"
          />
        </div>

        <!-- Precio -->
        <div class="flex flex-col">
          <label class="text-xs text-gray-600 mb-1">Precio unitario</label>
          <input
            type="number"
            v-model="form.precio_unitario"
            min="0"
            step="0.01"
            class="border-gray-300 rounded-xl w-28 bg-gray-100 text-gray-700 text-right"
            readonly
          />
        </div>

        <!-- Subtotal dinámico -->
        <div class="flex flex-col">
          <label class="text-xs text-gray-600 mb-1">Subtotal</label>
          <input
            type="text"
            :value="subtotalParcial.toLocaleString('es-CO', { style: 'currency', currency: 'COP' })"
            class="border-gray-300 rounded-xl w-36 bg-gray-100 text-gray-700 text-right"
            readonly
          />
        </div>

        <!-- Botón agregar -->
        <button
          type="submit"
          class="px-5 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition h-10"
        >
          Agregar
        </button>
      </form>

      <!--  Totales -->
      <div class="mt-8 text-right text-gray-800 text-sm">
        <p><strong>Subtotal:</strong> ${{ factura.subtotal.toLocaleString() }}</p>
        <p><strong>IVA (19%):</strong> ${{ factura.impuestos.toLocaleString() }}</p>
        <p class="text-lg font-bold">TOTAL: ${{ factura.total.toLocaleString() }}</p>
      </div>
    </div>
  </RecepcionistaLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
