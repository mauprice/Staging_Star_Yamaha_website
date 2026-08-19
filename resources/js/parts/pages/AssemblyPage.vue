<template>
  <div class="max-w-full mx-auto px-4 py-4">
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else-if="assembly">
      <!-- Breadcrumb -->
      <nav class="text-sm text-gray-500 mb-3 flex items-center gap-2 flex-wrap">
        <router-link to="/" class="hover:text-blue-600">Models</router-link>
        <span>›</span>
        <router-link :to="`/model/${assembly.product_id}`" class="hover:text-blue-600 max-w-xs truncate">
          {{ assembly.model }} {{ assembly.year }}
        </router-link>
        <span>›</span>
        <span class="text-gray-400 text-xs">{{ assembly.content_title }}</span>
        <span>›</span>
        <span class="text-gray-800 font-medium">{{ assembly.title }}</span>
      </nav>

      <!-- Main layout: diagram left, parts right -->
      <div class="flex gap-4 items-start" :class="hasImage ? 'flex-col lg:flex-row' : 'flex-col'">

        <!-- Diagram panel -->
        <div v-if="hasImage" class="lg:w-[60%] xl:w-[65%] shrink-0">
          <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
              <h2 class="font-semibold text-gray-700 text-sm">{{ assembly.title }}</h2>
              <!-- Image switcher if multiple diagrams -->
              <div v-if="images.length > 1" class="flex items-center gap-1">
                <button v-for="(img, i) in images" :key="i"
                  @click="activeImageIndex = i"
                  class="w-6 h-6 rounded text-xs font-semibold transition-colors"
                  :class="activeImageIndex === i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300'">
                  {{ i + 1 }}
                </button>
              </div>
            </div>

            <div class="relative overflow-auto bg-gray-100 cursor-crosshair"
              ref="diagramContainer"
              style="max-height: 75vh">
              <div class="relative inline-block" ref="imageWrapper">
                <img
                  v-if="activeImage"
                  :src="activeImage.url"
                  :alt="assembly.title"
                  class="block max-w-full"
                  @load="onImageLoad"
                  @error="onImageError"
                  ref="diagramImg"
                />

                <!-- Placeholder when not yet extracted -->
                <div v-if="!activeImage?.extracted" class="absolute inset-0 flex items-center justify-center bg-gray-100">
                  <div class="text-center text-gray-400">
                    <div class="text-4xl mb-2">🖼️</div>
                    <div class="text-sm font-medium">Image not yet extracted</div>
                    <div class="text-xs mt-1">Run: php artisan ypic:import-images</div>
                  </div>
                </div>

                <!-- Hotspot markers -->
                <template v-if="activeImage?.extracted && imageLoaded">
                  <div
                    v-for="part in partsWithCoords"
                    :key="part.part_id"
                    class="absolute -translate-x-1/2 -translate-y-1/2 cursor-pointer"
                    :style="hotspotStyle(part)"
                    @click="selectPart(part)"
                  >
                    <div class="flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold leading-none border-2 transition-all shadow-sm"
                      :class="selectedPartId === part.part_id
                        ? 'bg-red-500 border-red-600 text-white scale-125 shadow-md'
                        : 'bg-white border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white hover:scale-110'">
                      {{ part.image_ref_no }}
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Parts list panel -->
        <div :class="hasImage ? 'flex-1 min-w-0' : 'w-full'">
          <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
              <h2 class="font-semibold text-gray-700 text-sm">Parts List</h2>
              <span class="text-xs text-gray-400">{{ parts.length }} parts</span>
            </div>

            <!-- Parts table -->
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                    <th class="px-3 py-2 text-center w-10">Ref</th>
                    <th class="px-3 py-2 text-left">Part Number</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-center w-12">Qty</th>
                    <th class="px-3 py-2 text-right w-24">Price</th>
                    <th class="px-3 py-2 text-left">Remark</th>
                    <th class="px-3 py-2 text-center w-12">Cart</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="part in parts" :key="part.part_id">
                    <!-- Top-level part row -->
                    <tr
                      :id="`part-${part.part_id}`"
                      @click="selectPart(part)"
                      class="border-b border-gray-100 cursor-pointer transition-colors group"
                      :class="[
                        selectedPartId === part.part_id ? 'bg-blue-50 border-blue-200' : 'hover:bg-gray-50',
                        part.parent_id ? 'bg-gray-50/50' : '',
                      ]"
                    >
                      <td class="px-3 py-2 text-center">
                        <span v-if="part.image_ref_no"
                          class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold border-2 transition-colors"
                          :class="selectedPartId === part.part_id
                            ? 'bg-red-500 border-red-500 text-white'
                            : 'border-blue-300 text-blue-600'">
                          {{ part.image_ref_no }}
                        </span>
                        <span v-else-if="part.parent_id" class="text-gray-300 text-xs">↳</span>
                      </td>

                      <td class="px-3 py-2 font-mono">
                        <div class="flex items-center gap-2">
                          <span v-if="part.number" class="font-semibold text-gray-800">{{ part.number }}</span>
                          <span v-else class="text-gray-400 italic text-xs">—</span>
                          <button
                            v-if="part.number"
                            @click.stop="copyPartNumber(part.number)"
                            class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-blue-100 text-gray-400 hover:text-blue-600 transition-all"
                            title="Copy part number"
                          >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                          </button>
                        </div>
                      </td>

                      <td class="px-3 py-2 text-gray-700" :class="part.parent_id ? 'pl-7' : ''">
                        {{ part.desc || '—' }}
                      </td>

                      <td class="px-3 py-2 text-center text-gray-600 font-medium">{{ part.qty }}</td>

                      <td class="px-3 py-2 text-right font-medium">
                        <span v-if="part.rrp != null" class="text-green-700">
                          ${{ part.rrp.toFixed(2) }}
                        </span>
                        <span v-else class="text-gray-300 text-xs">—</span>
                      </td>

                      <td class="px-3 py-2 text-gray-400 text-xs">{{ part.remark || '' }}</td>

                      <td class="px-3 py-2 text-center" @click.stop>
                        <button
                          v-if="part.rrp != null"
                          type="button"
                          @click="addPartToCart(part, $event)"
                          :disabled="addingPartNumber === part.number"
                          class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors disabled:opacity-50"
                          :class="addedPartNumber === part.number ? 'bg-green-100 text-green-600' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'"
                          :title="addedPartNumber === part.number ? 'Added to cart' : 'Add to cart'"
                        >
                          <svg v-if="addedPartNumber !== part.number" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.972-4.665 2.53-7.032.15-.633-.324-1.243-.973-1.243H5.436m1.36 8.275L5.436 5.965M7.5 14.25L5.436 5.965M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                          </svg>
                          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/>
                          </svg>
                        </button>
                        <span v-else class="text-gray-300 text-xs">—</span>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Copy toast -->
      <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-y-2"
        leave-active-class="transition-all duration-200" leave-to-class="opacity-0 translate-y-2">
        <div v-if="copiedNumber"
          class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-medium shadow-xl z-50">
          ✓ Copied: {{ copiedNumber }}
        </div>
      </Transition>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { useCart } from '../useCart'

