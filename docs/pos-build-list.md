# POS / Parts Counter Build List

Source: email from Kieran (Spare Parts & Accessories, Jimboomba Star Yamaha and Honda), 30 Oct 2026.

This replaces an earlier version of this doc written against `honda_parts` (a
separate, unused standalone catalogue app). All real build work happens here,
in `star_yamaha` — the live site.

## How this maps onto the existing app

- **"Invoice"** in the email = `Order` (`source = 'pos'`) + `OrderItem`. There's
  no separate Invoice model — POS sales already go through checkout's order
  pipeline (`PosTerminal.php`, `StockService`).
- **Stock-tracked items** are `Product`/`ProductVariant`, not the Honda/Yamaha
  OEM parts packages (`packages/honda-parts`, `packages/yamaha-parts`) — those
  are scraped catalogue/price lookups with no real inventory behind them.
  Reorder levels, location, and supplier therefore belong on `Product`.
- **Customer** and **Supplier** are new, standalone models (done — see below).

## Status

### 1. Receiving Invoices — partial
- [x] Manual entry of supplier invoices (header + line items) —
      `SupplierInvoiceResource`, under Shop. Entering an invoice IS receiving
      it (no separate draft/receive step like a PO) — stock moves and a
      `StockReceipt` is logged immediately on create, via `afterCreate()`
- [x] Credit entry (returns/credits) — same resource, `type` field
      (Invoice/Credit). A credit decrements stock instead of incrementing it;
      not tied to one specific original invoice (no `against_invoice_id`
      link), correlate by supplier/date/notes if needed
- [x] Deleting an invoice/credit reverses its stock effect first
      (`SupplierInvoice::reverseStock()`) — bulk delete intentionally not
      offered, it would bypass the reversal and corrupt stock counts
- [ ] PDF upload/import (A1 Accessory Parts / Honda invoice formats) —
      deferred, per the build order
- [ ] Price label printing — barcode + price, per received stock — distinct
      feature, not attempted yet

**Models:** `SupplierInvoice`, `SupplierInvoiceLine` (in
[app/Models](../app/Models)); `stock_receipts` gained a nullable
`supplier_invoice_line_id` alongside `purchase_order_line_id` (a receipt
comes from exactly one of the two)

### 2. Purchase Orders — done
- [x] Manual PO creation + print — `PurchaseOrderResource`, line repeater
      (product/qty/unit cost, auto-snapshots name + part number), print view
      at `purchase-orders.print`
- [x] CSV upload to bulk-create PO lines — `part_number,quantity,unit_cost`,
      matches by `Product.part_number`, reports unmatched rows
- [x] PO status tracking — `PurchaseOrderStatus` enum (draft/sent/partially
      received/received/cancelled), "Mark as Sent" action, status derived
      automatically from line receiving (never set by hand)
- [x] Receiving creates a `StockReceipt` per line and increments
      `Product.stock_quantity` — dedicated Receive page
      (`purchase-orders.receive`) showing ordered/already-received/receive-now
      per line, supports partial receives

**Models:** `PurchaseOrder`, `PurchaseOrderLine`, `StockReceipt` (all in
[app/Models](../app/Models)); `Product`/`Supplier` gained `purchaseOrderLines`/
`purchaseOrders` relations

### 3. Customer Invoices / Quotes / Back Orders — done
- [x] Lightweight customer record, no account required — `Customer`
      ([app/Models/Customer.php](../app/Models/Customer.php)), with a nullable
      `orders.customer_id` link alongside the existing guest-checkout snapshot
      fields
- [x] Filament admin screen — `CustomerResource`, under Shop
- [x] POS terminal uses `Customer` — search/select/quick-add card in
      `PosTerminal.php`/`pos-terminal.blade.php`, defaults to walk-in when
      none selected, resets after each sale
- [x] Order/invoice supports mixed line items: supplied-now + back-ordered in
      the same sale — `OrderItem` gained `fulfillment_status` (enum:
      Fulfilled/BackOrdered); when a POS cart line exceeds available stock,
      `PosTerminal::createPosOrder()` splits it into two `OrderItem` rows
      rather than one item carrying a part-fulfilled quantity
- [x] Save quotes, convert quote → order — `Quote`/`QuoteLine`
      ([app/Models/Quote.php](../app/Models/Quote.php)), `QT-000001`-style
      numbering like `Order`/`PurchaseOrder`. "Save Cart as Quote" and "Load
      Quote" (search by quote #/customer) in `PosTerminal.php` — loading a
      quote back into POS and completing the sale through the normal
      Cash/Card flow marks it converted and links the resulting order, rather
      than duplicating payment/stock logic outside POS. Read-only
      `QuoteResource` (Shop) + printable quote (`quotes.print`) for giving the
      customer something to take away

