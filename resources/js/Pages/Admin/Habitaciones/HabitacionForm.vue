<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch, reactive } from 'vue'

// Recibimos la habitación si viene de editar
const props = defineProps({
  habitacion: {
    type: Object,
    default: null
  }
})

// Vista previa de la foto nueva
const preview = ref(null)

// Inicializamos el formulario
const form = useForm({
  id: props.habitacion?.id || null,
  numero_habitacion: props.habitacion?.numero_habitacion || '',
  tipo: props.habitacion?.tipo || 'Individual',
  capacidad_personas: props.habitacion?.capacidad_personas || null,
  precio: props.habitacion?.precio || null,
  estado: props.habitacion?.estado || 'Disponible',
  descripcion: props.habitacion?.descripcion || '',
  foto: null
})

// Errores de validación local
const localErrors = reactive({
  numero_habitacion: '',
  capacidad_personas: '',
  precio: '',
  descripcion: '',
  foto: ''   // ← AÑADIDO
})

// Actualizar form si cambian las props (editar)
watch(
  () => props.habitacion,
  (newVal) => {
    if (newVal) {
      form.id = newVal.id
      form.numero_habitacion = newVal.numero_habitacion || ''
      form.tipo = newVal.tipo
      form.capacidad_personas = newVal.capacidad_personas || null
      form.precio = newVal.precio || null
      form.estado = newVal.estado
      form.descripcion = newVal.descripcion || ''
    }
  },
  { immediate: true }
)

// Limpiar errores al escribir
watch(() => form.numero_habitacion, val => { if (val.trim()) localErrors.numero_habitacion = '' })
watch(() => form.capacidad_personas, val => { if (val) localErrors.capacidad_personas = '' })
watch(() => form.precio, val => { if (val) localErrors.precio = '' })
watch(() => form.descripcion, val => { if (val.trim()) localErrors.descripcion = '' })

// Manejar subida de foto
function handleFileChange(e) {
  const file = e.target.files[0]
  form.foto = file
  preview.value = file ? URL.createObjectURL(file) : null
  localErrors.foto = ''   // ← limpia error
}

// Validación local
function validateForm() {
  localErrors.numero_habitacion = form.numero_habitacion.trim() ? '' : 'El número de habitación es obligatorio'
  localErrors.capacidad_personas = form.capacidad_personas ? '' : 'La capacidad es obligatoria'
  localErrors.precio = form.precio ? '' : 'El precio es obligatorio'
  localErrors.descripcion = form.descripcion.trim() ? '' : 'La descripción es obligatoria'

  // FOTO → obligatoria solo al crear
  if (!props.habitacion && !form.foto) {
    localErrors.foto = 'Debe subir una imagen de la habitación'
  }

  return Object.values(localErrors).every(err => err === '')
}

// Enviar formulario
function submit() {
  if (!validateForm()) return

  // Normalizar datos
  form.numero_habitacion = form.numero_habitacion.trim()
  form.capacidad_personas = parseInt(form.capacidad_personas)
  form.precio = parseFloat(form.precio)
  form.descripcion = form.descripcion.trim()

  const url = props.habitacion
    ? `/admin/habitaciones/${form.id}`
    : '/admin/habitaciones'

  if (props.habitacion) {
    // Actualizar con FormData
    form.transform((data) => {
      const fd = new FormData()
      Object.entries(data).forEach(([key, value]) => {
        if (value !== null && value !== undefined) fd.append(key, value)
      })
      fd.append('_method', 'PUT')
      return fd
    })

    form.post(url, {
      onSuccess: () => {
        alert('Habitación actualizada correctamente.')
        window.location.href = '/admin/habitaciones'
      }
    })

  } else {
    // Crear
    form.post(url, {
      forceFormData: true,
      onSuccess: () => {
        alert('Habitación creada correctamente.')
        window.location.href = '/admin/habitaciones'
      }
    })
  }
}
</script>

