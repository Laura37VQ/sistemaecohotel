<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  categorias: Array,
  servicio: {
    type: Object,
    default: null
  }
})

const form = useForm({
  categoria_id: props.servicio?.categoria_id ?? '',
  nombre: props.servicio?.nombre ?? '',
  descripcion: props.servicio?.descripcion ?? '',
  precio: props.servicio?.precio ?? '',
  estado: props.servicio?.estado ?? 'Activo',
  foto: null
})

const previewUrl = ref(props.servicio?.foto_url ?? null)

function handleImageChange(e) {
  const file = e.target.files[0]
  if (file) {
    form.foto = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

function submit() {
  const url = props.servicio
    ? `/admin/servicios/${props.servicio.id}`
    : '/admin/servicios'

  if (props.servicio) {
    //  Enviar como FormData manualmente (PUT no soporta archivos nativos)
    form.transform((data) => {
      const fd = new FormData()
      Object.entries(data).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
          fd.append(key, value)
        }
      })
      fd.append('_method', 'PUT')
      return fd
    })

    form.post(url, {
      onSuccess: () => {
        alert(' Servicio actualizado correctamente.')
        window.location.href = '/admin/servicios'
      },
      onError: (errors) => {
        console.error(errors)
        alert(' Error al actualizar el servicio.')
      },
    })
  } else {
    //  Crear nuevo servicio
    form.post(url, {
      forceFormData: true,
      onSuccess: () => {
        alert(' Servicio creado correctamente.')
        window.location.href = '/admin/servicios'
      },
      onError: (errors) => {
        console.error(errors)
        alert(' Error al crear servicio.')
      },
    })
  }
}
</script>

<template>
  <Head :title="props.servicio ? 'Editar Servicio' : 'Registrar Servicio'" />
  <AdminLayout>
    <div class="p-8 max-w-3xl mx-auto bg-white rounded-2xl shadow-lg">
      <h2 class="text-3xl font-bold text-green-700 mb-6 text-center">
        {{ props.servicio ? 'Editar Servicio' : 'Registrar Nuevo Servicio' }}
      </h2>

      <form @submit.prevent="submit" class="space-y-6 text-lg">
        <!-- Categoría -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Categoría</label>
          <select v-model="form.categoria_id"
            class="w-full border rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-green-600">
            <option value="">Seleccione una categoría</option>
            <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
              {{ cat.nombre_categoria }}
            </option>
          </select>
          <p v-if="form.errors.categoria_id" class="text-red-600 text-sm mt-1">
            {{ form.errors.categoria_id }}
          </p>
        </div>

        <!-- Nombre -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Nombre</label>
          <input v-model="form.nombre" type="text"
            class="w-full border rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-green-600" />
          <p v-if="form.errors.nombre" class="text-red-600 text-sm mt-1">{{ form.errors.nombre }}</p>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Descripción</label>
          <textarea v-model="form.descripcion" rows="3"
            class="w-full border rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-green-600"></textarea>
        </div>

        <!-- Precio -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Precio</label>
          <input v-model="form.precio" type="number" step="0.01" min="0"
            class="w-full border rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-green-600" />
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Estado</label>
          <select v-model="form.estado"
            class="w-full border rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-green-600">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>

        <!-- Foto -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Foto del servicio</label>
          <input type="file" @change="handleImageChange" accept="image/*"
            class="w-full border rounded-xl px-4 py-3 text-base" />
          <div v-if="previewUrl" class="mt-3">
            <p class="text-gray-600 text-sm mb-1">Vista previa:</p>
            <img :src="previewUrl" class="rounded-xl w-48 border" />
          </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-between items-center mt-6">
          <a href="/admin/servicios" class="px-5 py-2 bg-gray-300 rounded-xl hover:bg-gray-400 transition text-base">
            ← Volver
          </a>
          <button type="submit"
            class="px-6 py-2 bg-green-700 text-white rounded-xl hover:bg-green-800 transition text-base"
            :disabled="form.processing">
            {{ props.servicio ? 'Actualizar' : 'Guardar' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
