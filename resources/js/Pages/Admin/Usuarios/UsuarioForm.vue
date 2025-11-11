<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { watch, ref } from 'vue'

// Props
const props = defineProps({
  usuario: {
    type: Object,
    default: null
  },
  roles: {
    type: Array,
    default: () => []
  }
})

// Bandera: si queremos cambiar la contraseña cuando editamos
const cambiarContrasena = ref(false)

// Formulario Inertia
const form = useForm({
  id: props.usuario?.id || null,
  rol_id: props.usuario?.rol_id || '',
  nombres: props.usuario?.nombres || '',
  apellidos: props.usuario?.apellidos || '',
  documento_identidad: props.usuario?.documento_identidad || '',
  fecha_nacimiento: props.usuario?.fecha_nacimiento || '',
  correo: props.usuario?.correo || '',
  telefono: props.usuario?.telefono || '',
  direccion: props.usuario?.direccion || '',
  nombre_usuario: props.usuario?.nombre_usuario || '',
  contrasena: '',
  contrasena_confirmation: ''
})

// Si llega un usuario (editar) actualizamos el form cuando cambian las props
watch(
  () => props.usuario,
  (newVal) => {
    if (newVal) {
      Object.assign(form, {
        id: newVal.id,
        rol_id: newVal.rol_id,
        nombres: newVal.nombres,
        apellidos: newVal.apellidos,
        documento_identidad: newVal.documento_identidad,
        fecha_nacimiento: newVal.fecha_nacimiento,
        correo: newVal.correo,
        telefono: newVal.telefono,
        direccion: newVal.direccion,
        nombre_usuario: newVal.nombre_usuario
      })
      cambiarContrasena.value = false // al editar por defecto no cambiar contraseña
      form.contrasena = ''
      form.contrasena_confirmation = ''
    }
  },
  { immediate: true }
)

// Enviar formulario
function submit() {
  if (props.usuario) {
    // Si estamos editando y no queremos cambiar contraseña, limpiamos campos para no enviarlos
    if (!cambiarContrasena.value) {
      form.contrasena = ''
      form.contrasena_confirmation = ''
    }
    form.put(`/admin/usuarios/${props.usuario.id}`)
  } else {
    form.post('/admin/usuarios')
  }
}
</script>

<template>
  <Head :title="props.usuario ? 'Editar Usuario' : 'Crear Usuario'" />
  <AdminLayout>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-[#2E7D32]">
        {{ props.usuario ? 'Editar Usuario' : 'Crear Usuario' }}
      </h2>

      <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
        <!-- Rol -->
        <div class="col-span-2">
          <label class="block text-sm font-medium">Rol</label>
          <select v-model="form.rol_id" class="w-full border p-2 rounded shadow-sm">
            <option value="">Seleccione un rol</option>
            <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.nombre_rol }}</option>
          </select>
          <div v-if="form.errors.rol_id" class="text-red-600 text-sm">{{ form.errors.rol_id }}</div>
        </div>

        <!-- Nombres -->
        <div>
          <label class="block text-sm font-medium">Nombres</label>
          <input v-model="form.nombres" type="text" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.nombres" class="text-red-600 text-sm">{{ form.errors.nombres }}</div>
        </div>

        <!-- Apellidos -->
        <div>
          <label class="block text-sm font-medium">Apellidos</label>
          <input v-model="form.apellidos" type="text" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.apellidos" class="text-red-600 text-sm">{{ form.errors.apellidos }}</div>
        </div>

        <!-- Documento -->
        <div>
          <label class="block text-sm font-medium">Documento de identidad</label>
          <input v-model="form.documento_identidad" type="text" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.documento_identidad" class="text-red-600 text-sm">{{ form.errors.documento_identidad }}</div>
        </div>

        <!-- Fecha nacimiento -->
        <div>
          <label class="block text-sm font-medium">Fecha de nacimiento</label>
          <input v-model="form.fecha_nacimiento" type="date" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.fecha_nacimiento" class="text-red-600 text-sm">{{ form.errors.fecha_nacimiento }}</div>
        </div>

        <!-- Correo -->
        <div>
          <label class="block text-sm font-medium">Correo electrónico</label>
          <input v-model="form.correo" type="email" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.correo" class="text-red-600 text-sm">{{ form.errors.correo }}</div>
        </div>

        <!-- Teléfono -->
        <div>
          <label class="block text-sm font-medium">Teléfono</label>
          <input v-model="form.telefono" type="text" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.telefono" class="text-red-600 text-sm">{{ form.errors.telefono }}</div>
        </div>

        <!-- Dirección -->
        <div class="col-span-2">
          <label class="block text-sm font-medium">Dirección</label>
          <input v-model="form.direccion" type="text" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.direccion" class="text-red-600 text-sm">{{ form.errors.direccion }}</div>
        </div>

        <!-- Nombre usuario -->
        <div>
          <label class="block text-sm font-medium">Nombre de usuario</label>
          <input v-model="form.nombre_usuario" type="text" class="w-full border p-2 rounded shadow-sm" />
          <div v-if="form.errors.nombre_usuario" class="text-red-600 text-sm">{{ form.errors.nombre_usuario }}</div>
        </div>

        <!-- Si estamos editando: opción para cambiar contraseña -->
        <div v-if="props.usuario" class="flex items-center gap-2">
          <label class="flex items-center space-x-2">
            <input type="checkbox" v-model="cambiarContrasena" class="w-4 h-4"/>
            <span class="text-sm">Cambiar contraseña</span>
          </label>
        </div>

        <!-- Campos de contraseña: aparecen al crear o si marcaste cambiarContrasena -->
        <div v-if="!props.usuario || cambiarContrasena" class="col-span-2 grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium">Contraseña</label>
            <input v-model="form.contrasena" type="password" class="w-full border p-2 rounded shadow-sm" />
            <div v-if="form.errors.contrasena" class="text-red-600 text-sm">{{ form.errors.contrasena }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium">Confirmar contraseña</label>
            <input v-model="form.contrasena_confirmation" type="password" class="w-full border p-2 rounded shadow-sm" />
          </div>
        </div>

        <!-- Botones -->
        <div class="col-span-2 flex gap-3 mt-4">
          <button type="submit" class="bg-[#2E7D32] text-white px-4 py-2 rounded shadow hover:bg-green-700 transition">
            {{ props.usuario ? 'Actualizar' : 'Guardar' }}
          </button>
          <a href="/admin/usuarios" class="px-4 py-2 border rounded shadow hover:bg-gray-100 transition">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
