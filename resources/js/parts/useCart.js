import { ref } from 'vue'

/**
 * Adds an OEM part to the cart via the same window.addToCart() helper
 * app.js defines for the Alpine-driven Shop Accessories pages - this Vue
 * bundle is a separate Vite entry with no shared JS state, but both are
 * loaded on the parts-finder page (it extends yamaha.layout), so reusing
 * the global function keeps CSRF handling and the header cart badge sync
 * in one place instead of duplicating it here.
 */
export function useCart() {
  const addingPartNumber = ref(null)
  const addedPartNumber = ref(null)

  async function addPartToCart(part, event) {
    if (part.rrp == null || !part.number) return

    addingPartNumber.value = part.number
    const button = event?.currentTarget ?? null

    const ok = await window.addToCart(
      { part_number: part.number, quantity: 1 },
      button,
      '/cart/add-part',
    )

    addingPartNumber.value = null

    if (ok) {
      addedPartNumber.value = part.number
      setTimeout(() => {
        if (addedPartNumber.value === part.number) addedPartNumber.value = null
      }, 2000)
    }
  }

  return { addingPartNumber, addedPartNumber, addPartToCart }
}
