<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
})

const fileInput = ref(null)
const selectedFile = ref(null)
const dragOver = ref(false)

const form = useForm({
  bukti_pembayaran: null
})

const totalHarga = computed(() => {
  if (!props.booking.jadwal?.harga) return 0
  
  if (props.booking.details && props.booking.details.length > 0) {
    return props.booking.jadwal.harga * props.booking.details.length
  }
  
  return props.booking.jadwal.harga
})

const isMultipleBooking = computed(() => {
  return props.booking.details && props.booking.details.length > 1
})

const formatKursiList = computed(() => {
  if (props.booking.details && props.booking.details.length > 0) {
    const kursiList = props.booking.details.map(detail => detail.nomor_kursi)
    return kursiList.join(', ')
  }
  
  return props.booking.nomor_kursi || '-'
})


const statusConfig = computed(() => {
  const config = {
    'pending': {
      label: 'Menunggu Pembayaran',
      class: 'bg-yellow-100 text-yellow-800',
      icon: '⏳',
      showUpload: true,
      title: 'Booking Berhasil!',
      subtitle: 'Silakan upload bukti pembayaran untuk melanjutkan'
    },
    'dibayar': {
      label: 'Menunggu Konfirmasi',
      class: 'bg-blue-100 text-blue-800',
      icon: '✅',
      showUpload: false,
      title: 'Pembayaran Terkirim!',
      subtitle: 'Pembayaran Anda sedang menunggu konfirmasi admin'
    },
    'dikonfirmasi': {
      label: 'Terkonfirmasi',
      class: 'bg-green-100 text-green-800',
      icon: '🎉',
      showUpload: false,
      title: 'Pembayaran Dikonfirmasi!',
      subtitle: 'Pembayaran Anda telah dikonfirmasi, tiket siap digunakan'
    },
    'dibatalkan': {
      label: 'Dibatalkan',
      class: 'bg-red-100 text-red-800',
      icon: '❌',
      showUpload: false,
      title: 'Booking Dibatalkan',
      subtitle: 'Pemesanan ini telah dibatalkan'
    }
  }
  
  return config[props.booking.status] || config.pending
})

const selectFile = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    form.bukti_pembayaran = file
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  dragOver.value = false
  
  const file = event.dataTransfer.files[0]
  if (file) {
    selectedFile.value = file
    form.bukti_pembayaran = file
  }
}

const handleDragOver = (event) => {
  event.preventDefault()
  dragOver.value = true
}

const handleDragLeave = () => {
  dragOver.value = false
}

const removeFile = () => {
  selectedFile.value = null
  form.bukti_pembayaran = null
  fileInput.value.value = ''
}

const submitPayment = () => {
  if (!selectedFile.value) return
  
  form.post(`/booking/${props.booking.id}/unggah-bukti-pembayaran`, {
    onSuccess: () => {

    }
  })
}

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
</script>

