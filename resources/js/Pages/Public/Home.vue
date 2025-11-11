<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const page = usePage()
const { habitaciones, categorias, info } = page.props

const menuAbierto = ref(false)

function logout() {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gradient-to-b from-[#E9F7EF] to-[#FFF9E6] text-gray-800">
    <!--  Navbar responsivo -->
    <header class="bg-white shadow sticky top-0 z-50">
      <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
        <!-- Logo / Nombre -->
        <Link href="/" class="flex items-center space-x-2 text-2xl font-bold text-[#2E7D32]">
          <img
            v-if="info?.logo"
            :src="'/storage/' + info.logo"
            alt="Logo"
            class="w-10 h-10 rounded-full object-cover"
          />
          <span>{{ info?.nombre || 'EcoHotel Villa del Sol' }}</span>
        </Link>

        <!-- Botón hamburguesa -->
        <button
          class="md:hidden text-[#2E7D32] focus:outline-none transition-transform duration-200"
          @click="menuAbierto = !menuAbierto"
          aria-label="Abrir menú"
        >
          <svg
            v-if="!menuAbierto"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
            class="w-8 h-8"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
            class="w-8 h-8"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Menú principal (desktop) -->
        <nav class="hidden md:flex items-center space-x-6 font-medium">
          <Link href="/#conocenos" class="hover:text-[#2E7D32]">Conócenos</Link>
          <Link href="/#habitaciones" class="hover:text-[#2E7D32]">Habitaciones</Link>
          <Link href="/#servicios" class="hover:text-[#2E7D32]">Servicios</Link>
          <Link href="/#contacto" class="hover:text-[#2E7D32]">Contacto</Link>

          <!-- Botones dinámicos -->
          <template v-if="!page.props.auth?.user">
            <Link
              href="/login"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
            >
              Iniciar sesión
            </Link>
            <Link
              href="/register"
              class="border border-[#2E7D32] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#2E7D32] hover:text-white transition"
            >
              Registrarse
            </Link>
          </template>

          <template v-else>
            <Link
              v-if="page.props.auth.user.rol_id === 1"
              href="/dashboard/admin"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
            >
              Ir al panel
            </Link>

            <Link
              v-else-if="page.props.auth.user.rol_id === 2"
              href="/dashboard/cliente"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
            >
              Ir a mi perfil
            </Link>

            <Link
              v-else-if="page.props.auth.user.rol_id === 3"
              href="/dashboard/recepcionista"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
            >
              Ir al panel
            </Link>

            <form @submit.prevent="logout" class="inline">
              <button
                type="submit"
                class="ml-2 border border-[#2E7D32] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#2E7D32] hover:text-white transition"
              >
                Cerrar sesión
              </button>
            </form>
          </template>
        </nav>
      </div>

      <!-- Menú móvil con animación deslizante -->
      <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-6"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-6"
      >
        <div
          v-if="menuAbierto"
          class="md:hidden flex flex-col items-center space-y-4 py-4 bg-white border-t border-gray-200 shadow-md"
        >
          <Link href="/#conocenos" class="hover:text-[#2E7D32]" @click="menuAbierto = false">
            Conócenos
          </Link>
          <Link href="/#habitaciones" class="hover:text-[#2E7D32]" @click="menuAbierto = false">
            Habitaciones
          </Link>
          <Link href="/#servicios" class="hover:text-[#2E7D32]" @click="menuAbierto = false">
            Servicios
          </Link>
          <Link href="/#contacto" class="hover:text-[#2E7D32]" @click="menuAbierto = false">
            Contacto
          </Link>

          <template v-if="!page.props.auth?.user">
            <Link
              href="/login"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
              @click="menuAbierto = false"
            >
              Iniciar sesión
            </Link>
            <Link
              href="/register"
              class="border border-[#2E7D32] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#2E7D32] hover:text-white transition"
              @click="menuAbierto = false"
            >
              Registrarse
            </Link>
          </template>

          <template v-else>
            <Link
              v-if="page.props.auth.user.rol_id === 1"
              href="/dashboard/admin"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
              @click="menuAbierto = false"
            >
              Ir al panel
            </Link>

            <Link
              v-else-if="page.props.auth.user.rol_id === 2"
              href="/dashboard/cliente"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
              @click="menuAbierto = false"
            >
              Ir a mi perfil
            </Link>

            <Link
              v-else-if="page.props.auth.user.rol_id === 3"
              href="/dashboard/recepcionista"
              class="bg-[#FFCE3E] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
              @click="menuAbierto = false"
            >
              Ir al panel
            </Link>

            <form @submit.prevent="logout" class="inline">
              <button
                type="submit"
                class="border border-[#2E7D32] text-[#2E7D32] px-4 py-2 rounded-lg font-semibold hover:bg-[#2E7D32] hover:text-white transition"
              >
                Cerrar sesión
              </button>
            </form>
          </template>
        </div>
      </transition>
    </header>

    <!--  Sección Conócenos -->
    <section id="conocenos" class="text-center py-16 bg-[#2E7D32] text-white">
      <h2 class="text-4xl font-bold mb-6">Conócenos</h2>
      <p class="max-w-3xl mx-auto text-lg leading-relaxed">
        {{ info?.actividad_economica || 'Descubre el encanto del turismo rural en Cabrera, Santander, donde la naturaleza, la sostenibilidad y la hospitalidad se unen en una experiencia única.' }}
      </p>
    </section>

    <!--  Habitaciones -->
    <section id="habitaciones" class="py-16 container mx-auto px-8">
      <h3 class="text-3xl font-bold text-center text-[#2E7D32] mb-10">Habitaciones Disponibles</h3>

      <div v-if="habitaciones.length" class="grid md:grid-cols-3 gap-8">
        <div
          v-for="habitacion in habitaciones"
          :key="habitacion.id"
          class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition"
        >
          <img :src="'/storage/' + habitacion.foto" alt="Foto" class="w-full h-48 object-cover" />
          <div class="p-4">
            <h4 class="text-lg font-semibold text-[#2E7D32]">
              {{ habitacion.tipo }} #{{ habitacion.numero_habitacion }}
            </h4>
            <p class="text-gray-600">{{ habitacion.descripcion }}</p>
            <p class="mt-2 font-bold text-[#2E7D32]">${{ habitacion.precio.toLocaleString() }}</p>
            <Link
              v-if="!page.props.auth?.user"
              href="/login"
              class="mt-3 inline-block bg-[#FFCE3E] text-[#2E7D32] px-3 py-1 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
            >
              Reservar
            </Link>

            <Link
            v-else-if="page.props.auth.user.rol_id === 2"
            :href="`/cliente/reservar/${habitacion.id}`"
            class="mt-3 inline-block bg-[#FFCE3E] text-[#2E7D32] px-3 py-1 rounded-lg font-semibold hover:bg-[#FFD95B] transition"
          >
          Reservar
          </Link>
          </div>
        </div>
      </div>

      <div v-else class="text-center text-gray-500">
        No hay habitaciones disponibles en este momento.
      </div>
    </section>

    <!--  Servicios -->
    <section id="servicios" class="bg-[#FFF9E6] py-16">
      <h3 class="text-3xl font-bold text-center text-[#2E7D32] mb-10">Servicios y Experiencias</h3>

      <div v-if="categorias.length" class="container mx-auto px-8 space-y-12">
        <div v-for="categoria in categorias" :key="categoria.id">
          <h4 class="text-2xl font-bold text-[#2E7D32] mb-6">{{ categoria.nombre_categoria }}</h4>
          <div class="grid md:grid-cols-3 gap-8">
            <div
              v-for="servicio in categoria.servicios"
              :key="servicio.id"
              class="bg-white shadow rounded-xl p-4 text-center hover:shadow-xl transition"
            >
              <img
                :src="'/storage/' + servicio.foto"
                class="w-full h-40 object-cover rounded-lg mb-3"
              />
              <h5 class="text-xl font-semibold text-[#2E7D32]">{{ servicio.nombre }}</h5>
              <p class="text-gray-600">{{ servicio.descripcion }}</p>
              <p class="mt-2 font-bold text-[#2E7D32]">${{ servicio.precio.toLocaleString() }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!--  Contacto -->
    <section id="contacto" class="py-16 text-center bg-[#2E7D32] text-white">
      <h3 class="text-3xl font-bold mb-6">Contáctanos</h3>
      <p> {{ info?.direccion }}</p>
      <p> {{ info?.telefono }}</p>
      <p> {{ info?.email }}</p>
    </section>

    <!-- Footer -->
    <footer class="bg-[#FFCE3E] text-[#2E7D32] text-center py-4 font-medium">
      © 2025 {{ info?.nombre || 'EcoHotel Villa del Sol' }} | Turismo Rural y Sostenible 
    </footer>
  </div>
</template>
