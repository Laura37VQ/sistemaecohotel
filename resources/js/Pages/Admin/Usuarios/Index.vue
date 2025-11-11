<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'

// Props recibidos desde el controlador
const props = defineProps({
  usuarios: {
    type: Object,
    default: () => ({ data: [] })
  }
})

// Cambiar estado (activar/desactivar)
function toggleEstado(id, estaDesactivado) {
  const mensaje = estaDesactivado
    ? '¿Deseas reactivar este usuario?'
    : '¿Deseas desactivar este usuario?'

  if (!confirm(mensaje)) return

  router.delete(`/admin/usuarios/${id}`, {
    preserveScroll: true,
    onSuccess: () =>
      alert(estaDesactivado ? 'Usuario reactivado.' : 'Usuario desactivado.')
  })
}

// Formatear fechas (solo día/mes/año)
function formatearFecha(fecha) {
  if (!fecha) return ''
  return new Date(fecha).toLocaleDateString('es-CO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}
</script>

<template>
  <Head title="Usuarios" />

  <AdminLayout>
    <div class="p-6 bg-gray-50">
      <!-- ENCABEZADO -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Usuarios</h2>

        <a
          href="/admin/usuarios/create"
          class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2"
        >
          <span class="text-xl font-bold">+</span> Crear usuario
        </a>
      </div>

      <!-- TABLA DE USUARIOS -->
      <div
        class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white max-w-full"
      >
        <table class="min-w-[1200px] text-sm text-gray-700">
          <thead class="bg-green-50 uppercase sticky top-0 z-10">
            <tr>
              <th class="px-3 py-3 border-b">ID</th>
              <th class="px-3 py-3 border-b">Nombre completo</th>
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
            <tr v-if="!usuarios.data.length">
              <td
                colspan="13"
                class="px-4 py-6 text-center text-gray-500 italic"
              >
                No hay usuarios registrados.
              </td>
            </tr>

            <tr
              v-for="u in usuarios.data"
              :key="u.id"
              :class="[
                u.deleted_at
                  ? 'bg-red-50 text-gray-500'
                  : 'hover:bg-gray-50 transition',
                'border-b'
              ]"
            >
              <td class="px-3 py-2">{{ u.id }}</td>
              <td class="px-3 py-2">{{ u.nombres }} {{ u.apellidos }}</td>
              <td class="px-3 py-2">{{ u.documento_identidad }}</td>
              <td class="px-3 py-2">{{ u.correo }}</td>
              <td class="px-3 py-2">{{ u.telefono }}</td>
              <td class="px-3 py-2">{{ u.direccion }}</td>
              <td class="px-3 py-2">{{ u.nombre_usuario }}</td>
              <td class="px-3 py-2">
                {{ formatearFecha(u.fecha_nacimiento) }}
              </td>
              <td class="px-3 py-2">{{ u.rol?.nombre_rol || 'Sin rol' }}</td>
              <td class="px-3 py-2">{{ formatearFecha(u.created_at) }}</td>
              <td class="px-3 py-2">{{ formatearFecha(u.updated_at) }}</td>

              <td class="px-3 py-2 text-center">
                <span
                  :class="
                    u.deleted_at
                      ? 'text-red-600 font-semibold'
                      : 'text-green-600 font-semibold'
                  "
                >
                  {{ u.deleted_at ? 'Desactivado' : 'Activo' }}
                </span>
              </td>

              <td class="px-3 py-2 text-center flex justify-center gap-2">
                <a
                  :href="`/admin/usuarios/${u.id}/edit`"
                  class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs font-medium"
                >
                  Editar
                </a>
                <button
                  @click="toggleEstado(u.id, !!u.deleted_at)"
                  :class="
                    u.deleted_at
                      ? 'bg-green-600 hover:bg-green-700'
                      : 'bg-red-500 hover:bg-red-600'
                  "
                  class="px-3 py-1 text-white rounded text-xs font-medium"
                >
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
          v-if="usuarios.prev_page_url"
          @click="router.get(usuarios.prev_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          ← Anterior
        </button>
        <button
          v-if="usuarios.next_page_url"
          @click="router.get(usuarios.next_page_url)"
          class="px-4 py-2 border rounded hover:bg-gray-100 transition"
        >
          Siguiente →
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

