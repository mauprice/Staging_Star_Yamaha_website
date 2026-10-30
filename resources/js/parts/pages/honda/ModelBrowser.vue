<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="w-36">
          <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Year</label>
          <select v-model="filters.year" @change="onFilterChange"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            <option value="">All Years</option>
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <div class="w-40">
          <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Type</label>
          <select v-model="filters.type" @change="onFilterChange"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            <option value="">All Types</option>
            <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>

        <div class="flex-1 min-w-48">
          <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Search Model</label>
          <input
            v-model="filters.search"
            @input="onFilterChange"
            type="text"
            placeholder="e.g. VT750, CBR600, XR250…"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
          />
        </div>

        <button @click="clearFilters"
          class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Clear
        </button>
      </div>
    </div>

    <!-- Results summary -->
    <div class="flex items-center justify-between mb-4">
      <div class="text-sm text-gray-500">
        <span v-if="!loading">{{ pagination.total?.toLocaleString() }} models found</span>
        <span v-else>Loading…</span>
      </div>
      <div class="text-sm text-gray-500" v-if="pagination.last_page > 1">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </div>
    </div>

    <!-- Loading spinner -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Vehicle grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <router-link
        v-for="v in vehicles"
        :key="v.model_id"
        :to="`/honda/model/${v.model_id}`"
        class="bg-white rounded-xl border border-gray-200 hover:border-red-400 hover:shadow-md transition-all group overflow-hidden flex flex-col p-3"
      >
        <div class="flex items-start justify-between gap-2 mb-1.5">
          <span v-if="v.type" class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-red-100 text-red-700">
            {{ v.type }}
          </span>
          <span v-if="v.year" class="text-xs font-bold text-gray-400">{{ v.year }}</span>
        </div>

        <h3 class="font-semibold text-gray-900 text-sm leading-snug group-hover:text-red-700 transition-colors">
          {{ v.model }}
        </h3>

        <p v-if="v.model_name" class="text-xs text-gray-500 mt-0.5 truncate">{{ v.model_name }}</p>

        <div class="mt-auto pt-2 flex items-center justify-between">
          <span v-if="v.code" class="text-xs text-gray-400 truncate max-w-[60%]">{{ v.code }}</span>
          <span class="text-xs text-red-600 font-medium group-hover:underline ml-auto">View Parts →</span>
        </div>
      </router-link>
    </div>

    <!-- Empty state -->
    <div v-if="!loading && vehicles.length === 0" class="text-center py-20 text-gray-400">
      <div class="text-4xl mb-3">🔍</div>
      <div class="font-medium">No models found</div>
      <div class="text-sm mt-1">Try adjusting your filters</div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
      <button
        v-for="page in pageNumbers"
        :key="page"
        @click="goToPage(page)"
        :disabled="page === '…'"
        class="px-3 py-1.5 text-sm rounded-lg transition-colors"
        :class="page === pagination.current_page
          ? 'bg-red-600 text-white'
          : page === '…'
            ? 'text-gray-400 cursor-default'
            : 'bg-white border border-gray-300 hover:bg-gray-50'"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route  = useRoute()
const router = useRouter()

const vehicles    = ref([])
const years       = ref([])
const types       = ref([])
const loading     = ref(false)
const pagination  = ref({ total: 0, current_page: 1, last_page: 1 })

const filters = reactive({
  search: route.query.search || '',
  year:   route.query.year   || '',
  type:   route.query.type   || '',
})

const currentPage = ref(parseInt(route.query.page) || 1)

onMounted(async () => {
  const [y, t] = await Promise.all([
    axios.get('/api/parts-catalogue/honda/vehicles/years'),
    axios.get('/api/parts-catalogue/honda/vehicles/types'),
  ])
  years.value = y.data
  types.value = t.data
  fetchVehicles()
})

async function fetchVehicles() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/parts-catalogue/honda/vehicles', {
      params: { ...filters, page: currentPage.value, per_page: 48 },
    })
    vehicles.value    = data.data
    pagination.value  = { total: data.total, current_page: data.current_page, last_page: data.last_page }
  } finally {
    loading.value = false
  }
}

function onFilterChange() {
  currentPage.value = 1
  updateQuery()
  fetchVehicles()
}

function clearFilters() {
  filters.search = ''
  filters.year   = ''
  filters.type   = ''
  onFilterChange()
}

function goToPage(page) {
  if (page === '…') return
  currentPage.value = page
  updateQuery()
  fetchVehicles()
}

function updateQuery() {
  router.replace({ query: {
    ...(filters.search ? { search: filters.search } : {}),
    ...(filters.year   ? { year:   filters.year }   : {}),
    ...(filters.type   ? { type:   filters.type }   : {}),
    ...(currentPage.value > 1 ? { page: currentPage.value } : {}),
  }})
}

const pageNumbers = computed(() => {
  const total   = pagination.value.last_page
  const current = pagination.value.current_page
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  const pages = [1]
  if (current > 3) pages.push('…')
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
  if (current < total - 2) pages.push('…')
  pages.push(total)
  return pages
})
</script>