const props = defineProps({ id: String })

const { addingPartNumber, addedPartNumber, addPartToCart } = useCart()

const assembly        = ref(null)
const images          = ref([])
const parts           = ref([])
const loading         = ref(true)
const activeImageIndex = ref(0)
const imageLoaded     = ref(false)
const selectedPartId  = ref(null)
const copiedNumber    = ref(null)
const diagramImg      = ref(null)
const diagramContainer = ref(null)
const imageWrapper    = ref(null)
const renderedWidth   = ref(0)
const renderedHeight  = ref(0)

const activeImage = computed(() => images.value[activeImageIndex.value] ?? null)
const hasImage    = computed(() => images.value.length > 0)

const partsWithCoords = computed(() =>
  parts.value.filter(p => p.img_x && p.img_y && p.image_ref_no)
)

onMounted(async () => {
  try {
    const { data } = await axios.get(`/api/parts-catalogue/assemblies/${props.id}`)
    assembly.value = data.assembly
    images.value   = data.images
    parts.value    = data.parts
  } finally {
    loading.value = false
  }
})

function onImageLoad(e) {
  renderedWidth.value  = e.target.naturalWidth
  renderedHeight.value = e.target.naturalHeight
  imageLoaded.value    = true
}

function onImageError(e) {
  e.target.src = '/images/placeholder.svg'
}

function hotspotStyle(part) {
  if (!activeImage.value?.width || !activeImage.value?.height) return {}
  const img = diagramImg.value
  if (!img) return {}
  const scaleX = img.clientWidth  / activeImage.value.width
  const scaleY = img.clientHeight / activeImage.value.height
  return {
    left: `${part.img_x * scaleX}px`,
    top:  `${part.img_y * scaleY}px`,
  }
}

function selectPart(part) {
  selectedPartId.value = part.part_id
  const row = document.getElementById(`part-${part.part_id}`)
  row?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
}

async function copyPartNumber(number) {
  try {
    await navigator.clipboard.writeText(number)
    copiedNumber.value = number
    setTimeout(() => copiedNumber.value = null, 2500)
  } catch {
    // Fallback for non-HTTPS or older browsers
    const el = document.createElement('textarea')
    el.value = number
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    copiedNumber.value = number
    setTimeout(() => copiedNumber.value = null, 2500)
  }
}
</script>

<style scoped>
tr.group:hover .opacity-0 { opacity: 1; }
</style>
