<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  info: Object
})

const previewLogo = ref(props.info?.logo ? `/storage/${props.info.logo}` : null)

const form = useForm({
  nombre: props.info?.nombre || '',
  nit: props.info?.nit || '',
  regimen_tributario: props.info?.regimen_tributario || '',
  direccion: props.info?.direccion || '',
  telefono: props.info?.telefono || '',
  email: props.info?.email || '',
  ciudad: props.info?.ciudad || '',
  pais: props.info?.pais || '',
  actividad_economica: props.info?.actividad_economica || '',
  mision: props.info?.mision || '',
  vision: props.info?.vision || '', 
  logo: null
})

const handleLogoChange = (e) => {
  const file = e.target.files[0]
  form.logo = file
  if (file) {
    const reader = new FileReader()
    reader.onload = (event) => {
      previewLogo.value = event.target.result
    }
    reader.readAsDataURL(file)
  } else if (props.info?.logo) {
    previewLogo.value = `/storage/${props.info.logo}`
  } else {
    previewLogo.value = null
  }
}

const submit = () => {
  form.post('/admin/informacion-hotel/save', {
    forceFormData: true
  })
}
</script>

<template>
  <Head :title="props.info ? 'Editar Información del Hotel' : 'Registrar Información del Hotel'" />
  <AdminLayout>
    <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
      <h2 class="text-2xl font-bold mb-6 text-green-700">
        {{ props.info ? 'Editar Información del Hotel' : 'Registrar Información del Hotel' }}
      </h2>

      <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
        <!-- Campos obligatorios -->
        <div v-for="field in [
          {label: 'Nombre del Hotel', model: 'nombre', type: 'text'},
          {label: 'NIT', model: 'nit', type: 'text'},
          {label: 'Régimen Tributario', model: 'regimen_tributario', type: 'text'},
          {label: 'Dirección', model: 'direccion', type: 'text'},
          {label: 'Teléfono', model: 'telefono', type: 'text'},
          {label: 'Email', model: 'email', type: 'email'},
          {label: 'Ciudad', model: 'ciudad', type: 'text'},
          {label: 'País', model: 'pais', type: 'text'},
          {label: 'Actividad Económica', model: 'actividad_economica', type: 'text'}
        ]" :key="field.model">
          <label class="block font-medium">{{ field.label }}</label>
          <input v-model="form[field.model]" :type="field.type" class="border rounded p-2 w-full" required />
          <div v-if="form.errors[field.model]" class="text-red-500 text-sm">{{ form.errors[field.model] }}</div>
        </div>

        <!-- Logo -->
        <div>
          <label class="block font-medium">Logo del Hotel</label>
          <input type="file" @change="handleLogoChange" class="border rounded p-2 w-full" :required="!props.info?.logo" />
          <div v-if="form.errors.logo" class="text-red-500 text-sm">{{ form.errors.logo }}</div>

          <div v-if="previewLogo" class="mt-3">
            <p class="text-sm text-gray-600">Vista previa del logo:</p>
            <img :src="previewLogo" alt="Logo" class="h-20 mt-1 rounded" />
          </div>
        </div>

        <!-- Misión -->
        <div>
          <label class="block font-medium">Misión</label>
          <textarea v-model="form.mision" rows="3" class="border rounded p-2 w-full"></textarea>
          <div v-if="form.errors.mision" class="text-red-500 text-sm">{{ form.errors.mision }}</div>
        </div>
        
        <!-- Visión -->
        <div>
          <label class="block font-medium">Visión</label>
          <textarea v-model="form.vision" rows="3" class="border rounded p-2 w-full"></textarea>
          <div v-if="form.errors.vision" class="text-red-500 text-sm">{{ form.errors.vision }}</div>
        </div>

        <!-- Botones -->
        <div class="flex justify-between items-center mt-6">
          <Link href="/admin/informacion-hotel" class="text-gray-600 hover:underline">← Volver</Link>
          <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" :disabled="form.processing">
            {{ props.info ? 'Actualizar' : 'Registrar' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
