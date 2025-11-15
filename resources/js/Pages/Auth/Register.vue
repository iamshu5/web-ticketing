<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { useSwal } from '@/composables/useSwal'
import { onMounted } from 'vue'

const { success, error } = useSwal()

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

onMounted(() => {
  if (Object.keys(form.errors).length > 0) {
    const firstError = Object.values(form.errors)[0]
    error(firstError)
  }
})

const submit = () => {
  form.post('/register', {
    onSuccess: () => {
      success('Pendaftaran berhasil! Selamat datang!')
    },
    onError: (errors) => {
      if (errors.email) {
        error('Email sudah digunakan!')
      } else if (errors.password) {
        error('Password minimal 8 karakter!')
      } else {
        error('Terjadi kesalahan! Silakan coba lagi.')
      }
    },
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Sistem Pemesanan Ticket</h1>
        <p class="text-gray-600">Buat akun baru</p>
      </div>
      
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
          <input type="text" v-model="form.name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" :class="{ 'border-red-500': form.errors.name }" placeholder="Masukkan nama lengkap" required>
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
            {{ form.errors.name }}
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
          <input type="email" v-model="form.email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" :class="{ 'border-red-500': form.errors.email }" placeholder="Masukkan email" required>
          <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Telepon</label>
          <input type="text" v-model="form.phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" :class="{ 'border-red-500': form.errors.phone }" placeholder="Contoh: 081234567890" required>
          <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
            {{ form.errors.phone }}
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
          <input type="password" v-model="form.password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" :class="{ 'border-red-500': form.errors.password }" placeholder="Minimal 8 karakter" required>
          <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
          <input type="password" v-model="form.password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Ulangi password" required>
        </div>

        <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-md hover:bg-green-700 transition font-semibold flex items-center justify-center" :disabled="form.processing">
          <span v-if="form.processing" class="animate-spin mr-2">⟳</span>
          {{ form.processing ? 'Memproses...' : 'Daftar' }}
        </button>
      </form>

      <p class="text-center text-gray-600 mt-6">
        Sudah punya akun? 
        <Link href="/login" class="text-green-600 hover:underline font-semibold">Login disini</Link>
      </p>
    </div>
  </div>
</template>