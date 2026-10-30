<template>
  <div class="max-w-5xl mx-auto px-4 py-6">
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else-if="vehicle">
      <!-- Breadcrumb -->
      <nav class="text-sm text-gray-500 mb-4 flex items-center gap-2">
        <router-link to="/honda" class="hover:text-red-600">Models</router-link>
        <span>›</span>
        <span class="text-gray-800 font-medium">{{ vehicle.model }}</span>
      </nav>

      <!-- Vehicle header -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-3 mb-2">
          <span v-if="vehicle.type" class="text-xs font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-red-100 text-red-700">
            {{ vehicle.type }}
          </span>
          <span v-if="vehicle.year" class="text-sm font-bold text-gray-500">{{ vehicle.year }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ vehicle.model }}</h1>
        <p v-if="vehicle.model_name" class="text-gray-500 mt-1">{{ vehicle.model_name }}</p>
        <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-400">
          <span v-if="vehicle.code"><strong>Code:</strong> {{ vehicle.code }}</span>
          <span v-if="vehicle.cc"><strong>CC:</strong> {{ vehicle.cc }}</span>
          <span v-if="vehicle.catalogue?.number"><strong>Manual:</strong> {{ vehicle.catalogue.number }}</span>
        </div>
      </div>

      <!-- Assemblies -->
      <h2 class="text-lg font-semibold text-gray-700 mb-3">Catalogue Diagrams</h2>

      <div v-if="assemblies.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            <router-link
              v-for="asm in assemblies"
              :key="asm.assembly_id"
              :to="`/honda/assembly/${asm.assembly_id}?model_id=${vehicle.model_id}`"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-50 hover:text-red-700 transition-colors group text-sm"
            >
              <svg class="w-4 h-4 text-gray-300 group-hover:text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
              </svg>
              <span class="truncate text-gray-700 group-hover:text-red-700">{{ asm.name }}</span>
              <span v-if="asm.address_no" class="text-[10px] text-gray-400 shrink-0">{{ asm.address_no }}</span>
              <svg v-if="asm.has_images" class="w-3 h-3 text-gray-300 group-hover:text-red-400 ml-auto shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
              </svg>
            </router-link>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-10 text-gray-400">
        <div class="text-3xl mb-2">📋</div>
        No catalogue diagrams found for this model.
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({ id: String })
const vehicle = ref(null)
const assemblies = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await axios.get(`/api/parts-catalogue/honda/vehicles/${props.id}`)
    vehicle.value = data.vehicle
    assemblies.value = data.assemblies
  } finally {
    loading.value = false
  }
})
</script>
