<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
  bookings: {
    type: Array,
    default: () => []
  }
})

const activeFilter = ref('semua')

const filters = [
  { value: 'semua', label: 'Semua' },
  { value: 'pending', label: 'Menunggu Bayar' },
  { value: 'dibayar', label: 'Menunggu Konfirmasi' },
  { value: 'dikonfirmasi', label: 'Terkonfirmasi' },
  { value: 'selesai', label: 'Selesai' },
  { value: 'dibatalkan', label: 'Dibatalkan' }
]

const statusLabels = {
  'pending': 'Menunggu Pembayaran',
  'dibayar': 'Menunggu Konfirmasi',
  'dikonfirmasi': 'Terkonfirmasi',
  'selesai': 'Selesai',
  'dibatalkan': 'Dibatalkan'
}

const statusClasses = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'dibayar': 'bg-blue-100 text-blue-800',
    'dikonfirmasi': 'bg-green-100 text-green-800',
    'selesai': 'bg-gray-100 text-gray-800',
    'dibatalkan': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const filteredBookings = computed(() => {
  if (activeFilter.value === 'semua') {
    return props.bookings
  }
  return props.bookings.filter(booking => booking.status === activeFilter.value)
})

const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    })
}

const calculateTotalHarga = (booking) => {
  if (!booking.jadwal?.harga) return 0
  
  if (booking.details && booking.details.length > 0) {
    return booking.jadwal.harga * booking.details.length
  }
  
  return booking.jadwal.harga
}

const formatKursiList = (booking) => {
  if (booking.details && booking.details.length > 0) {
    const kursiList = booking.details.map(detail => detail.nomor_kursi)
    return kursiList.join(', ')
  }
  
  return booking.nomor_kursi || '-'
}

const formatPenumpangList = (booking) => {
  if (booking.details && booking.details.length > 0) {
    const penumpangList = booking.details.map(detail => detail.nama_penumpang)
    return penumpangList.join(', ')
  }
  
  return booking.nama_penumpang || '-'
}

const isMultipleBooking = (booking) => {
  return booking.details && booking.details.length > 1
}

const downloadTicket = (booking) => {
  alert(`Download tiket untuk ${booking.kode_pemesanan}`)
}

const cancelBooking = (id) => {
  if (confirm('Yakin ingin membatalkan pemesanan ini?')) {
    router.post(`/booking/${id}/batal`)
  }
}
</script>

<template>
  <AuthLayout>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Pemesanan Saya</h1>
      <p class="text-gray-600">Lihat riwayat dan status pemesanan tiket Anda</p>
    </div>

    <div class="mb-6">
      <div class="flex space-x-2 overflow-x-auto pb-2">
        <button v-for="filter in filters" :key="filter.value" @click="activeFilter = filter.value"
          :class="[
            'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap',
            activeFilter === filter.value 
              ? 'bg-blue-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>

    <div class="space-y-4">
      <div v-if="filteredBookings.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📭</div>
        <p class="text-gray-500">Tidak ada booking</p>
        <Link href="/booking/buat" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
          Pesan Tiket Sekarang
        </Link>
      </div>

      <div v-else v-for="booking in filteredBookings" :key="booking.id" class="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">
                  {{ formatPenumpangList(booking) }}
                  <span v-if="isMultipleBooking(booking)" class="ml-2 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                    {{ booking.details.length }} Penumpang
                  </span>
                </h3>
                <p class="text-sm text-gray-600">Kode: <span class="font-mono">{{ booking.kode_pemesanan }}</span></p>
              </div>
              <span :class="statusClasses(booking.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                {{ statusLabels[booking.status] }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <p class="text-sm text-gray-500">Rute</p>
                <p class="font-medium">{{ booking.jadwal?.nama_rute }}</p>
                <p class="text-sm text-gray-600">{{ booking.jadwal?.kota_keberangkatan }} → {{ booking.jadwal?.kota_tujuan }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Keberangkatan</p>
                <p class="font-medium">{{ formatDateTime(booking.jadwal?.waktu_keberangkatan) }}</p>
              </div>
            </div>

            <div class="flex justify-between items-center border-t pt-4">
              <div>
                <p class="text-sm text-gray-500">Kursi</p>
                <p class="font-medium">No. {{ formatKursiList(booking) }}</p>
                <p v-if="isMultipleBooking(booking)" class="text-xs text-gray-500 mt-1">
                  {{ booking.details.length }} kursi terpesan
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-500">Total Bayar</p>
                <p class="text-xl font-bold text-blue-600">Rp {{ formatPrice(calculateTotalHarga(booking)) }}</p>
                <p v-if="isMultipleBooking(booking)" class="text-xs text-gray-500 mt-1">
                  {{ booking.details.length }} × Rp {{ formatPrice(booking.jadwal?.harga) }}
                </p>
              </div>
            </div>

            <div v-if="isMultipleBooking(booking)" class="mt-4 p-3 bg-gray-50 rounded border">
              <p class="text-sm font-medium text-gray-700 mb-2">Detail Penumpang:</p>
              <div class="space-y-2">
                <div v-for="(detail, index) in booking.details" :key="index" class="flex justify-between text-sm">
                  <span class="text-gray-600">{{ detail.nama_penumpang }}</span>
                  <span class="text-gray-500">Kursi {{ detail.nomor_kursi }} • {{ detail.telepon_penumpang }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-4 pt-4 border-t">
          <Link v-if="booking.status === 'pending'" :href="`/booking/${booking.id}/konfirmasi`" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
            Upload Bukti Bayar
          </Link>
          
          <button v-if="booking.status === 'dikonfirmasi'" @click="downloadTicket(booking)" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
            Download Tiket
          </button>

          <button v-if="['pending', 'dibayar'].includes(booking.status)" @click="cancelBooking(booking.id)" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">
            Batalkan
          </button>

          <Link :href="`/booking/${booking.id}/konfirmasi`" class="bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
            Lihat Detail
          </Link>
        </div>

        <div v-if="booking.bukti_pembayaran" class="mt-3 p-3 bg-green-50 rounded border border-green-200">
          <p class="text-sm text-green-700">
            ✅ Bukti pembayaran sudah diupload pada {{ formatDateTime(booking.tanggal_pembayaran) }}
          </p>
        </div>

        <div v-if="booking.catatan" class="mt-3 p-3 bg-blue-50 rounded border border-blue-200">
          <p class="text-sm text-blue-700">
            📝 {{ booking.catatan }}
          </p>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>