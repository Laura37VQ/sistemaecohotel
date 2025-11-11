<script setup>
import ClienteLayout from '@/layouts/ClienteLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  reservas: Array
})
</script>

<template>
  <Head title="Panel del Cliente" />

  <ClienteLayout>
    <div class="min-h-screen bg-[#E8EFEB] py-12">
      <div class="max-w-6xl mx-auto bg-white rounded-3xl shadow-lg px-8 py-10">
        <!-- Encabezado -->
        <div class="text-center mb-10">
          <h2 class="text-4xl font-extrabold text-[#2E7D32] tracking-tight">
            Bienvenido, {{ user.nombres }}
          </h2>
          <p class="text-gray-600 mt-3 text-lg">
            Administra tus reservas y servicios del <b>EcoHotel Villa del Sol</b>.
          </p>
          <div class="h-1 w-24 bg-[#FFCE3E] mx-auto mt-4 rounded-full"></div>
        </div>

        <!-- Tarjetas de acción -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-10">
          <!-- Reservar habitación -->
          <Link
            href="/cliente/disponibilidad"
            class="block bg-[#FFCE3E] text-black rounded-2xl p-6 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all border border-[#E5B500]"
          >
            <div class="text-xl font-bold mb-2">Reservar habitación</div>
            <p class="text-sm text-gray-800 leading-relaxed">
              Consulta disponibilidad y realiza tu reserva fácilmente.
            </p>
          </Link>

          <!-- Mis reservas -->
          <Link
            href="/cliente/reservas"
            class="block bg-[#F8FBF9] rounded-2xl p-6 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all border border-[#DCE9DE]"
          >
            <div class="text-xl font-bold text-[#2E7D32] mb-2">Mis reservas</div>
            <p class="text-sm text-gray-700 leading-relaxed">
              Visualiza tus reservas activas, fechas y estados.
            </p>
          </Link>

          <!-- Servicios -->
          <Link
            href="/cliente/servicios/1"
            class="block bg-[#F8FBF9] rounded-2xl p-6 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all border border-[#DCE9DE]"
          >
            <div class="text-xl font-bold text-[#2E7D32] mb-2">
              Servicios y experiencias
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">
              Agrega experiencias únicas a tu estadía: spa, restaurante o productos.
            </p>
          </Link>

          <!-- Facturación -->
          <Link
            href="/cliente/facturacion"
            class="block bg-[#F8FBF9] rounded-2xl p-6 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all border border-[#DCE9DE]"
          >
            <div class="text-xl font-bold text-[#2E7D32] mb-2">Facturación</div>
            <p class="text-sm text-gray-700 leading-relaxed">
              Consulta y descarga tus facturas confirmadas.
            </p>
          </Link>
        </div>

        <!-- Reservas recientes -->
        <div v-if="reservas.length">
          <h3 class="text-2xl font-bold text-[#2E7D32] mb-6 text-center">
            Tus reservas recientes
          </h3>

          <div
            class="overflow-x-auto bg-white rounded-2xl shadow border border-gray-200"
          >
            <table class="min-w-full text-sm text-left text-gray-700">
              <thead class="bg-[#A5D6A7] text-[#1B5E20] uppercase text-xs font-semibold">
                <tr>
                  <th class="p-3">Código</th>
                  <th class="p-3">Habitación</th>
                  <th class="p-3">Fechas</th>
                  <th class="p-3">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="r in reservas"
                  :key="r.id"
                  class="border-t hover:bg-[#F1F8E9] transition"
                >
                  <td class="p-3 font-medium">{{ r.codigo_reserva }}</td>
                  <td class="p-3">{{ r.habitacion?.tipo }}</td>
                  <td class="p-3">{{ r.fecha_ingreso }} – {{ r.fecha_salida }}</td>
                  <td class="p-3">
                    <span
                      :class="r.estado === 'Confirmada'
                        ? 'text-green-700 font-semibold'
                        : 'text-gray-600'"
                    >
                      {{ r.estado }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Sin reservas -->
        <div v-else class="text-center text-gray-600 mt-20 italic text-base">
          Aún no tienes reservas registradas. <br />
          Comienza explorando nuestras habitaciones disponibles.
        </div>
      </div>
    </div>
  </ClienteLayout>
</template>
