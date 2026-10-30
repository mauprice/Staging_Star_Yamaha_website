<template>
  <div class="max-w-4xl mx-auto px-4 py-6">
    <h1 class="text-xl font-bold text-gray-800 mb-4">Part Number Search</h1>

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
      <div class="flex gap-3">
        <input
          v-model="query"
          @keydown.enter="search"
          type="text"
          placeholder="Enter part number (e.g. 12301-149-020) or description…"
          class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
          autofocus
        />
        <button @click="search"
          :disabled="loading || query.length < 3"
          class="px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 transition-colors">
          Search
        </button>
      </div>
      <p class="text-xs text-gray-400 mt-2">Minimum 3 characters. Hyphens are optional.</p>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else-if="results.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm text-gray-500">
        {{ results.length }} result{{ results.length !== 1 ? 's' : '' }}
      </div>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-xs text-gray-500 uppercase tracking-wide border-b border-gray-200 bg-gray-50">
            <th class="px-4 py-2 text-left">Part Number</th>
            <th class="px-4 py-2 text-left">Description</th>
            <th class="px-4 py-2 text-right w-24">Price</th>
            <th class="px-4 py-2 text-left">Used On</th>
            <th class="px-4 py-2 text-left">Assembly</th>
            <th class="px-4 py-2 w-12"></th>
            <th class="px-4 py-2 w-24"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in results" :key="r.part_id"
            class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <td class="px-4 py-2.5">
              <div class="flex items-center gap-2">
                <span class="font-mono font-semibold text-gray-800">{{ r.part_no || '—' }}</span>
                <button v-if="r.part_no" @click="copy(r.part_no)"
                  class="p-1 rounded hover:bg-red-100 text-gray-400 hover:text-red-600 transition-colors"
                  title="Copy part number">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                </button>
              </div>
              <div v-if="r.superseded_by" class="mt-1">
                <router-link :to="`/honda/search?q=${encodeURIComponent(r.superseded_by)}`"
                  class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-medium bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors">
                  ⚠ Superseded → {{ r.superseded_by }}
                </router-link>
              </div>
              <div v-else-if="r.superseded_note" class="mt-1">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-red-100 text-red-700">
                  ⚠ {{ r.superseded_note }}
                </span>
              </div>
            </td>
            <td class="px-4 py-2.5 text-gray-700">{{ r.description }}</td>
            <td class="px-4 py-2.5 text-right font-medium">
              <span v-if="r.rrp != null" class="text-green-700">${{ r.rrp.toFixed(2) }}</span>
              <span v-else class="text-gray-300 text-xs">—</span>
            </td>
            <td class="px-4 py-2.5 text-gray-500 text-xs">
              <span v-if="r.vehicles?.length">
                {{ r.vehicles.map(v => v.model).join(', ') }}
                <span v-if="r.vehicle_count > r.vehicles.length" class="text-gray-400"> +{{ r.vehicle_count - r.vehicles.length }} more</span>
              </span>
              <span v-else class="text-gray-300">—</span>
            </td>
            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ r.assembly }}</td>
            <td class="px-4 py-2.5 text-center" @click.stop>
              <button
                v-if="r.rrp != null"
                type="button"
                @click="addPartToCart({ number: r.part_no, rrp: r.rrp, superseded_by: r.superseded_by }, $event)"
                :disabled="addingPartNumber === r.part_no"
                class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors disabled:opacity-50"
                :class="addedPartNumber === r.part_no ? 'bg-green-100 text-green-600' : 'bg-red-50 text-red-600 hover:bg-red-100'"
                :title="addedPartNumber === r.part_no ? 'Added to cart' : 'Add to cart'"
              >
                <svg v-if="addedPartNumber !== r.part_no" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.972-4.665 2.53-7.032.15-.633-.324-1.243-.973-1.243H5.436m1.36 8.275L5.436 5.965M7.5 14.25L5.436 5.965M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
              </button>
              <span v-else class="text-gray-300 text-xs">—</span>
            </td>
            <td class="px-4 py-2.5">
              <router-link v-if="r.assembly_id" :to="`/honda/assembly/${r.assembly_id}`"
                class="text-xs text-red-600 hover:underline whitespace-nowrap">
                View →
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else-if="searched && !loading"
      class="text-center py-16 text-gray-400">
      <div class="text-4xl mb-3">🔍</div>
      <div class="font-medium">No parts found for "{{ lastQuery }}"</div>
      <div class="text-sm mt-1">Try a different part number or description</div>
    </div>

    <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-y-2"
      leave-active-class="transition-all duration-200" leave-to-class="opacity-0 translate-y-2">
      <div v-if="copied"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-medium shadow-xl z-50">
        ✓ Copied: {{ copied }}
      </div>
    </Transition>

    <!-- Supersession confirm prompt -->
    <Transition enter-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
      <div v-if="pendingSupersession"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
        @click.self="cancelSupersessionPrompt">
        <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-5">
          <div class="flex items-start gap-3">
            <div class="text-2xl leading-none">⚠</div>
            <div>
              <h3 class="font-semibold text-gray-900">Part Superseded</h3>
              <p class="text-sm text-gray-600 mt-1">
                <span class="font-mono">{{ pendingSupersession.number }}</span> has been superseded.
                Current part number:
                <span class="font-mono font-semibold">{{ pendingSupersession.superseded_by }}</span>
              </p>
            </div>
          </div>
          <div class="mt-5 flex flex-col gap-2">
            <button @click="confirmAddLatest"
              class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">
              Add {{ pendingSupersession.superseded_by }} (current)
            </button>
            <button @click="confirmAddOriginal"
              class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 rounded-lg transition-colors">
              Add {{ pendingSupersession.number }} anyway
            </button>
            <button @click="cancelSupersessionPrompt"
              class="text-xs text-gray-400 hover:text-gray-600 mt-1">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { useCart } from '../../useCart'

const {
  addingPartNumber,
  addedPartNumber,
  pendingSupersession,
  addPartToCart,
  confirmAddLatest,
  confirmAddOriginal,
  cancelSupersessionPrompt,
} = useCart('/cart/add-honda-part')

const route     = useRoute()
const query     = ref('')
const results   = ref([])
const loading   = ref(false)
const searched  = ref(false)
const lastQuery = ref('')
const copied    = ref(null)

watch(
  () => route.query.q,
  (q) => {
    if (q && q !== query.value) {
      query.value = String(q)
      search()
    }
  },
  { immediate: true }
)

async function search() {
  if (query.value.length < 3) return
  loading.value  = true
  searched.value = false
  lastQuery.value = query.value
  try {
    const { data } = await axios.get('/api/parts-catalogue/honda/parts/search', { params: { q: query.value } })
    results.value  = data
    searched.value = true
  } finally {
    loading.value = false
  }
}

async function copy(number) {
  try {
    await navigator.clipboard.writeText(number)
  } catch {
    const el = document.createElement('textarea')
    el.value = number
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
  }
  copied.value = number
  setTimeout(() => copied.value = null, 2500)
}
</script>
