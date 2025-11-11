<script setup>
import RecepcionistaLayout from '@/layouts/RecepcionistaLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  clientes: { type: Object, default: () => ({ data: [] }) },
  filtros: { type: Object, default: () => ({ q: '' }) }
})

const q = ref(props.filtros.q || '')

function buscar() {
  router.get('/recepcionista/clientes', { q: q.value }, { preserveState: true, preserveScroll: true })
}

function desactivar(id) {
  if (!confirm('¿Desactivar este cliente?')) return
  router.delete(`/recepcionista/clientes/${id}`, {
    preserveScroll: true,
    onSuccess: () => alert('Cliente desactivado.')
  })
}

function reactivar(id) {
  if (!confirm('¿Reactivar este cliente?')) return
  router.post(`/recepcionista/clientes/${id}/restore`, {}, {
    preserveScroll: true,
    onSuccess: () => alert('Cliente reactivado.')
  })
}
</script>

<template>
  <Head title="Clientes (Recepcionista)" />

  <RecepcionistaLayout>
    <div class="p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <h2 class="text-3xl font-bold text-[#2E7D32]">Clientes</h2>

        <div class="flex gap-2 items-center">
          <input
            v-model="q"
            type="text"
            placeholder="Buscar por nombre, doc, correo..."
            class="border rounded-lg px-3 py-2 w-72"
            @keyup.enter="buscar"
          />
          <button @click="buscar" class="px-4 py-2 bg-[#2E7D32] text-white rounded-lg">Buscar</button>
        </div>

        <a
          href="/recepcionista/clientes/create"
          class="px-5 py-2 bg-[#2E7D32] text-white rounded-lg shadow hover:bg-green-700 transition"
        >
          Nuevo cliente
        </a>
      </div>

      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
          <thead class="bg-yellow-50 text-left text-gray-700 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Nombre</th>
              <th class="px-4 py-3">Documento</th>
              <th class="px-4 py-3">Correo</th>
              <th class="px-4 py-3">Teléfono</th>
              <th class="px-4 py-3">Usuario</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!props.clientes.data.length">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">Sin clientes</td>
            </tr>

            <tr v-else v-for="c in props.clientes.data" :key="c.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3">{{ c.apellidos }}, {{ c.nombres }}</td>
              <td class="px-4 py-3">{{ c.documento_identidad }}</td>
              <td class="px-4 py-3">{{ c.correo }}</td>
              <td class="px-4 py-3">{{ c.telefono }}</td>
              <td class="px-4 py-3">{{ c.nombre_usuario }}</td>
              <td class="px-4 py-3 text-center">
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-white text-sm',
                    c.deleted_at ? 'bg-red-500' : 'bg-green-600'
                  ]"
                >
                  {{ c.deleted_at ? 'Inactivo' : 'Activo' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2 justify-center">
                  <a
                    :href="`/recepcionista/clientes/${c.id}/edit`"
                    class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm"
                    v-if="!c.deleted_at"
                  >
                    Editar
                  </a>

                  <button
                    v-if="!c.deleted_at"
                    @click="desactivar(c.id)"
                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                  >
                    Desactivar
                  </button>

                  <button
                    v-else
                    @click="reactivar(c.id)"
                    class="px-3 py-1 bg-[#2E7D32] text-white rounded hover:bg-green-700 text-sm"
                  >
                    Reactivar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-6 flex justify-between">
        <button
          v-if="props.clientes.prev_page_url"
          @click="router.get(props.clientes.prev_page_url, {}, { preserveState: true })"
          class="px-4 py-2 border rounded hover:bg-gray-100"
        >
          ← Anterior
        </button>
        <button
          v-if="props.clientes.next_page_url"
          @click="router.get(props.clientes.next_page_url, {}, { preserveState: true })"
          class="px-4 py-2 border rounded hover:bg-gray-100"
        >
          Siguiente →
        </button>
      </div>
    </div>
  </RecepcionistaLayout>
</template>
