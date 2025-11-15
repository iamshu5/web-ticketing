<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
  bookings: Array,
  jadwalHariIni: Array,
})

const formatJadwalList = computed(() => {
  if (!props.jadwalHariIni || props.jadwalHariIni.length === 0) {
    return 'Tidak ada jadwal'
  }
  
  return props.jadwalHariIni.map(jadwal => `${jadwal.nama_rute} (${formatTime(jadwal.waktu_keberangkatan)})`).join(', ')
})

const checkedCount = computed(() => {
  return props.bookings.filter(b => b.status === 'selesai').length
})

const pendingCheckCount = computed(() => {
  return props.bookings.filter(b => b.status === 'dikonfirmasi').length
})

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('id-ID')
}

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const isMultipleBooking = (booking) => {
  return booking.details && booking.details.length > 1
}

const formatKursiList = (booking) => {
  if (booking.details && booking.details.length > 0) {
    const kursiList = booking.details.map(detail => detail.nomor_kursi)
    return kursiList.join(', ')
  }
  return booking.nomor_kursi || '-'
}

const formatNamaPenumpang = (booking) => {
  if (booking.details && booking.details.length > 0) {
    const namaList = booking.details.map(detail => detail.nama_penumpang)
    return namaList.join(', ')
  }
  return booking.nama_penumpang || '-'
}

const checkPassenger = (id) => {
  if (confirm('Konfirmasi penumpang sudah hadir?')) {
    router.post(`/checker/booking/${id}/periksa`)
  }
}
</script>

<template>
  <AuthLayout>
    <div class="space-y-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Checker</h1>
      </div>
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Checker</h1>
        <p class="text-gray-600 mt-2">
          {{ new Date().toLocaleDateString('id-ID', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          }) }}
        </p>
        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
          <p class="text-sm font-medium text-blue-800 mb-2">📅 Jadwal Keberangkatan Hari Ini:</p>
          <p class="text-blue-700">{{ formatJadwalList }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg mr-4">
              <span class="text-blue-600 text-2xl">👥</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total Penumpang Hari Ini</p>
              <p class="text-2xl font-bold text-gray-800">{{ bookings.length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg mr-4">
              <span class="text-yellow-600 text-2xl">⏳</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Belum Diperiksa</p>
              <p class="text-2xl font-bold text-gray-800">{{ pendingCheckCount }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg mr-4">
              <span class="text-green-600 text-2xl">✅</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Sudah Diperiksa</p>
              <p class="text-2xl font-bold text-gray-800">{{ checkedCount }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b">
          <h2 class="text-xl font-semibold text-gray-800">Daftar Penumpang Hari Ini</h2>
          <p class="text-gray-600">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
          <!-- <p class="text-sm text-gray-600 mt-1">
            Total {{ bookings.length }} penumpang • {{ pendingCheckCount }} belum diperiksa • {{ checkedCount }} sudah diperiksa
          </p> -->
        </div>
        
        <div class="p-6">
          <div v-if="bookings.length === 0" class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">🚌</div>
            <p class="text-gray-500 text-lg mb-2">Tidak ada penumpang hari ini</p>
            <p class="text-gray-400 text-sm">Tidak ada jadwal keberangkatan untuk hari ini</p>
          </div>
          
          <div v-else class="space-y-4">
            <div v-for="booking in bookings" :key="booking.id" class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <div class="flex items-center mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">
                      {{ formatNamaPenumpang(booking) }}
                    </h3>
                    <span v-if="isMultipleBooking(booking)" class="ml-2 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                      {{ booking.details.length }} Penumpang
                    </span>
                  </div>
                  <p class="text-sm text-gray-600">
                    Kode: <span class="font-mono font-semibold">{{ booking.kode_pemesanan }}</span>
                  </p>
                </div>
                
                <div class="text-right">
                  <span v-if="booking.status === 'selesai'" class="bg-green-100 text-green-800 px-3 py-2 rounded-full text-sm font-medium flex items-center">
                    <span class="mr-1">✅</span> Sudah Diperiksa
                  </span>
                  <button v-else @click="checkPassenger(booking.id)" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center transition-colors">
                    <span class="mr-2">👁️</span> Periksa Penumpang
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Rute</p>
                  <p class="font-semibold text-gray-800">{{ booking.jadwal?.nama_rute }}</p>
                  <p class="text-sm text-gray-600">{{ booking.jadwal?.kota_keberangkatan }} → {{ booking.jadwal?.kota_tujuan }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500 mb-1">Keberangkatan</p>
                  <p class="font-semibold text-gray-800">{{ formatTime(booking.jadwal?.waktu_keberangkatan) }}</p>
                  <p class="text-sm text-gray-600">Kursi: {{ formatKursiList(booking) }}</p>
                </div>
              </div>

              <div v-if="isMultipleBooking(booking)" class="mt-4 p-4 bg-gray-50 rounded-lg border">
                <p class="text-sm font-medium text-gray-700 mb-3">Detail Penumpang:</p>
                <div class="space-y-3">
                  <div v-for="(detail, index) in booking.details" :key="index" class="flex justify-between items-center p-3 bg-white rounded border">
                    <div>
                      <p class="font-medium text-gray-800">{{ detail.nama_penumpang }}</p>
                      <p class="text-sm text-gray-500">{{ detail.telepon_penumpang }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-medium">
                      Kursi {{ detail.nomor_kursi }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>Booking dibuat: {{ formatDateTime(booking.created_at) }}</span>
                  <span v-if="booking.status === 'selesai'" class="text-green-600 font-medium">
                    ✓ Sudah check-in
                  </span>
                  <span v-else class="text-yellow-600 font-medium">
                    ⏳ Pending check-in
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>