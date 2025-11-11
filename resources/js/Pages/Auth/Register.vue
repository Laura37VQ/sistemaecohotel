<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';

// Formulario base
const form = useForm({
  rol_id: 2, // Cliente
  nombres: '',
  apellidos: '',
  documento_identidad: '',
  fecha_nacimiento: '',
  nombre_usuario: '',
  correo: '',
  telefono: '',
  direccion: '',
  contrasena: '',
  contrasena_confirmation: '',
});

const processing = ref(false);

// Validaciones
watch(form, () => {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  form.errors.correo = form.correo && !emailPattern.test(form.correo) ? 'Correo inválido' : '';

  form.errors.contrasena = form.contrasena && form.contrasena.length < 8 ? 'La contraseña debe tener mínimo 8 caracteres' : '';

  form.errors.contrasena_confirmation =
    form.contrasena && form.contrasena_confirmation && form.contrasena !== form.contrasena_confirmation
      ? 'Las contraseñas no coinciden'
      : '';

  form.errors.documento_identidad =
    form.documento_identidad && !/^\d+$/.test(form.documento_identidad) ? 'El documento debe contener solo números' : '';

  form.errors.telefono =
    form.telefono && !/^\+?\d{7,15}$/.test(form.telefono) ? 'Número de teléfono inválido' : '';
});

// Envío
const submit = () => {
  processing.value = true;
  form.post('/register', {
    onFinish: () => (processing.value = false),
  });
};
</script>

<template>
  <AuthSplitLayout
    title="Crear una cuenta"
    description="Ingrese sus datos para crear su cuenta"
  >
    <Head title="Registro" />

    <form @submit.prevent="submit" class="flex flex-col gap-6">
      <div class="grid gap-6">
        <!-- Nombres -->
        <div class="grid gap-2">
          <Label for="nombres">Nombres</Label>
          <Input id="nombres" type="text" v-model="form.nombres" required placeholder="Ingrese sus nombres" />
          <InputError :message="form.errors.nombres" />
        </div>

        <!-- Apellidos -->
        <div class="grid gap-2">
          <Label for="apellidos">Apellidos</Label>
          <Input id="apellidos" type="text" v-model="form.apellidos" required placeholder="Ingrese sus apellidos" />
          <InputError :message="form.errors.apellidos" />
        </div>

        <!-- Documento identidad -->
        <div class="grid gap-2">
          <Label for="documento_identidad">Documento de identidad</Label>
          <Input id="documento_identidad" type="text" v-model="form.documento_identidad" required placeholder="Ingrese su número de documento" />
          <InputError :message="form.errors.documento_identidad" />
        </div>

        <!-- Fecha nacimiento -->
        <div class="grid gap-2">
          <Label for="fecha_nacimiento">Fecha de nacimiento</Label>
          <Input id="fecha_nacimiento" type="date" v-model="form.fecha_nacimiento" required />
          <InputError :message="form.errors.fecha_nacimiento" />
        </div>

        <!-- Nombre usuario -->
        <div class="grid gap-2">
          <Label for="nombre_usuario">Nombre de usuario</Label>
          <Input id="nombre_usuario" type="text" v-model="form.nombre_usuario" required placeholder="Ingrese un nombre de usuario" />
          <InputError :message="form.errors.nombre_usuario" />
        </div>

        <!-- Correo -->
        <div class="grid gap-2">
          <Label for="correo">Correo electrónico</Label>
          <Input id="correo" type="email" v-model="form.correo" required placeholder="correo@ejemplo.com" />
          <InputError :message="form.errors.correo" />
        </div>

        <!-- Teléfono -->
        <div class="grid gap-2">
          <Label for="telefono">Teléfono</Label>
          <Input id="telefono" type="tel" v-model="form.telefono" placeholder="Ingrese su número de teléfono" />
          <InputError :message="form.errors.telefono" />
        </div>

        <!-- Dirección -->
        <div class="grid gap-2">
          <Label for="direccion">Dirección</Label>
          <Input id="direccion" type="text" v-model="form.direccion" placeholder="Ingrese su dirección" />
          <InputError :message="form.errors.direccion" />
        </div>

        <!-- Contraseña -->
        <div class="grid gap-2">
          <Label for="contrasena">Contraseña</Label>
          <Input id="contrasena" type="password" v-model="form.contrasena" required placeholder="Ingrese su contraseña" />
          <InputError :message="form.errors.contrasena" />
        </div>

        <!-- Confirmar contraseña -->
        <div class="grid gap-2">
          <Label for="contrasena_confirmation">Confirmar contraseña</Label>
          <Input id="contrasena_confirmation" type="password" v-model="form.contrasena_confirmation" required placeholder="Repita su contraseña" />
          <InputError :message="form.errors.contrasena_confirmation" />
        </div>

        <!-- Botón -->
        <Button
          type="submit"
          class="mt-2 w-full bg-[#2E7D32] hover:bg-[#256428] text-white font-semibold py-2 rounded-lg"
          :disabled="processing"
        >
          <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
          <span v-else>Crear cuenta</span>
        </Button>
      </div>

      <!-- Link a login -->
      <div class="text-center text-sm text-gray-600 mt-4">
        ¿Ya tienes una cuenta?
        <TextLink href="/login" class="text-[#2E7D32] hover:underline font-semibold">
          Iniciar sesión
        </TextLink>
      </div>
    </form>
  </AuthSplitLayout>
</template>
