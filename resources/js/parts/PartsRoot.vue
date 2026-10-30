<template>
  <div class="max-w-7xl mx-auto px-4 pt-4">
    <!-- Brand switcher -->
    <div class="flex gap-3 mb-4">
      <router-link :to="brandHome('yamaha')"
        class="brand-tile"
        :class="brand === 'yamaha' ? 'brand-tile-active-yamaha' : ''">
        <img src="/images/yamaha_brand_logo.png" alt="Yamaha" class="brand-tile-logo" />
      </router-link>
      <router-link :to="brandHome('honda')"
        class="brand-tile"
        :class="brand === 'honda' ? 'brand-tile-active-honda' : ''">
        <img src="/images/honda_brand_logo.png" alt="Honda" class="brand-tile-logo" />
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

.brand-tile {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.875rem 2rem;
  border-radius: 0.75rem;
  border: 2px solid #e5e7eb;
  background: #fff;
  transition: border-color .15s, box-shadow .15s, background .15s;
}
.brand-tile:hover { border-color: #d1d5db; background: #f9fafb; }

.brand-tile-logo {
  height: 2.25rem;
  width: auto;
  display: block;
}

.brand-tile-active-yamaha,
.brand-tile-active-honda {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.brand-tile-active-yamaha { border-color: #2563eb; }
.brand-tile-active-honda { border-color: #dc2626; }
</style>