<template>
  <AuthLayout>
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-8">
        <div class="text-6xl mb-4">{{ statusConfig.icon }}</div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ statusConfig.title }}</h1>
        <p class="text-gray-600">{{ statusConfig.subtitle }}</p>
      </div>

      <div v-if="statusConfig.showUpload" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
        <div class="flex items-center">
          <div class="text-blue-600 text-xl mr-3">💡</div>
          <div>
            <p class="text-blue-800 font-medium">Transfer ke rekening berikut:</p>
            <p class="text-blue-700">Bank BCA - 1234567890 a.n. PT. Mencari Cinta Sejati</p>
            <p class="text-blue-600 text-sm mt-1">
              Total yang harus dibayar: 
              <span class="font-bold">Rp {{ formatPrice(totalHarga) }}</span>
              <span v-if="isMultipleBooking" class="text-blue-500 ml-2">
                ({{ booking.details.length }} penumpang × Rp {{ formatPrice(booking.jadwal?.harga) }})
              </span>
            </p>
          </div>
        </div>
      </div>

      <div v-if="booking.status === 'dibayar'" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
        <div class="flex items-center">
          <div class="text-blue-600 text-xl mr-3">⏳</div>
          <div>
            <p class="text-blue-800 font-medium">Pembayaran Anda sedang dikonfirmasi</p>
            <p class="text-blue-700 text-sm mt-1">
              Bukti pembayaran telah diterima. Admin akan memverifikasi pembayaran Anda dalam 1x24 jam.
            </p>
          </div>
        </div>
      </div>

      <div v-if="booking.status === 'dikonfirmasi'" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-8">
        <div class="flex items-center">
          <div class="text-green-600 text-xl mr-3">✅</div>
          <div>
            <p class="text-green-800 font-medium">Pembayaran telah dikonfirmasi!</p>
            <p class="text-green-700 text-sm mt-1">
              Tiket Anda sudah aktif. Silakan tunjukkan tiket saat boarding.
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div :class="['lg:col-span-2', !statusConfig.showUpload ? 'lg:col-span-3' : 'lg:col-span-2']">
          <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-800">Detail Booking</h2>
              <div class="flex items-center space-x-2">
                <span v-if="isMultipleBooking" class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                  {{ booking.details.length }} Penumpang
                </span>
                <span :class="['px-3 py-1 rounded-full text-sm font-medium', statusConfig.class]">
                  {{ statusConfig.label }}
                </span>
              </div>
            </div>
            
            <div class="space-y-4">
              <div class="flex justify-between items-center py-3 border-b">
                <span class="text-gray-600">Kode Booking</span>
                <span class="font-mono font-bold text-blue-600">{{ booking.kode_pemesanan }}</span>
              </div>

              <div class="py-3 border-b">
                <p class="text-gray-600 mb-2">Rute Perjalanan</p>
                <p class="font-medium text-lg">{{ booking.jadwal?.nama_rute }}</p>
                <p class="text-gray-600">{{ booking.jadwal?.kota_keberangkatan }} → {{ booking.jadwal?.kota_tujuan }}</p>
              </div>

              <div class="flex justify-between items-center py-3 border-b">
                <span class="text-gray-600">Keberangkatan</span>
                <span class="font-medium text-right">{{ formatDateTime(booking.jadwal?.waktu_keberangkatan) }}</span>
              </div>

              <div class="flex justify-between items-center py-3 border-b">
                <span class="text-gray-600">Tiba</span>
                <span class="font-medium text-right">{{ formatDateTime(booking.jadwal?.waktu_tiba) }}</span>
              </div>

              <div class="flex justify-between items-center py-3 border-b">
                <span class="text-gray-600">Kursi</span>
                <span class="font-medium">No. {{ formatKursiList }}</span>
              </div>

              <div v-if="isMultipleBooking" class="py-3 border-b">
                <p class="text-gray-600 mb-3">Detail Penumpang</p>
                <div class="space-y-3">
                  <div v-for="(detail, index) in booking.details" :key="index" class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                      <p class="font-medium">{{ detail.nama_penumpang }}</p>
                      <p class="text-sm text-gray-500">{{ detail.telepon_penumpang }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                      Kursi {{ detail.nomor_kursi }}
                    </span>
                  </div>
                </div>
              </div>

              <div v-else class="py-3 border-b">
                <p class="text-gray-600 mb-2">Detail Penumpang</p>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                  <div>
                    <p class="font-medium">{{ booking.nama_penumpang }}</p>
                    <p class="text-sm text-gray-500">{{ booking.telepon_penumpang }}</p>
                  </div>
                  <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                    Kursi {{ booking.nomor_kursi }}
                  </span>
                </div>
              </div>

              <div class="py-3">
                <div class="space-y-2">
                  <div v-if="isMultipleBooking" class="flex justify-between text-sm text-gray-600">
                    <span>{{ booking.details.length }} penumpang × Rp {{ formatPrice(booking.jadwal?.harga) }}</span>
                    <span>Rp {{ formatPrice(booking.jadwal?.harga * booking.details.length) }}</span>
                  </div>
                  <div v-else class="flex justify-between text-sm text-gray-600">
                    <span>1 penumpang × Rp {{ formatPrice(booking.jadwal?.harga) }}</span>
                    <span>Rp {{ formatPrice(booking.jadwal?.harga) }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-lg font-semibold text-gray-800">Total Pembayaran</span>
                    <span class="text-2xl font-bold text-blue-600">Rp {{ formatPrice(totalHarga) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="booking.bukti_pembayaran && booking.status !== 'pending'" class="p-3 bg-green-50 rounded border border-green-200">
                <p class="text-sm text-green-700">
                  ✅ Bukti pembayaran diupload pada {{ formatDateTime(booking.tanggal_pembayaran) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="statusConfig.showUpload" class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload Bukti Bayar</h2>
            
            <form @submit.prevent="submitPayment">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Upload Bukti Transfer
                </label>
                
                <div @click="selectFile" @drop="handleDrop" @dragover="handleDragOver" @dragleave="handleDragLeave"
                  :class="[
                    'border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors',
                    dragOver ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400',
                    form.errors.bukti_pembayaran ? 'border-red-500' : ''
                  ]"
                >
                  <div class="text-4xl mb-2">📎</div>
                  <p class="text-sm text-gray-600 mb-2">
                    Klik atau drag file ke sini
                  </p>
                  <p class="text-xs text-gray-500">
                    Format: JPG, PNG, PDF (max 2MB)
                  </p>
                  <input type="file" ref="fileInput" @change="handleFileSelect" accept=".jpg,.jpeg,.png,.pdf" class="hidden"/>
                </div>

                <div v-if="selectedFile" class="mt-3 p-3 bg-green-50 rounded border border-green-200">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <span class="text-green-600 text-lg mr-2">✅</span>
                      <span class="text-sm text-green-700">{{ selectedFile.name }}</span>
                    </div>
                    <button type="button" @click="removeFile" class="text-red-500 hover:text-red-700">
                      ✕
                    </button>
                  </div>
                </div>

                <div v-if="form.errors.bukti_pembayaran" class="text-red-500 text-sm mt-2">
                  {{ form.errors.bukti_pembayaran }}
                </div>
              </div>

              <button type="submit" :disabled="form.processing || !selectedFile"
                :class="[
                  'w-full py-3 px-4 rounded-lg font-semibold transition-colors',
                  form.processing || !selectedFile
                    ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                    : 'bg-green-600 text-white hover:bg-green-700'
                ]"
              >
                <span v-if="form.processing" class="animate-spin mr-2">⟳</span>
                {{ form.processing ? 'Mengupload...' : 'Konfirmasi Pembayaran' }}
              </button>
            </form>

            <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
              <p class="text-sm text-yellow-800">
                ⚠️ Harap selesaikan pembayaran dan Upload Bukti Pembayaran!
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-between mt-8">
        <Link href="/booking/buat" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
          ← Pesan Tiket Lain
        </Link>
        <Link href="/booking" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
          Lihat Semua Pemesanan →
        </Link>
      </div>
    </div>
  </AuthLayout>
</template>