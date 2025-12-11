<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

/* Props desde backend */
const props = defineProps({
  usuarios: Object,
  roles: Array,
  filtros: Object
})

/* Filtros */
const q      = ref(props.filtros.q || '')
const rol    = ref(props.filtros.rol || '')
const estado = ref(props.filtros.estado || '')

/* Buscar */
function buscar() {
  router.get('/admin/usuarios', {
    q: q.value,
    rol: rol.value,
    estado: estado.value,
  }, { preserveState: true })
}

/* Limpiar */
function limpiar() {
  q.value = ''
  rol.value = ''
  estado.value = ''
  buscar()
}

/* Activar / desactivar */
function toggleEstado(id, desactivado) {
  const msg = desactivado
    ? '¿Deseas reactivar este usuario?'
    : '¿Deseas desactivar este usuario?'

  if (!confirm(msg)) return
  router.delete(`/admin/usuarios/${id}`, { preserveScroll: true })
}

/* Formato fechas */
function formatearFecha(fecha) {
  if (!fecha) return ''
  return new Date(fecha).toLocaleDateString('es-CO')
}
</script>

<template>
  <Head title="Usuarios" />

  <AdminLayout>
    <div class="p-6 bg-gray-50 overflow-x-auto">

      <!-- TÍTULO -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-green-700">Usuarios</h2>

        <a href="/admin/usuarios/create"
           class="px-5 py-2 bg-green-700 text-white rounded-lg shadow hover:bg-green-800 transition">
          + Crear usuario
        </a>
      </div>

      <!-- FILTROS -->
      <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4 flex-wrap">

        <!-- BUSCAR -->
        <input
          v-model="q"
          type="text"
          placeholder="Buscar..."
          class="border px-3 py-2 rounded w-60"
        />

        <!-- ROL -->
        <select v-model="rol" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los roles</option>
          <option v-for="r in props.roles" :key="r.id" :value="r.id">
            {{ r.nombre_rol }}
          </option>
        </select>

        <!-- ESTADO -->
        <select v-model="estado" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los estados</option>
          <option value="activo">Activos</option>
          <option value="inactivo">Inactivos</option>
        </select>

        <!-- BOTONES -->
        <button @click="buscar"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Buscar
        </button>

        <button @click="limpiar"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
          Limpiar
        </button>

      </div>

      <!-- TABLA -->
      <div class="overflow-x-auto overflow-y-auto rounded-lg shadow border bg-white max-h-[550px]">
        <table class="min-w-[1800px] text-xs text-gray-700 table-auto">
          <thead class="bg-green-50 uppercase sticky top-0 z-10">
            <tr>
              <th class="px-3 py-3 border-b">ID</th>
              <th class="px-3 py-3 border-b">Nombre</th>
              <th class="px-3 py-3 border-b">Documento</th>
              <th class="px-3 py-3 border-b">Correo</th>
              <th class="px-3 py-3 border-b">Teléfono</th>
              <th class="px-3 py-3 border-b">Dirección</th>
              <th class="px-3 py-3 border-b">Usuario</th>
              <th class="px-3 py-3 border-b">Nacimiento</th>
              <th class="px-3 py-3 border-b">Rol</th>
              <th class="px-3 py-3 border-b">Creado</th>
              <th class="px-3 py-3 border-b">Actualizado</th>
              <th class="px-3 py-3 border-b text-center">Estado</th>
              <th class="px-3 py-3 border-b text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!props.usuarios.data.length">
              <td colspan="13" class="px-4 py-6 text-center text-gray-500 italic">
                No hay usuarios registrados.
              </td>
            </tr>

            <tr
              v-for="u in props.usuarios.data"
              :key="u.id"
              :class="[ u.deleted_at ? 'bg-red-50 text-gray-500' : 'hover:bg-gray-50', 'border-b' ]"
            >
              <td class="px-3 py-2">{{ u.id }}</td>
              <td class="px-3 py-2">{{ u.nombres }} {{ u.apellidos }}</td>
              <td class="px-3 py-2">{{ u.documento_identidad }}</td>
              <td class="px-3 py-2">{{ u.correo }}</td>
              <td class="px-3 py-2">{{ u.telefono }}</td>
              <td class="px-3 py-2">{{ u.direccion }}</td>
              <td class="px-3 py-2">{{ u.nombre_usuario }}</td>
              <td class="px-3 py-2">{{ formatearFecha(u.fecha_nacimiento) }}</td>
              <td class="px-3 py-2">{{ u.rol?.nombre_rol }}</td>
              <td class="px-3 py-2">{{ formatearFecha(u.created_at) }}</td>
              <td class="px-3 py-2">{{ formatearFecha(u.updated_at) }}</td>

              <td class="px-3 py-2 text-center">
                <span :class="u.deleted_at ? 'text-red-600' : 'text-green-600'">
                  {{ u.deleted_at ? 'Desactivado' : 'Activo' }}
                </span>
              </td>

              <td class="px-3 py-2 flex justify-center gap-2">
                <a :href="`/admin/usuarios/${u.id}/edit`"
                   class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">
                  Editar
                </a>

                <button @click="toggleEstado(u.id, !!u.deleted_at)"
                        :class="u.deleted_at ? 'bg-green-600' : 'bg-red-600'"
                        class="px-3 py-1 text-white rounded text-xs">
                  {{ u.deleted_at ? 'Reactivar' : 'Desactivar' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-between">
        <button
          v-if="props.usuarios.prev_page_url"
          @click="router.get(props.usuarios.prev_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100">
          ← Anterior
        </button>

        <button
          v-if="props.usuarios.next_page_url"
          @click="router.get(props.usuarios.next_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100">
          Siguiente →
        </button>
      </div>

    </div>
  </AdminLayout>
</template>
