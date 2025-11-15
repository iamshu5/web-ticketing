<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post('/login', {
    onFinish: () => {
      form.reset('password')
    },
  })
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Sistem Pemesanan Ticketing</h1>
        <p class="text-gray-600">Silahkan Login</p>
      </div>
      
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
          <input type="email" v-model="form.email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" :class="{ 'border-red-500': form.errors.email }" placeholder="Masukkan email" required>
          <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
          <input type="password" v-model="form.password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" :class="{ 'border-red-500': form.errors.password }" placeholder="Masukkan password" required>
          <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition font-semibold flex items-center justify-center" :disabled="form.processing">
          <span v-if="form.processing" class="animate-spin mr-2">⟳</span>
          {{ form.processing ? 'Memproses...' : 'Login' }}
        </button>
      </form>

      <p class="text-center text-gray-600 mt-6">
        Belum punya akun? 
        <Link href="/register" class="text-blue-600 hover:underline font-semibold">Daftar di sini</Link>
      </p>

      <div class="mt-8 p-4 bg-gray-50 rounded-lg">
        <p class="text-sm text-gray-600 font-semibold mb-2">Akun Demo:</p>
        <div class="text-xs text-gray-500 space-y-1">
          <p><span class="font-medium">Admin:</span> admin@gmail.com / password</p>
          <p><span class="font-medium">Checker:</span> checker@gmail.com / password</p>
          <p><span class="font-medium">Customer:</span> customer@gmail.com / password</p>
        </div>
      </div>
    </div>
  </div>
</template>