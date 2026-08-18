<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
          <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Search Model</label>
          <input
            v-model="filters.search"
            @input="onFilterChange"
            type="text"
            placeholder="e.g. YZF-R1, WR450F, F150…"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="w-36">
          <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Year</label>
          <select v-model="filters.year" @change="onFilterChange"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Years</option>
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <div class="w-52">
          <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Type</label>
          <select v-model="filters.type" @change="onFilterChange"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Types</option>
            <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>

        <div class="w-44">
          <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Catalogue</label>
          <select v-model="filters.catalogue" @change="onFilterChange"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Catalogues</option>
            <option value="MB">MB — Motorcycle / ATV</option>
            <option value="MA">MA — Marine / Outboard</option>
          </select>
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
      <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Product grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <router-link
        v-for="p in products"
        :key="p.product_id"
        :to="`/model/${p.product_id}`"
        class="bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-md transition-all group overflow-hidden flex flex-col"
      >
        <!-- Thumbnail strip -->
        <div class="h-28 bg-gray-100 flex items-center justify-center overflow-hidden relative shrink-0">
          <img
            v-if="p.thumbnail_url"
            :src="p.thumbnail_url"
            :alt="p.model"
            class="w-full h-full object-cover object-center opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-300"
            loading="lazy"
          />
          <div v-else class="flex flex-col items-center gap-1 text-gray-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
            </svg>
            <span class="text-[10px]">No image yet</span>
          </div>
          <!-- Year badge overlaid on image -->
          <span v-if="p.year && p.thumbnail_url"
            class="absolute top-1.5 right-2 text-[10px] font-bold text-white bg-black/40 rounded px-1.5 py-0.5">
            {{ p.year }}
          </span>
        </div>

        <!-- Card body -->
        <div class="p-3 flex flex-col flex-1">
          <div class="flex items-start justify-between gap-2 mb-1.5">
            <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full"
              :class="typeClass(p.type)">
              {{ typeName(p.type) }}
            </span>
            <span v-if="p.year && !p.thumbnail_url" class="text-xs font-bold text-gray-400">{{ p.year }}</span>
          </div>

          <h3 class="font-semibold text-gray-900 text-sm leading-snug group-hover:text-blue-700 transition-colors">
            {{ p.model }}
          </h3>

          <p v-if="p.common_name" class="text-xs text-gray-500 mt-0.5 truncate">{{ p.common_name }}</p>

          <div class="mt-auto pt-2 flex items-center justify-between">
            <span v-if="p.prefix" class="text-xs text-gray-400 truncate max-w-[60%]">{{ p.prefix }}</span>
            <span class="text-xs text-blue-600 font-medium group-hover:underline ml-auto">View Parts →</span>
          </div>
        </div>
      </router-link>
    </div>

    <!-- Empty state -->
    <div v-if="!loading && products.length === 0" class="text-center py-20 text-gray-400">
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
          ? 'bg-blue-600 text-white'
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
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route  = useRoute()
const router = useRouter()

const products   = ref([])
const years      = ref([])
const types      = ref([])
const loading    = ref(false)
const pagination = ref({ total: 0, current_page: 1, last_page: 1 })

const filters = reactive({
  search:    route.query.search    || '',
  year:      route.query.year      || '',
  type:      route.query.type      !== undefined ? route.query.type : '',
  catalogue: route.query.catalogue || '',
})

const currentPage = ref(parseInt(route.query.page) || 1)

onMounted(async () => {
  const [y, t] = await Promise.all([
    axios.get('/api/parts-catalogue/products/years'),
    axios.get('/api/parts-catalogue/products/types'),
  ])
  years.value = y.data
  types.value = t.data
  fetchProducts()
})

async function fetchProducts() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/parts-catalogue/products', {
      params: { ...filters, page: currentPage.value, per_page: 48 },
    })
    products.value   = data.data
    pagination.value = { total: data.total, current_page: data.current_page, last_page: data.last_page }
  } finally {
    loading.value = false
  }
}

function onFilterChange() {
  currentPage.value = 1
  updateQuery()
  fetchProducts()
}

function clearFilters() {
  filters.search    = ''
  filters.year      = ''
  filters.type      = ''
  filters.catalogue = ''
  onFilterChange()
}

function goToPage(page) {
  if (page === '…') return
  currentPage.value = page
  updateQuery()
  fetchProducts()
}

function updateQuery() {
  router.replace({ query: {
    ...(filters.search    ? { search:    filters.search }    : {}),
    ...(filters.year      ? { year:      filters.year }      : {}),
    ...(filters.type      !== '' ? { type:      filters.type }      : {}),
    ...(filters.catalogue !== '' ? { catalogue: filters.catalogue } : {}),
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

const TYPE_LABELS = { 0: 'Motorcycle', 1: 'Outboard', 2: 'ATV', 3: 'Snowmobile', 4: 'PWC', 5: 'Boat' }
const TYPE_CLASSES = {
  0: 'bg-blue-100 text-blue-700',
  1: 'bg-cyan-100 text-cyan-700',
  2: 'bg-green-100 text-green-700',
  3: 'bg-indigo-100 text-indigo-700',
  4: 'bg-purple-100 text-purple-700',
  5: 'bg-teal-100 text-teal-700',
}

function typeName(t) { return TYPE_LABELS[t] ?? 'Other' }
function typeClass(t) { return TYPE_CLASSES[t] ?? 'bg-gray-100 text-gray-600' }
</script>
