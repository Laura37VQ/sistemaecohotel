<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const props = defineProps({
  cliente: { type: Object, default: null }
})

const editando = computed(() => !!props.cliente)

const form = useForm({
  nombres: props.cliente?.nombres ?? '',
  apellidos: props.cliente?.apellidos ?? '',
  documento_identidad: props.cliente?.documento_identidad ?? '',
  fecha_nacimiento: props.cliente?.fecha_nacimiento ?? '',
  correo: props.cliente?.correo ?? '',
  telefono: props.cliente?.telefono ?? '',
  direccion: props.cliente?.direccion ?? '',
  nombre_usuario: props.cliente?.nombre_usuario ?? '',
  contrasena: '' // 
})

watch(
  () => props.cliente,
  (nuevo) => {
    if (!nuevo) return
    form.nombres = nuevo.nombres ?? ''
    form.apellidos = nuevo.apellidos ?? ''
    form.documento_identidad = nuevo.documento_identidad ?? ''
    form.fecha_nacimiento = nuevo.fecha_nacimiento ?? '' // 
    form.correo = nuevo.correo ?? ''
    form.telefono = nuevo.telefono ?? ''
    form.direccion = nuevo.direccion ?? ''
    form.nombre_usuario = nuevo.nombre_usuario ?? ''
    form.contrasena = ''
  },
  { immediate: true }
)

function submit() {
  if (editando.value) {
    form.put(`/recepcionista/clientes/${props.cliente.id}`, {
      onError: (e) => {
        const first = Object.values(e)[0]
        if (first) alert(first)
      },
      onSuccess: () => {
        alert('Cliente actualizado correctamente.')
        window.location.href = '/recepcionista/clientes'
      }
    })
  } else {
    form.post('/recepcionista/clientes', {
      onError: (e) => {
        const first = Object.values(e)[0]
        if (first) alert(first)
      },
      onSuccess: () => {
        alert('Cliente creado correctamente.')
        window.location.href = '/recepcionista/clientes'
      }
    })
  }
}
</script>

<template>
  <Head :title="editando ? 'Editar cliente' : 'Nuevo cliente'" />

  <RecepcionistaLayout>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded-lg shadow">
      <h2 class="text-2xl font-bold mb-4 text-[#2E7D32]">
        {{ editando ? 'Editar cliente' : 'Nuevo cliente' }}
      </h2>
      <p class="text-sm text-gray-500 mb-4">
        Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
      </p>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium">
              Nombres <span class="text-red-500">*</span>
            </label>
            <input
            v-model="form.nombres"
            type="text"
            required
            class="w-full border p-2 rounded"
            />
            <input v-model="form.nombres" type="text" class="w-full border p-2 rounded" />
            <div v-if="form.errors.nombres" class="text-red-600 text-sm">{{ form.errors.nombres }}</div>
          </div>

          <div>
             <label class="block text-sm font-medium">
              Apellidos <span class="text-red-500">*</span>
            </label>
            <input v-model="form.apellidos" type="text" class="w-full border p-2 rounded" />
            <div v-if="form.errors.apellidos" class="text-red-600 text-sm">{{ form.errors.apellidos }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium">
              Documento <span class="text-red-500">*</span>
            </label>
            <input v-model="form.documento_identidad" type="text" class="w-full border p-2 rounded" />
            <small class="text-gray-500">
              Ingrese solo números, sin puntos ni espacios.
            </small>
            <div v-if="form.errors.documento_identidad" class="text-red-600 text-sm">{{ form.errors.documento_identidad }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium">
              Fecha nacimiento <span class="text-red-500">*</span>
            </label>
            <input v-model="form.fecha_nacimiento" type="date" class="w-full border p-2 rounded" />
            <small class="text-gray-500">
              Ejemplo: correo@ejemplo.com
            </small>
            <div v-if="form.errors.fecha_nacimiento" class="text-red-600 text-sm">{{ form.errors.fecha_nacimiento }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium">
              Correo <span class="text-red-500">*</span>
            </label>
            <input v-model="form.correo" type="email" class="w-full border p-2 rounded" />
            <div v-if="form.errors.correo" class="text-red-600 text-sm">{{ form.errors.correo }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium">
              Teléfono <span class="text-red-500">*</span>
            </label>
            <input v-model="form.telefono" type="text" class="w-full border p-2 rounded" />
            <div v-if="form.errors.telefono" class="text-red-600 text-sm">{{ form.errors.telefono }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium">
              Dirección <span class="text-red-500">*</span>
            </label>
            <input v-model="form.direccion" type="text" class="w-full border p-2 rounded" />
            <div v-if="form.errors.direccion" class="text-red-600 text-sm">{{ form.errors.direccion }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium">
              Nombre de usuario <span class="text-red-500">*</span>
            </label>
            <input v-model="form.nombre_usuario" type="text" class="w-full border p-2 rounded" />
            <div v-if="form.errors.nombre_usuario" class="text-red-600 text-sm">{{ form.errors.nombre_usuario }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium">
              {{ editando ? 'Nueva contraseña (opcional)' : 'Contraseña (opcional)' }}
            </label>
            <input v-model="form.contrasena" type="password" class="w-full border p-2 rounded" />
            <div v-if="form.errors.contrasena" class="text-red-600 text-sm">{{ form.errors.contrasena }}</div>
          </div>
        </div>

        <div class="flex gap-2 mt-4">
          <button type="submit" class="bg-[#2E7D32] text-white px-4 py-2 rounded hover:bg-green-700 transition">
            {{ editando ? 'Actualizar' : 'Guardar' }}
          </button>
          <a href="/recepcionista/clientes" class="px-4 py-2 border rounded hover:bg-gray-100 transition">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </RecepcionistaLayout>
</template>
