<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

// Props desde el controlador
const props = defineProps({
  categoria: {
    type: Object,
    default: null
  }
})

// Saber si se está editando
const editando = computed(() => !!props.categoria?.id)

// Formulario reactivo
const form = useForm({
  nombre_categoria: props.categoria?.nombre_categoria || '',
  descripcion: props.categoria?.descripcion || '',
  estado: props.categoria?.estado || 'Activo'
})

// Si cambia la categoría, actualizar el form
watch(
  () => props.categoria,
  (newVal) => {
    form.reset({
      nombre_categoria: newVal?.nombre_categoria || '',
      descripcion: newVal?.descripcion || '',
      estado: newVal?.estado || 'Activo'
    })
  },
  { immediate: true }
)

// URL dinámica para PUT o POST
const actionUrl = computed(() =>
  editando.value
    ? `/admin/categorias-servicios/${props.categoria.id}`
    : '/admin/categorias-servicios'
)

// Enviar formulario
function submit() {
  if (editando.value) {
    // EDITAR → PUT
    form.put(actionUrl.value, {
      onSuccess: () => router.visit('/admin/categorias-servicios')
    })
  } else {
    // CREAR → POST
    form.post(actionUrl.value, {
      onSuccess: () => router.visit('/admin/categorias-servicios')
    })
  }
}
</script>

<template>
  <Head :title="editando ? 'Editar Categoría' : 'Crear Categoría'" />

  <AdminLayout>
    <div class="p-6 max-w-xl mx-auto bg-white rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-green-700 text-center">
        {{ editando ? 'Editar Categoría' : 'Crear Categoría' }}
      </h2>

      <form @submit.prevent="submit" class="space-y-4">
        <p class="text-sm text-gray-500 mb-4 text-center">
          Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
        </p>
        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium">
            Nombre de la categoría <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500">
            Ejemplo: Spa, Restaurante, Tours, Bienestar.
          </p>
          <input
            v-model="form.nombre_categoria"
            type="text"
            class="w-full border p-2 rounded shadow-sm"
            required
          />
          <div v-if="form.errors.nombre_categoria" class="text-red-600 text-sm">
            {{ form.errors.nombre_categoria }}
          </div>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium">
            Descripción <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500">
            Breve descripción del tipo de servicios que agrupa esta categoría.
          </p>
          <textarea
            v-model="form.descripcion"
            class="w-full border p-2 rounded shadow-sm"
            rows="3"
          ></textarea>
          <div v-if="form.errors.descripcion" class="text-red-600 text-sm">
            {{ form.errors.descripcion }}
          </div>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium">
            Estado <span class="text-red-500">*</span>
          </label>
          <select v-model="form.estado" class="w-full border p-2 rounded shadow-sm" required>
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
          <div v-if="form.errors.estado" class="text-red-600 text-sm">
            {{ form.errors.estado }}
          </div>
        </div>

        <!-- Botones -->
        <div class="flex gap-2 mt-4 justify-end">
          <a
            href="/admin/categorias-servicios"
            class="px-4 py-2 border rounded shadow hover:bg-gray-100 transition"
          >
            Cancelar
          </a>

          <button
            type="submit"
            class="bg-green-700 text-white px-4 py-2 rounded shadow hover:bg-green-800 transition"
            :disabled="form.processing"
          >
            {{ editando ? 'Actualizar' : 'Guardar' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
