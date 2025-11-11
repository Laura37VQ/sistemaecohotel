<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';

// Props del componente (si Laravel las envía)
defineProps<{
  status?: string;
  canResetPassword?: boolean;
}>();

// Formulario con Inertia
const form = useForm({
  correo: '',
  contrasena: '',
  remember: false,
});

// Enviar formulario
const submit = () => form.post('/login');
</script>

<template>
  <AuthSplitLayout
    title="Iniciar sesión"
    description="Accede a tu cuenta en el EcoHotel Villa del Sol"
  >
    <Head title="Iniciar sesión" />

    <!-- Mensaje de estado -->
    <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
      <!-- Correo -->
      <div class="grid gap-2">
        <Label for="correo">Correo electrónico</Label>
        <Input
          id="correo"
          type="email"
          v-model="form.correo"
          required
          placeholder="correo@ejemplo.com"
        />
        <InputError :message="form.errors.correo" />
      </div>

      <!-- Contraseña -->
      <div class="grid gap-2">
        <Label for="contrasena">Contraseña</Label>
        <Input
          id="contrasena"
          type="password"
          v-model="form.contrasena"
          required
          placeholder="••••••••"
        />
        <InputError :message="form.errors.contrasena" />
      </div>

      <!-- Botón -->
      <Button
        type="submit"
        class="mt-4 w-full bg-[#2E7D32] hover:bg-[#256428] text-white font-semibold py-2 rounded-lg"
        :disabled="form.processing"
      >
        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
        <span v-else>Iniciar sesión</span>
      </Button>

      <!-- Registro -->
      <p class="text-center text-sm text-gray-600">
        ¿No tienes una cuenta?
        <TextLink href="/register" class="text-[#2E7D32] hover:underline font-semibold">
          Regístrate
        </TextLink>
      </p>
    </form>
  </AuthSplitLayout>
</template>
