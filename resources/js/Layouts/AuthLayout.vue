<script setup>
import { router } from '@inertiajs/vue3'

const logout = () => {
  if (confirm('Logout?')) {
    router.post('/logout')
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
          <h1 class="text-xl font-bold text-gray-800">Sistem Pemesanan Tiket</h1>
          <button 
            @click="logout"
            class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <main class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>