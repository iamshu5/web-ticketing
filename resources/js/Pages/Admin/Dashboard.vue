<!-- resources/js/Pages/Admin/Dashboard.vue -->
<script setup>
import { router, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { ref, computed } from 'vue'

const props = defineProps({
  pendingBookings: {
    type: Array,
    default: () => []
  },
  jadwal: {
    type: Array,
    default: () => []
  }
})

const showTambahJadwal = ref(false)
const formJadwal = useForm({
  nama_rute: '',
  kota_keberangkatan: '',
  kota_tujuan: '',
  waktu_keberangkatan: '',
  waktu_tiba: '',
  jumlah_kursi: '',
  harga: ''
})

const stats = computed(() => {
  const totalBookingPending = props.pendingBookings?.length || 0
  const totalJadwal = props.jadwal?.length || 0
  
  const totalKursiTerpesan = props.jadwal?.reduce((total, jadwal) => {
    return total + (jadwal.jumlah_kursi_terpesan || 0)
  }, 0) || 0

  const totalKursiTersedia = props.jadwal?.reduce((total, jadwal) => {
    return total + (jadwal.jumlah_kursi - (jadwal.jumlah_kursi_terpesan || 0))
  }, 0) || 0

  return {
    totalBookingPending,
    totalJadwal,
    totalKursiTerpesan,
    totalKursiTersedia
  }
})

const confirmBooking = (id) => {
  if (confirm('Konfirmasi pemesanan ini?')) {
    router.post(`/admin/booking/${id}/konfirmasi`)
  }
}

const tambahJadwal = () => {
  formJadwal.post('/admin/jadwal', {
    onSuccess: () => {
      showTambahJadwal.value = false
      formJadwal.reset()
    }
  })
}

const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID')
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const formatTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const persentaseTerpesan = (jadwal) => {
  if (!jadwal.jumlah_kursi) return 0
  const terpesan = jadwal.jumlah_kursi_terpesan || 0
  return Math.round((terpesan / jadwal.jumlah_kursi) * 100)
}

const getKursiColor = (jadwal) => {
  const persentase = persentaseTerpesan(jadwal)
  if (persentase >= 90) return 'text-red-600 bg-red-50'
  if (persentase >= 70) return 'text-orange-600 bg-orange-50'
  if (persentase >= 50) return 'text-yellow-600 bg-yellow-50'
  return 'text-green-600 bg-green-50'
}
</script>

<template>
  <AuthLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin</h1>
        <p class="text-gray-600">Kelola booking dan jadwal</p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg mr-4">
              <span class="text-blue-600 text-2xl">⏳</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Booking Pending</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.totalBookingPending }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg mr-4">
              <span class="text-green-600 text-2xl">📅</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total Jadwal</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.totalJadwal }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg mr-4">
              <span class="text-purple-600 text-2xl">💺</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Kursi Terpesan</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.totalKursiTerpesan }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-lg mr-4">
              <span class="text-orange-600 text-2xl">🪑</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Kursi Tersedia</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.totalKursiTersedia }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-800">Booking Menunggu Konfirmasi</h2>
          <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
            {{ pendingBookings?.length || 0 }} menunggu
          </span>
        </div>
        <div class="p-6">
          <div v-if="!pendingBookings || pendingBookings.length === 0" class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">✅</div>
            <p class="text-gray-500 text-lg">Tidak ada booking yang menunggu konfirmasi</p>
            <p class="text-gray-400 text-sm mt-1">Semua booking sudah dikonfirmasi</p>
          </div>
          
          <div v-else class="space-y-4">
            <div v-for="booking in pendingBookings" :key="booking.id" class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center mb-3">
                    <h3 class="text-lg font-semibold text-gray-800">{{ booking.nama_penumpang }}</h3>
                    <span class="ml-3 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                      Menunggu Konfirmasi
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                      <p class="text-gray-600">
                        <span class="font-medium">Kode:</span> 
                        <span class="font-mono ml-2">{{ booking.kode_pemesanan }}</span>
                      </p>
                      <p class="text-gray-600">
                        <span class="font-medium">Kursi:</span> 
                        <span class="ml-2">No. {{ booking.nomor_kursi }}</span>
                      </p>
                      <p class="text-gray-600">
                        <span class="font-medium">Telepon:</span> 
                        <span class="ml-2">{{ booking.telepon_penumpang }}</span>
                      </p>
                    </div>
                    <div>
                      <p class="text-gray-600 font-medium">{{ booking.jadwal?.nama_rute }}</p>
                      <p class="text-gray-500">
                        {{ formatDate(booking.jadwal?.waktu_keberangkatan) }}
                      </p>
                      <p class="text-gray-500">
                        {{ formatTime(booking.jadwal?.waktu_keberangkatan) }} WIB
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="text-right ml-6">
                  <p class="text-xl font-bold text-blue-600 mb-3">
                    Rp {{ formatPrice(booking.jadwal?.harga) }}
                  </p>
                  <button @click="confirmBooking(booking.id)" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors flex items-center">
                    <span class="mr-2">✅</span>
                    Konfirmasi
                  </button>
                </div>
              </div>

              <div v-if="booking.bukti_pembayaran" class="mt-4 p-3 bg-green-50 rounded border border-green-200">
                <p class="text-sm text-green-700">
                  📎 Bukti pembayaran sudah diupload
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-800">Daftar Jadwal</h2>
          <button @click="showTambahJadwal = !showTambahJadwal" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
            + Tambah Jadwal
          </button>
        </div>

        <!-- Form Tambah Jadwal -->
        <div v-if="showTambahJadwal" class="p-6 border-b bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Jadwal Baru</h3>
          <form @submit.prevent="tambahJadwal" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Rute *</label>
              <input v-model="formJadwal.nama_rute" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Jakarta - Bandung">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
              <input v-model="formJadwal.harga" type="number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: 150000">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kota Keberangkatan *</label>
              <input v-model="formJadwal.kota_keberangkatan" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Jakarta">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kota Tujuan *</label>
              <input v-model="formJadwal.kota_tujuan" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Bandung">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Keberangkatan *</label>
              <input v-model="formJadwal.waktu_keberangkatan" type="datetime-local" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Tiba *</label>
              <input v-model="formJadwal.waktu_tiba" type="datetime-local" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kursi *</label>
              <input v-model="formJadwal.jumlah_kursi" type="number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: 30">
            </div>
            <div class="flex items-end">
              <button type="submit" :disabled="formJadwal.processing" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors disabled:bg-gray-400">
                {{ formJadwal.processing ? 'Menyimpan...' : 'Simpan Jadwal' }}
              </button>
            </div>
          </form>
        </div>

        <div class="p-6">
          <div v-if="!jadwal || jadwal.length === 0" class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">🚌</div>
            <p class="text-gray-500 text-lg">Belum ada jadwal tersedia</p>
            <p class="text-gray-400 text-sm mt-1">Tambahkan jadwal baru untuk memulai</p>
          </div>
          
          <div v-else class="space-y-4">
            <div v-for="schedule in jadwal" :key="schedule.id" class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center mb-3">
                    <h3 class="text-lg font-semibold text-gray-800">{{ schedule.nama_rute }}</h3>
                    <span :class="['ml-3 px-3 py-1 rounded-full text-sm font-medium',
                      schedule.status === 'aktif' 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-red-100 text-red-800'
                    ]">
                      {{ schedule.status }}
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-3">
                    <div>
                      <p class="text-gray-600">
                        <span class="font-medium">Rute:</span> 
                        <span class="ml-2">{{ schedule.kota_keberangkatan }} → {{ schedule.kota_tujuan }}</span>
                      </p>
                      <p class="text-gray-600">
                        <span class="font-medium">Keberangkatan:</span> 
                        <span class="ml-2">{{ formatDateTime(schedule.waktu_keberangkatan) }}</span>
                      </p>
                      <p class="text-gray-600">
                        <span class="font-medium">Tiba:</span> 
                        <span class="ml-2">{{ formatDateTime(schedule.waktu_tiba) }}</span>
                      </p>
                    </div>
                    <div>
                      <p class="text-gray-600">
                        <span class="font-medium">Harga:</span> 
                        <span class="ml-2 font-bold text-blue-600">Rp {{ formatPrice(schedule.harga) }}</span>
                      </p>
                      <p class="text-gray-600">
                        <span class="font-medium">Kursi:</span> 
                        <span class="ml-2">
                          <span :class="['px-2 py-1 rounded text-xs font-medium', getKursiColor(schedule)]">
                            {{ schedule.jumlah_kursi_terpesan || 0 }} terpesan
                          </span>
                          dari {{ schedule.jumlah_kursi }} total
                          ({{ persentaseTerpesan(schedule) }}%)
                        </span>
                      </p>
                      <p class="text-gray-600">
                        <span class="font-medium">Tersedia:</span> 
                        <span class="ml-2 font-semibold">
                          {{ schedule.jumlah_kursi - (schedule.jumlah_kursi_terpesan || 0) }} kursi
                        </span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-3">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                  <span>Kursi Terpesan</span>
                  <span>{{ persentaseTerpesan(schedule) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div :class="['h-2 rounded-full transition-all',
                    persentaseTerpesan(schedule) >= 90 ? 'bg-red-500' :
                    persentaseTerpesan(schedule) >= 70 ? 'bg-orange-500' :
                    persentaseTerpesan(schedule) >= 50 ? 'bg-yellow-500' : 'bg-green-500'
                  ]":style="{ width: persentaseTerpesan(schedule) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>