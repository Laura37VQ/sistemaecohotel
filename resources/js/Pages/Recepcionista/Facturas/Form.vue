<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  factura: Object,
  clientes: Array,
  reservas: Array
})

const form = useForm({
  numero_factura: props.factura?.numero_factura || '',
  prefijo: props.factura?.prefijo || 'FV',
  cliente_id: props.factura?.cliente_id || '',
  reserva_id: props.factura?.reserva_id || '',
  fecha_emision: props.factura?.fecha_emision || new Date().toISOString().slice(0, 10),
  metodo_pago: props.factura?.metodo_pago || '',
  estado: props.factura?.estado || 'Pendiente',
  observaciones: props.factura?.observaciones || '',
  subtotal: props.factura?.subtotal || 0,
  impuestos: props.factura?.impuestos || 0,
  total: props.factura?.total || 0
})

const submit = () => {
  if (props.factura) form.put(`/recepcionista/facturas/${props.factura.id}`)
  else form.post('/recepcionista/facturas')
}
</script>

<template>
  <Head :title="props.factura ? 'Editar Factura' : 'Nueva Factura'" />
  <RecepcionistaLayout>
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-2xl shadow">
      <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        {{ props.factura ? 'Editar Factura' : 'Nueva Factura' }}
      </h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium text-gray-700">Número de factura</label>
            <input v-model="form.numero_factura" type="text" class="w-full border-gray-300 rounded-xl" />
          </div>

          <div>
            <label class="block font-medium text-gray-700">Prefijo</label>
            <input v-model="form.prefijo" type="text" maxlength="5" class="w-full border-gray-300 rounded-xl" />
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700">Cliente</label>
          <select v-model="form.cliente_id" class="w-full border-gray-300 rounded-xl">
            <option value="">Seleccione un cliente</option>
            <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombres }} {{ c.apellidos }}</option>
          </select>
          <div v-if="form.errors.cliente_id" class="text-red-600 text-sm mt-1">{{ form.errors.cliente_id }}</div>
        </div>

        <div>
          <label class="block font-medium text-gray-700">Reserva asociada</label>
          <select v-model="form.reserva_id" class="w-full border-gray-300 rounded-xl">
            <option value="">Seleccione una reserva</option>
            <option v-for="r in reservas" :key="r.id" :value="r.id">{{ r.codigo_reserva }}</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium text-gray-700">Fecha de emisión</label>
            <input v-model="form.fecha_emision" type="date" class="w-full border-gray-300 rounded-xl" />
          </div>

          <div>
            <label class="block font-medium text-gray-700">Método de pago</label>
            <select v-model="form.metodo_pago" class="w-full border-gray-300 rounded-xl">
              <option value="">Seleccione</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Tarjeta">Tarjeta</option>
              <option value="Transferencia">Transferencia</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700">Estado</label>
          <select v-model="form.estado" class="w-full border-gray-300 rounded-xl">
            <option value="Pendiente">Pendiente</option>
            <option value="Pagada">Pagada</option>
            <option value="Anulada">Anulada</option>
          </select>
        </div>

        <div>
          <label class="block font-medium text-gray-700">Observaciones</label>
          <textarea v-model="form.observaciones" rows="3" class="w-full border-gray-300 rounded-xl" />
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block font-medium text-gray-700">Subtotal</label>
            <input v-model="form.subtotal" type="number" step="0.01" class="w-full border-gray-300 rounded-xl" />
          </div>
          <div>
            <label class="block font-medium text-gray-700">Impuestos</label>
            <input v-model="form.impuestos" type="number" step="0.01" class="w-full border-gray-300 rounded-xl" />
          </div>
          <div>
            <label class="block font-medium text-gray-700">Total</label>
            <input v-model="form.total" type="number" step="0.01" class="w-full border-gray-300 rounded-xl" />
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700">
            {{ props.factura ? 'Actualizar' : 'Guardar' }}
          </button>
        </div>
      </form>
    </div>
  </RecepcionistaLayout>
</template>
