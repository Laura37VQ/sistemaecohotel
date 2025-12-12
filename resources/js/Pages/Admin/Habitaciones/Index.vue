<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

/*
   Recibimos desde el backend:
   - La lista paginada de habitaciones (incluye activas y desactivadas)
   - Los filtros anteriores
*/
const props = defineProps({
  habitaciones: Object,
  filtros: Object
})

// Filtros reactivos
const q      = ref(props.filtros.q || "")
const tipo   = ref(props.filtros.tipo || "")
const estado = ref(props.filtros.estado || "")

/*
   Buscar con filtros aplicados
*/
function buscar() {
  router.get("/admin/habitaciones", {
    q: q.value,
    tipo: tipo.value,
    estado: estado.value,
  }, { preserveState: true })
}

/*
   Limpiar todos los filtros
*/
function limpiar() {
  q.value = ""
  tipo.value = ""
  estado.value = ""
  buscar()
}

/*
   Desactivar habitación (SoftDelete)
   Esto hace que desaparezca para el cliente.
*/
function desactivar(id) {
  if (!confirm("¿Desactivar esta habitación? Ya no estará disponible para los clientes.")) return;

  router.delete(`/admin/habitaciones/${id}`, {
    onSuccess: () => alert("Habitación desactivada correctamente.")
  })
}

/*
   Reactivar habitación que fue desactivada
*/
function reactivar(id) {
  if (!confirm("¿Reactivar esta habitación? Volverá a estar disponible.")) return;

  router.put(`/admin/habitaciones/${id}/restore`, {}, {
    onSuccess: () => alert("Habitación reactivada correctamente.")
  })
}
</script>

<template>
  <Head title="Habitaciones" />

  <AdminLayout>
    <div class="p-6">

      <!-- Título -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-green-700">Gestión de Habitaciones</h2>

        <a href="/admin/habitaciones/create"
           class="px-5 py-2 bg-green-700 text-white rounded-lg shadow hover:bg-green-800 transition">
          + Crear habitación
        </a>
      </div>

      <!-- Filtros -->
      <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4 flex-wrap">
        
        <!-- Buscar texto -->
        <input
          v-model="q"
          type="text"
          placeholder="Buscar número, tipo o descripción..."
          class="border px-3 py-2 rounded w-60"
        />

        <!-- Tipo -->
        <select v-model="tipo" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los tipos</option>
          <option value="Individual">Individual</option>
          <option value="Doble">Doble</option>
          <option value="Familiar">Familiar</option>
          <option value="Suite">Suite</option>
        </select>

        <!-- Estado operativo -->
        <select v-model="estado" class="border px-3 py-2 rounded w-48">
          <option value="">Todos los estados</option>
          <option value="Disponible">Disponible</option>
          <option value="Ocupada">Ocupada</option>
          <option value="Mantenimiento">Mantenimiento</option>
        </select>

        <button @click="buscar"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Buscar
        </button>

        <button @click="limpiar"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
          Limpiar
        </button>

      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-lg">
          <thead class="bg-green-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Número</th>
              <th class="px-4 py-3">Tipo</th>
              <th class="px-4 py-3">Capacidad</th>
              <th class="px-4 py-3">Precio</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Descripción</th>
              <th class="px-4 py-3">Foto</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!props.habitaciones.data.length">
              <td colspan="8" class="px-4 py-6 text-center text-gray-500 italic">
                No hay habitaciones registradas.
              </td>
            </tr>

            <tr v-for="h in props.habitaciones.data" :key="h.id"
                class="border-t hover:bg-gray-50">

              <td class="px-4 py-3 font-medium">#{{ h.numero_habitacion }}</td>
              <td class="px-4 py-3">{{ h.tipo }}</td>
              <td class="px-4 py-3">{{ h.capacidad_personas }}</td>
              <td class="px-4 py-3">${{ h.precio }}</td>

              <!-- Estado -->
              <td class="px-4 py-3">
                <span
                  :class="{
                    'px-3 py-1 rounded-full text-white text-sm font-semibold': true,
                    'bg-green-500': h.estado === 'Disponible',
                    'bg-yellow-500': h.estado === 'Ocupada',
                    'bg-red-500': h.estado === 'Mantenimiento'
                  }"
                >
                  {{ h.estado }}
                </span>

                <div v-if="h.deleted_at"
                     class="text-red-600 text-xs font-semibold mt-1">
                  (Desactivada)
                </div>
              </td>

              <td class="px-4 py-3">{{ h.descripcion }}</td>

              <td class="px-4 py-3">
                <img v-if="h.foto" :src="`/storage/${h.foto}`"
                     class="w-16 h-16 object-cover rounded-lg shadow-sm" />
                <span v-else class="text-gray-400 italic">Sin foto</span>
              </td>

              <!-- ACCIONES -->
              <td class="px-4 py-3 text-center">
                <div class="flex flex-col items-center gap-2">

                  <!-- Editar -->
                  <a v-if="!h.deleted_at"
                     :href="`/admin/habitaciones/${h.id}/edit`"
                     class="w-24 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm text-center">
                    Editar
                  </a>

                  <!-- Desactivar -->
                  <button v-if="!h.deleted_at"
                          @click="desactivar(h.id)"
                          class="w-24 px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                    Desactivar
                  </button>

                  <!-- Reactivar -->
                  <button v-if="h.deleted_at"
                          @click="reactivar(h.id)"
                          class="w-24 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Reactivar
                  </button>

                </div>
              </td>

            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-between">
        <button v-if="props.habitaciones.prev_page_url"
                @click="router.get(props.habitaciones.prev_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100">
          ← Anterior
        </button>

        <button v-if="props.habitaciones.next_page_url"
                @click="router.get(props.habitaciones.next_page_url)"
                class="px-4 py-2 border rounded hover:bg-gray-100">
          Siguiente →
        </button>
      </div>

    </div>
  </AdminLayout>
</template>
