<!-- resources/js/Pages/Booking/Buat.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
  jadwal: Array
})

const jadwalTerpilih = ref(null)
const showPreview = ref(false)

const form = ref({
  jadwal_id: null,
  penumpang: [
    {
      nomor_kursi: '',
      nama_penumpang: '',
      telepon_penumpang: ''
    }
  ]
})

watch(jadwalTerpilih, (newJadwal) => {
  if (newJadwal) {
    form.value.jadwal_id = newJadwal.id
    resetFormPenumpang()
    showPreview.value = false
  }
})

const resetFormPenumpang = () => {
  form.value.penumpang = [
    {
      nomor_kursi: '',
      nama_penumpang: '',
      telepon_penumpang: ''
    }
  ]
}

const tambahPenumpang = () => {
  if (form.value.penumpang.length < jumlahKursiTersedia.value) {
    form.value.penumpang.push({
      nomor_kursi: '',
      nama_penumpang: '',
      telepon_penumpang: ''
    })
  }
}

const hapusPenumpang = (index) => {
  if (form.value.penumpang.length > 1) {
    form.value.penumpang.splice(index, 1)
  }
}

const bisaTambahPenumpang = computed(() => {
  if (!jadwalTerpilih.value) return false
  return form.value.penumpang.length < jumlahKursiTersedia.value
})

const kursiTersedia = computed(() => {
  if (!jadwalTerpilih.value) return []
  
  const jumlahKursiTotal = jadwalTerpilih.value.jumlah_kursi || 0
  
  const kursiTerpilihSaatIni = form.value.penumpang
    .map(p => p.nomor_kursi)
    .filter(k => k)
  
  const kursiTerpesanDiDatabase = jadwalTerpilih.value.kursi_terpesan || []
  
  const semuaKursi = Array.from(
    { length: jumlahKursiTotal }, 
    (_, i) => i + 1
  )

  return semuaKursi.filter(kursi => 
    !kursiTerpilihSaatIni.includes(kursi) && 
    !kursiTerpesanDiDatabase.includes(kursi)
  )
})

const jumlahKursiTersedia = computed(() => {
  if (!jadwalTerpilih.value) return 0
  
  const jumlahKursiTotal = jadwalTerpilih.value.jumlah_kursi || 0
  const jumlahKursiTerpesan = jadwalTerpilih.value.jumlah_kursi_terpesan || 0
  
  return jumlahKursiTotal - jumlahKursiTerpesan
})

const totalHarga = computed(() => {
  if (!jadwalTerpilih.value) return 0
  return jadwalTerpilih.value.harga * form.value.penumpang.length
})

const pilihJadwal = (jadwal) => {
  jadwalTerpilih.value = jadwal
  form.value.jadwal_id = jadwal.id
  showPreview.value = false
}

const tampilkanPreview = () => {
  if (!formValid.value) return
  showPreview.value = true
}

const kembaliKeForm = () => {
  showPreview.value = false
}

const kirimBooking = () => {
  const formData = {
    ...form.value,
    penumpang: form.value.penumpang.map(p => ({
      ...p,
      nomor_kursi: Number(p.nomor_kursi)
    }))
  }
  
  router.post('/booking', formData)
}

const formValid = computed(() => {
  if (!form.value.jadwal_id) return false
  
  return form.value.penumpang.every(penumpang => 
    penumpang.nomor_kursi && 
    penumpang.nama_penumpang.trim() && 
    penumpang.telepon_penumpang.trim()
  )
})

const getNomorKursi = (penumpang) => {
  return penumpang.nomor_kursi ? Number(penumpang.nomor_kursi) : ''
}

const formatTanggal = (tanggal) => {
  return new Date(tanggal).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatWaktu = (tanggal) => {
  return new Date(tanggal).toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const formatHarga = (harga) => {
  return new Intl.NumberFormat('id-ID').format(harga)
}

const formatDurasi = (berangkat, tiba) => {
  const diffMs = new Date(tiba) - new Date(berangkat)
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60))
  return `${diffHours}j ${diffMinutes}m`
}

const debugInfo = computed(() => {
  if (!jadwalTerpilih.value) return null
  
  return {
    totalKursi: jadwalTerpilih.value.jumlah_kursi,
    terpesanDiDatabase: jadwalTerpilih.value.kursi_terpesan || [],
    terpilihSaatIni: form.value.penumpang.map(p => p.nomor_kursi).filter(k => k),
    tersedia: kursiTersedia.value
  }
})
</script>