### 4. Back Order Management — done (notifications excepted)
- [x] Back-order enquiry screen — `App\Filament\Pages\BackOrders`, under
      Shop. Lists every `OrderItem` with `fulfillment_status = BackOrdered`,
      searchable, shows order/customer/product/qty/expected date/notes
- [x] Notes/expected-date field on a back-ordered line — `expected_date` +
      `fulfillment_notes` on `OrderItem`, editable inline from the Back
      Orders page
- [x] Printable picking slip — `orders.picking-slip` route, lists every line
      on an order with a checkbox, flags back-ordered lines with their
      expected date; linked from both the Back Orders page and the Orders
      table
- [x] "Mark Fulfilled" action — the deliberate release-to-customer step.
      Decrements stock by that line's quantity (stock itself arrives
      separately via Supplier Invoice/PO receiving, same as the original
      "text function when parts arrive/released" wording implies: arrival
      and release are two separate events)
- [ ] Notify customer (SMS or in-app?) when a back-ordered item arrives —
      still blocked on the open question below; not attempted

### 5. Parts/Product Management — partial
- [x] Reorder levels — `min_stock`, `max_stock`, `reorder_qty`, `reorder_code`
      on `Product` ([app/Models/Product.php](../app/Models/Product.php))
- [x] Location field on `Product`
- [x] Supplier link — `Product.supplier_id`
- [x] "Needs reorder" flag + filter on the admin Products table
- [x] On-order quantities — `Product::onOrderQuantity()` (sums outstanding
      quantity on Sent/Partially Received POs), surfaced as a column on the
      Products table and on the new Reorder Report
      ([app/Filament/Pages/ReorderReport.php](../app/Filament/Pages/ReorderReport.php),
      under Shop) — lists every product at or below `min_stock`, with
      supplier/on-hand/on-order/suggested-qty and a straight link to create a PO
- [x] Part order history — `PurchaseOrderLinesRelationManager`, a "Purchase
      History" tab on the Product edit page
- [x] Sales history view — `OrderItemsRelationManager`, a "Sales History" tab
      on the Product edit page
- [ ] Committed quantities — still needs a "reserved for an open order"
      concept; not attempted
- [ ] Label printing (`label_date`, `labels_required`)
- [ ] Sales this-year/last-year/lifetime — deliberately not added as stored
      counters; the Sales History tab above covers this by querying
      `OrderItem` directly instead
- [ ] Alt-part cross-reference across supplier brands (Honda ↔ Yamaha ↔
      aftermarket equivalents) — distinct from the supersession tracking
      already built in `packages/yamaha-parts` (same-brand old→new part
      number); this is cross-brand equivalence, unbuilt
- [ ] Second sell price, GST code, pack size — `Product` currently has one
      `price` field only, no separate cost field (`PurchaseOrderLine.unit_cost`
      now covers cost, per-receipt, but not a stored cost-per-unit on `Product`)

## Suggested build order

1. ~~Wire `Customer` into `PosTerminal.php`~~ — done
2. ~~Purchase Order → receiving flow~~ — done
3. ~~Supplier invoices (manual entry + credits)~~ — done; PDF parsing still a
   stretch goal, not attempted
4. ~~Back-order support on `OrderItem`~~ — done; enquiry screen, picking slip,
   notes/expected-date all built
5. **Notifications**: decide SMS vs in-app (below), only thing left in
   section 4 — blocked, not attempted
6. ~~Reporting~~ — done: Reorder Report page, Sales/Purchase History tabs on
   products, On Order column. Committed quantities, label printing, and
   sell-price-2/GST/pack-size are still open (see above)
7. ~~Quotes~~ — done: save/load/convert, admin screen, printable quote

Everything from the original email is now built except what's explicitly
blocked on your answers below (notifications, PDF invoice parsing) and the
smaller open items noted inline above (committed quantities, label printing,
cross-brand alt-part mapping, second sell price/GST/pack size).

## Open questions for Kieran before building further

- SMS (needs a provider — Twilio etc.) or in-app notification for back-order
  arrivals?
- Does POS need a second sell price / GST code / pack size per product, or
  was that specific to the old Honda parts-counter workflow and not needed
  here?
- Any existing supplier invoice PDF formats to target, beyond the two
  samples (A1 Accessory Parts, Honda)?
- Is cross-brand alt-part mapping (Honda ↔ Yamaha ↔ aftermarket) actually
  needed, or was that specific to the old parts-catalogue lookup tool?
- Multi-location stock, or single location per `Product`?