<template>
  <Head :title="props.habitacion ? 'Editar Habitación' : 'Crear Habitación'" />

  <AdminLayout>
    <div class="p-6 max-w-2xl mx-auto bg-white rounded-lg shadow-lg">

      <h2 class="text-2xl font-bold mb-6 text-[#2E7D32]">
        {{ props.habitacion ? 'Editar Habitación' : 'Crear Habitación' }}
      </h2>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- Número -->
        <div>
          <label class="block text-sm font-medium mb-1">Número</label>
          <input 
            v-model="form.numero_habitacion" 
            type="text"
            :class="['w-full border p-2 rounded shadow-sm', (localErrors.numero_habitacion || form.errors.numero_habitacion) ? 'border-red-500' : 'border-gray-300']"
          />
          <p v-if="localErrors.numero_habitacion" class="text-red-600 text-sm mt-1">{{ localErrors.numero_habitacion }}</p>
          <p v-if="form.errors.numero_habitacion" class="text-red-600 text-sm mt-1">{{ form.errors.numero_habitacion }}</p>
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium mb-1">Tipo</label>
          <select v-model="form.tipo" class="w-full border p-2 rounded shadow-sm border-gray-300">
            <option>Individual</option>
            <option>Doble</option>
            <option>Suite</option>
            <option>Familiar</option>
          </select>
        </div>

        <!-- Capacidad y Precio -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Capacidad</label>
            <input 
              v-model="form.capacidad_personas" 
              type="number" 
              :class="['w-full border p-2 rounded shadow-sm', (localErrors.capacidad_personas || form.errors.capacidad_personas) ? 'border-red-500' : 'border-gray-300']"
            />
            <p v-if="localErrors.capacidad_personas" class="text-red-600 text-sm mt-1">{{ localErrors.capacidad_personas }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Precio</label>
            <input 
              v-model="form.precio" 
              type="number" 
              step="0.01"
              :class="['w-full border p-2 rounded shadow-sm', (localErrors.precio || form.errors.precio) ? 'border-red-500' : 'border-gray-300']"
            />
            <p v-if="localErrors.precio" class="text-red-600 text-sm mt-1">{{ localErrors.precio }}</p>
          </div>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium mb-1">Estado</label>
          <select v-model="form.estado" class="w-full border p-2 rounded shadow-sm border-gray-300">
            <option>Disponible</option>
            <option>Ocupada</option>
            <option>Mantenimiento</option>
          </select>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium mb-1">Descripción</label>
          <textarea 
            v-model="form.descripcion" 
            :class="['w-full border p-2 rounded shadow-sm', (localErrors.descripcion || form.errors.descripcion) ? 'border-red-500' : 'border-gray-300']"
          ></textarea>
          <p v-if="localErrors.descripcion" class="text-red-600 text-sm mt-1">{{ localErrors.descripcion }}</p>
        </div>

        <!-- Foto -->
        <div>
          <label class="block text-sm font-medium mb-1">Foto</label>

          <!-- Foto actual en edición -->
          <div v-if="props.habitacion && props.habitacion.foto" class="mb-2">
            <img :src="`/storage/${props.habitacion.foto}`" class="w-48 h-32 object-cover rounded shadow-sm" />
          </div>

          <input type="file" @change="handleFileChange" accept="image/*" class="w-full border p-2 rounded shadow-sm border-gray-300"/>

          <!-- Error si foto no se sube al crear -->
          <p v-if="localErrors.foto" class="text-red-600 text-sm mt-1">{{ localErrors.foto }}</p>
          <p v-if="form.errors.foto" class="text-red-600 text-sm mt-1">{{ form.errors.foto }}</p>

          <!-- Vista previa -->
          <div v-if="preview" class="mt-3">
            <p class="text-sm text-gray-600 mb-1">Vista previa:</p>
            <img :src="preview" class="w-48 h-32 object-cover rounded shadow-sm" />
          </div>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 mt-6">
          <button type="submit" class="bg-[#2E7D32] text-white px-6 py-2 rounded shadow hover:bg-green-700 transition">
            {{ props.habitacion ? 'Actualizar' : 'Guardar' }}
          </button>
          <a href="/admin/habitaciones" class="px-6 py-2 border rounded shadow hover:bg-gray-100 transition">
            Cancelar
          </a>
        </div>

      </form>
    </div>
  </AdminLayout>
</template>