<template>
  <AuthLayout>
    <div class="container mx-auto px-4 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Pemesanan Tiket</h1>
        <p class="text-gray-600">Pilih jadwal dan kursi</p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Pilih Jadwal Keberangkatan</h2>
            <div v-if="jadwal.length === 0" class="text-center py-12 bg-white rounded-lg border">
              <p class="text-gray-500">Tidak ada jadwal tersedia</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="jadwalItem in jadwal" :key="jadwalItem.id" 
                :class="[
                  'bg-white rounded-lg border-2 p-6 transition-all cursor-pointer hover:shadow-md',
                  jadwalTerpilih?.id === jadwalItem.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'
                ]" @click="pilihJadwal(jadwalItem)"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="flex items-center mb-2">
                      <h3 class="text-xl font-semibold text-gray-800">{{ jadwalItem.nama_rute }}</h3>
                      <span v-if="jadwalTerpilih?.id === jadwalItem.id" class="ml-3 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                        Terpilih
                      </span>
                    </div>
                    
                    <div class="flex items-center text-gray-600 mb-3">
                      <span class="font-medium">{{ jadwalItem.kota_keberangkatan }}</span>
                      <span class="mx-3">→</span>
                      <span class="font-medium">{{ jadwalItem.kota_tujuan }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                      <div>
                        <p class="font-medium">Keberangkatan</p>
                        <p>{{ formatTanggal(jadwalItem.waktu_keberangkatan) }}</p>
                        <p class="text-blue-600 font-semibold">{{ formatWaktu(jadwalItem.waktu_keberangkatan) }} WIB</p>
                      </div>
                      <div>
                        <p class="font-medium">Tiba</p>
                        <p>{{ formatTanggal(jadwalItem.waktu_tiba) }}</p>
                        <p class="text-green-600 font-semibold">{{ formatWaktu(jadwalItem.waktu_tiba) }} WIB</p>
                      </div>
                    </div>

                    <div class="mt-3 text-sm text-gray-500">
                      ⏱️ Durasi: {{ formatDurasi(jadwalItem.waktu_keberangkatan, jadwalItem.waktu_tiba) }}
                    </div>
                  </div>

                  <div class="text-right ml-4">
                    <p class="text-2xl font-bold text-blue-600 mb-2">Rp {{ formatHarga(jadwalItem.harga) }}</p>
                    <div class="text-sm">
                      <p class="text-gray-600">
                        Kursi tersedia: 
                        <span 
                          :class="[
                            'font-semibold',
                            jadwalItem.jumlah_kursi_tersedia > 5 
                              ? 'text-green-600' 
                              : jadwalItem.jumlah_kursi_tersedia > 0 
                              ? 'text-orange-600' 
                              : 'text-red-600'
                          ]">
                          {{ jadwalItem.jumlah_kursi_tersedia }}
                        </span>
                        / {{ jadwalItem.jumlah_kursi }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div v-if="!showPreview" class="bg-white rounded-lg shadow-md border p-6 sticky top-6">
            <div v-if="jadwalTerpilih">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Data Penumpang</h2>
                <button v-if="bisaTambahPenumpang" @click="tambahPenumpang" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition-colors" :disabled="!bisaTambahPenumpang">
                  + Tambah Penumpang
                </button>
                <button v-else class="bg-gray-300 text-gray-500 px-3 py-1 rounded text-sm cursor-not-allowed" disabled>
                  Kursi Penuh!
                </button>
              </div>
              
              <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p class="font-medium text-blue-800 text-sm">{{ jadwalTerpilih.nama_rute }}</p>
                <p class="text-blue-700 text-xs">
                  {{ formatTanggal(jadwalTerpilih.waktu_keberangkatan) }} • 
                  {{ formatWaktu(jadwalTerpilih.waktu_keberangkatan) }}
                </p>
                <p class="text-blue-600 text-xs mt-1">
                  💺 Tersedia: {{ jumlahKursiTersedia }} kursi • 
                  💰 Harga: Rp {{ formatHarga(jadwalTerpilih.harga) }}/orang
                </p>
                <p v-if="debugInfo" class="text-xs text-gray-500 mt-1">
                  {{ debugInfo.terpesanDiDatabase.length }} kursi terpesan di database
                </p>
              </div>

              <div>
                <div v-for="(penumpang, index) in form.penumpang" :key="index" class="mb-6 p-4 border border-gray-200 rounded-lg">
                  <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700">Penumpang {{ index + 1 }}</h3>
                    <button v-if="form.penumpang.length > 1" @click="hapusPenumpang(index)" type="button" class="text-red-500 hover:text-red-700 text-sm transition-colors">
                      Hapus
                    </button>
                  </div>

                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Kursi *
                      </label>
                      <select v-model="penumpang.nomor_kursi" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" :class="{'border-red-300': !penumpang.nomor_kursi}">
                        <option value="">Pilih nomor kursi</option>
                        <option v-for="kursi in kursiTersedia" :key="kursi" :value="kursi">
                          Kursi {{ kursi }}
                        </option>
                      </select>
                      
                      <p class="text-xs text-green-600 font-medium mt-1" v-if="penumpang.nomor_kursi">
                        Terpilih: Kursi {{ getNomorKursi(penumpang) }}
                      </p>
                      <p class="text-xs text-red-500 mt-1" v-else>
                        Silakan pilih kursi
                      </p>
                      
                      <p v-if="kursiTersedia.length === 0" class="text-xs text-red-500 mt-1">
                        Tidak ada kursi tersedia
                      </p>
                      <p v-else class="text-xs text-gray-500 mt-1">
                        {{ kursiTersedia.length }} kursi tersedia
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap *
                      </label>
                      <input type="text" v-model="penumpang.nama_penumpang" placeholder="Masukkan nama lengkap" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" :class="{'border-red-300': !penumpang.nama_penumpang.trim()}">
                      <p v-if="!penumpang.nama_penumpang.trim()" class="text-xs text-red-500 mt-1">
                        Nama penumpang wajib diisi
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nomor Telepon *
                      </label>
                      <input type="tel" v-model="penumpang.telepon_penumpang" placeholder="Contoh: 081234567890" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" :class="{'border-red-300': !penumpang.telepon_penumpang.trim()}">
                      <p v-if="!penumpang.telepon_penumpang.trim()" class="text-xs text-red-500 mt-1">
                        Nomor telepon wajib diisi
                      </p>
                    </div>
                  </div>
                </div>

                <div class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                  <div class="flex justify-between items-center">
                    <span class="text-purple-700 font-medium">Total Pembayaran:</span>
                    <span class="text-lg font-bold text-purple-600">
                      Rp {{ formatHarga(totalHarga) }}
                    </span>
                  </div>
                  <p class="text-xs text-purple-600 mt-1 text-right">
                    {{ form.penumpang.length }} orang × Rp {{ formatHarga(jadwalTerpilih.harga) }}
                  </p>
                </div>

                <button @click="tampilkanPreview" :disabled="!formValid" 
                  :class="[
                    'w-full py-3 px-4 rounded-lg font-semibold transition-colors mb-3',
                    formValid 
                      ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' 
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  ]"
                > 📋 Preview Booking ({{ form.penumpang.length }} penumpang)
                </button>

                <p v-if="!formValid" class="text-xs text-red-500 text-center">
                  ⚠️ Harap lengkapi semua data penumpang terlebih dahulu
                </p>
              </div>
            </div>

            <div v-else class="text-center py-8">
              <div class="text-4xl text-gray-300 mb-4">📍</div>
              <p class="text-gray-500">Mo kemana?</p>
              <p class="text-gray-400 text-sm mt-2">Klik jadwal tersedia di sebelah kiri</p>
            </div>
          </div>

          <div v-else class="bg-white rounded-lg shadow-md border p-6 sticky top-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Preview Booking</h2>
            
            <div class="space-y-4 mb-6">
              <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-blue-800 mb-2">📍 Detail Perjalanan</h3>
                <p class="text-blue-700 font-medium">🗺️ {{ jadwalTerpilih.nama_rute }}</p>
                <p class="text-sm text-blue-600 mt-1">
                  {{ jadwalTerpilih.kota_keberangkatan }} → {{ jadwalTerpilih.kota_tujuan }}
                </p>
                <p class="text-sm text-blue-600">
                  🗓️ {{ formatTanggal(jadwalTerpilih.waktu_keberangkatan) }}
                </p>
                <p class="text-sm text-blue-600">
                  🕘 {{ formatWaktu(jadwalTerpilih.waktu_keberangkatan) }} WIB
                </p>
              </div>

              <div v-for="(penumpang, index) in form.penumpang" :key="index" class="p-4 bg-green-50 rounded-lg border border-green-200">
                <h3 class="font-semibold text-green-800 mb-2">👤 Penumpang {{ index + 1 }}</h3>
                <div class="space-y-1">
                  <p class="text-green-700">
                    <span class="font-medium">Nama:</span> {{ penumpang.nama_penumpang }}
                  </p>
                  <p class="text-sm text-green-600">
                    <span class="font-medium">Telp:</span> {{ penumpang.telepon_penumpang }}
                  </p>
                  <p class="text-sm text-green-600">
                    <span class="font-medium">Kursi:</span> {{ getNomorKursi(penumpang) }}
                  </p>
                </div>
              </div>

              <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h3 class="font-semibold text-purple-800 mb-2">💰 Detail Harga</h3>
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-purple-600">{{ form.penumpang.length }} orang × Rp {{ formatHarga(jadwalTerpilih.harga) }}</span>
                    <span class="text-purple-700 font-medium">Rp {{ formatHarga(totalHarga) }}</span>
                  </div>
                  <div class="flex justify-between items-center border-t border-purple-200 pt-2">
                    <span class="text-purple-700 font-semibold">Total Pembayaran</span>
                    <span class="text-xl font-bold text-purple-600">
                      Rp {{ formatHarga(totalHarga) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <button @click="kirimBooking" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center">
                <span class="mr-2">✅</span>
                Konfirmasi & Pesan Tiket ({{ form.penumpang.length }} penumpang)
              </button>
              
              <button @click="kembaliKeForm" class="w-full bg-gray-500 text-white py-2 px-4 rounded-lg font-medium hover:bg-gray-600 transition-colors flex items-center justify-center">
                <span class="mr-2">✏️</span>
                Edit Data
              </button>
            </div>

            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
              <p class="text-xs text-yellow-700">
                ⚠️ Pastikan data sudah benar. Setelah dikonfirmasi, pemesanan tidak dapat diubah.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>