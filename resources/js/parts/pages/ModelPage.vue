<template>
  <div class="max-w-5xl mx-auto px-4 py-6">
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else-if="product">
      <!-- Breadcrumb -->
      <nav class="text-sm text-gray-500 mb-4 flex items-center gap-2">
        <router-link to="/" class="hover:text-blue-600">Models</router-link>
        <span>›</span>
        <span class="text-gray-800 font-medium">{{ product.model }}</span>
      </nav>

      <!-- Model header -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6 flex items-start justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full"
              :class="typeClass(product.type)">
              {{ typeName(product.type) }}
            </span>
            <span v-if="product.year" class="text-sm font-bold text-gray-500">{{ product.year }}</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900">{{ product.model }}</h1>
          <p v-if="product.common_name" class="text-gray-500 mt-1">{{ product.common_name }}</p>
          <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-400">
            <span v-if="product.prefix"><strong>Prefix:</strong> {{ product.prefix }}</span>
            <span v-if="product.serial"><strong>Serial:</strong> {{ product.serial }}</span>
            <span v-if="product.cat_number"><strong>Cat:</strong> {{ product.cat_number }}</span>
          </div>
        </div>
      </div>

      <!-- Contents -->
      <h2 class="text-lg font-semibold text-gray-700 mb-3">Catalogue Sections</h2>

      <div v-if="product.contents?.length" class="space-y-3">
        <div v-for="content in product.contents" :key="content.content_id"
          class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">{{ content.title }}</h3>
            <span class="text-xs text-gray-400">{{ content.assemblies?.length ?? 0 }} assemblies</span>
          </div>

          <div class="p-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
              <router-link
                v-for="asm in content.assemblies"
                :key="asm.assembly_id"
                :to="`/assembly/${asm.assembly_id}`"
                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors group text-sm"
              >
                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                </svg>
                <span class="truncate text-gray-700 group-hover:text-blue-700">{{ asm.title }}</span>
                <svg v-if="asm.has_parts" class="w-3 h-3 text-gray-300 group-hover:text-blue-400 ml-auto shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                </svg>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-10 text-gray-400">
        <div class="text-3xl mb-2">📋</div>
        No sections found for this model.
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props   = defineProps({ id: String })
const product = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await axios.get(`/api/parts-catalogue/products/${props.id}`)
    product.value  = data.product
  } finally {
    loading.value = false
  }
})

const TYPE_LABELS  = { 0: 'Motorcycle', 1: 'Outboard', 2: 'ATV', 3: 'Snowmobile', 4: 'PWC', 5: 'Boat' }
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
