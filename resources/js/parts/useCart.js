import { ref } from 'vue'

/**
 * Adds an OEM part to the cart via the same window.addToCart() helper
 * app.js defines for the Alpine-driven Shop Accessories pages - this Vue
 * bundle is a separate Vite entry with no shared JS state, but both are
 * loaded on the parts-finder page (it extends yamaha.layout), so reusing
 * the global function keeps CSRF handling and the header cart badge sync
 * in one place instead of duplicating it here.
 */
export function useCart(addUrl = '/cart/add-part') {
  const addingPartNumber = ref(null)
  const addedPartNumber = ref(null)

  // When Add to Cart is clicked on a part whose superseded_by is set
  // (already resolved to the terminal/current part number server-side),
  // this holds the part so a confirm prompt can ask which one to add
  // instead of silently substituting or silently ignoring the supersession.
  const pendingSupersession = ref(null)

  async function addPartToCart(part, event) {
    if (part.rrp == null || !part.number) return

    if (part.superseded_by && part.superseded_by !== part.number) {
      pendingSupersession.value = { number: part.number, superseded_by: part.superseded_by, event }
      return
    }

    await performAdd(part.number, event)
  }

  async function confirmAddLatest() {
    if (!pendingSupersession.value) return
    const { superseded_by: number, event } = pendingSupersession.value
    pendingSupersession.value = null
    await performAdd(number, event)
  }

  async function confirmAddOriginal() {
    if (!pendingSupersession.value) return
    const { number, event } = pendingSupersession.value
    pendingSupersession.value = null
    await performAdd(number, event)
  }

  function cancelSupersessionPrompt() {
    pendingSupersession.value = null
  }

  async function performAdd(number, event) {
    addingPartNumber.value = number
    const button = event?.currentTarget ?? null

    const ok = await window.addToCart(
      { part_number: number, quantity: 1 },
      button,
      addUrl,
    )

    addingPartNumber.value = null

    if (ok) {
      addedPartNumber.value = number
      setTimeout(() => {
        if (addedPartNumber.value === number) addedPartNumber.value = null
      }, 2000)
    }
  }

  return {
    addingPartNumber,
    addedPartNumber,
    pendingSupersession,
    addPartToCart,
    confirmAddLatest,
    confirmAddOriginal,
    cancelSupersessionPrompt,
  }
}
