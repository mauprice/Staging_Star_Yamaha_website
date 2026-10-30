<template>
  <div class="max-w-7xl mx-auto px-4 pt-4">
    <!-- Brand switcher -->
    <div class="flex gap-2 mb-3">
      <router-link :to="brandHome('yamaha')"
        class="brand-btn"
        :class="brand === 'yamaha' ? 'brand-btn-active-yamaha' : ''">
        Yamaha
      </router-link>
      <router-link :to="brandHome('honda')"
        class="brand-btn"
        :class="brand === 'honda' ? 'brand-btn-active-honda' : ''">
        Honda
      </router-link>
    </div>

    <div class="flex gap-1 border-b border-gray-200">
      <router-link :to="browsePath" class="tab" :class="{ 'tab-active': isBrowseActive }">
        Browse by Model
      </router-link>
      <router-link :to="searchPath" class="tab" :class="{ 'tab-active': isSearchActive }">
        Search by Part Number
      </router-link>
    </div>
  </div>
  <router-view />
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const brand = computed(() => route.path.startsWith('/honda') ? 'honda' : 'yamaha')

const browsePath = computed(() => brand.value === 'honda' ? '/honda' : '/')
const searchPath  = computed(() => brand.value === 'honda' ? '/honda/search' : '/search')

const isSearchActive = computed(() => route.path === searchPath.value)
const isBrowseActive = computed(() => !isSearchActive.value)

// Switching brand keeps you on Browse vs Search, just moves to the other brand's version of it.
function brandHome(targetBrand) {
  const onSearch = route.path.endsWith('/search')
  if (targetBrand === 'honda') return onSearch ? '/honda/search' : '/honda'
  return onSearch ? '/search' : '/'
}
</script>

<style scoped>
.tab {
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}
.tab:hover { color: #374151; }
.tab-active { color: #2563eb; border-bottom-color: #2563eb; }

.brand-btn {
  padding: 0.375rem 1rem;
  font-size: 0.8125rem;
  font-weight: 600;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  color: #6b7280;
  background: #fff;
}
.brand-btn:hover { background: #f9fafb; }
.brand-btn-active-yamaha { background: #2563eb; border-color: #2563eb; color: #fff; }
.brand-btn-active-honda { background: #dc2626; border-color: #dc2626; color: #fff; }
</style>
