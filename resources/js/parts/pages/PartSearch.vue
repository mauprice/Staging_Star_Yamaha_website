<template>
  <div class="max-w-4xl mx-auto px-4 py-6">
    <h1 class="text-xl font-bold text-gray-800 mb-4">Part Number Search</h1>

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
      <div class="flex gap-3">
        <input
          v-model="query"
          @keydown.enter="search"
          type="text"
          placeholder="Enter part number (e.g. 4WV-15100-12) or description…"
          class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          autofocus
        />
        <button @click="search"
          :disabled="loading || query.length < 3"
          class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors">
          Search
        </button>
      </div>
      <p class="text-xs text-gray-400 mt-2">Minimum 3 characters. Hyphens are optional.</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Results -->
    <div v-else-if="results.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm text-gray-500">
        {{ results.length }} result{{ results.length !== 1 ? 's' : '' }}
      </div>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-xs text-gray-500 uppercase tracking-wide border-b border-gray-200 bg-gray-50">
            <th class="px-4 py-2 text-left">Part Number</th>
            <th class="px-4 py-2 text-left">Description</th>
            <th class="px-4 py-2 text-center w-12">Qty</th>
            <th class="px-4 py-2 text-right w-28">Price (RRP)</th>
            <th class="px-4 py-2 text-left">Model</th>
            <th class="px-4 py-2 text-left">Assembly</th>
            <th class="px-4 py-2 text-center w-12">Cart</th>
            <th class="px-4 py-2 w-24"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in results" :key="r.part_id"
            class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <td class="px-4 py-2.5">
              <div class="flex items-center gap-2">
                <span class="font-mono font-semibold text-gray-800">{{ r.number || '—' }}</span>
                <button v-if="r.number" @click="copy(r.number)"
                  class="p-1 rounded hover:bg-blue-100 text-gray-400 hover:text-blue-600 transition-colors"
                  title="Copy part number">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                </button>
              </div>
            </td>
            <td class="px-4 py-2.5 text-gray-700">{{ r.desc }}</td>
            <td class="px-4 py-2.5 text-center text-gray-600">{{ r.qty }}</td>
            <td class="px-4 py-2.5 text-right font-medium">
              <span v-if="r.rrp != null" class="text-green-700">
                ${{ r.rrp.toFixed(2) }}
              </span>
              <span v-else class="text-gray-300 text-xs">—</span>
            </td>
            <td class="px-4 py-2.5 text-gray-500 text-xs">
              {{ r.model }}<span v-if="r.year" class="ml-1 text-gray-400">({{ r.year }})</span>
            </td>
            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ r.assembly }}</td>
            <td class="px-4 py-2.5 text-center">
              <button
                v-if="r.rrp != null"
                type="button"
                @click="addPartToCart(r, $event)"
                :disabled="addingPartNumber === r.number"
                class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors disabled:opacity-50"
                :class="addedPartNumber === r.number ? 'bg-green-100 text-green-600' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'"
                :title="addedPartNumber === r.number ? 'Added to cart' : 'Add to cart'"
              >
                <svg v-if="addedPartNumber !== r.number" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.972-4.665 2.53-7.032.15-.633-.324-1.243-.973-1.243H5.436m1.36 8.275L5.436 5.965M7.5 14.25L5.436 5.965M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
              </button>
              <span v-else class="text-gray-300 text-xs">—</span>
            </td>
            <td class="px-4 py-2.5">
              <router-link v-if="r.assembly_id" :to="`/assembly/${r.assembly_id}`"
                class="text-xs text-blue-600 hover:underline whitespace-nowrap">
                View →
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty -->
    <div v-else-if="searched && !loading"
      class="text-center py-16 text-gray-400">
      <div class="text-4xl mb-3">🔍</div>
      <div class="font-medium">No parts found for "{{ lastQuery }}"</div>
      <div class="text-sm mt-1">Try a different part number or description</div>
    </div>

    <!-- Copy toast -->
    <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-y-2"
      leave-active-class="transition-all duration-200" leave-to-class="opacity-0 translate-y-2">
      <div v-if="copied"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-medium shadow-xl z-50">
        ✓ Copied: {{ copied }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useCart } from '../useCart'

const { addingPartNumber, addedPartNumber, addPartToCart } = useCart()

const query     = ref('')
const results   = ref([])
const loading   = ref(false)
const searched  = ref(false)
const lastQuery = ref('')
const copied    = ref(null)

async function search() {
  if (query.value.length < 3) return
  loading.value  = true
  searched.value = false
  lastQuery.value = query.value
  try {
    const { data } = await axios.get('/api/parts-catalogue/parts/search', { params: { q: query.value } })
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
