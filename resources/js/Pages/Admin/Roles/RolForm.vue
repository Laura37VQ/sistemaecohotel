<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  rol: { type: Object, default: null }
})

const form = useForm({
  id: props.rol?.id || null,
  nombre_rol: props.rol?.nombre_rol || ''
})

function submit() {
  if (props.rol) {
    form.put(`/admin/roles/${props.rol.id}`)
  } else {
    form.post('/admin/roles')
  }
}
</script>

<template>
  <Head :title="props.rol ? 'Editar Rol' : 'Crear Rol'" />
  <AdminLayout>
    <div class="p-6 max-w-lg mx-auto bg-white rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-[#2E7D32]">
        {{ props.rol ? 'Editar Rol' : 'Crear Rol' }}
      </h2>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium">Nombre del Rol</label>
          <input v-model="form.nombre_rol" type="text" class="w-full border p-2 rounded shadow-sm"/>
          <div v-if="form.errors.nombre_rol" class="text-red-600 text-sm">{{ form.errors.nombre_rol }}</div>
        </div>

        <div class="flex gap-2 mt-4">
          <button type="submit" class="bg-[#2E7D32] text-white px-4 py-2 rounded shadow hover:bg-green-700 transition">
            {{ props.rol ? 'Actualizar' : 'Guardar' }}
          </button>
          <a href="/admin/roles" class="px-4 py-2 border rounded shadow hover:bg-gray-100 transition">Cancelar</a>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
