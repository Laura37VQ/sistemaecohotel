<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  roles: Object
})

function toggleEstado(id, estaDesactivado) {
  const accion = estaDesactivado ? 'activar' : 'desactivar';
  if (!confirm(`¿Seguro que deseas ${accion} este rol?`)) return;
  router.delete(`/admin/roles/${id}`);
}
</script>

<template>
  <Head title="Roles" />
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Roles</h2>
        <a href="/admin/roles/create"
           class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition flex items-center gap-2">
          <span class="text-xl font-bold">+</span> Crear Rol
        </a>
      </div>

      <table class="min-w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3">ID</th>
            <th class="px-4 py-3">Nombre del Rol</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in roles.data" :key="r.id" class="border-t hover:bg-gray-50 transition">
            <td class="px-4 py-3">{{ r.id }}</td>
            <td class="px-4 py-3">{{ r.nombre_rol }}</td>
            <td class="px-4 py-3">
              <span :class="r.deleted_at ? 'text-red-600' : 'text-green-600'">
                {{ r.deleted_at ? 'Inactivo' : 'Activo' }}
              </span>
            </td>
            <td class="px-4 py-3 flex gap-2">
              <a :href="`/admin/roles/${r.id}/edit`"
                 class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition text-sm font-medium">
                Editar
              </a>
              <button 
                @click="toggleEstado(r.id, !!r.deleted_at)"
                :class="[
                  'px-3 py-1 rounded text-white text-sm font-medium transition',
                  r.deleted_at ? 'bg-green-600 hover:bg-green-700' : 'bg-red-500 hover:bg-red-600'
                ]">
                {{ r.deleted_at ? 'Activar' : 'Desactivar' }}
              </button>
            </td>
          </tr>
          <tr v-if="!roles.data.length">
            <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">No hay roles registrados.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>
