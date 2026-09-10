<template>
  <div class="max-w-7xl mx-auto px-4 pt-4">
    <div class="flex gap-1 border-b border-gray-200">
      <router-link to="/" class="tab" active-class="tab-active" exact-active-class="tab-active">
        Browse by Model
      </router-link>
      <router-link to="/search" class="tab" active-class="tab-active">
        Search by Part Number
      </router-link>
    </div>
  </div>
  <router-view />
</template>

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
</style>
