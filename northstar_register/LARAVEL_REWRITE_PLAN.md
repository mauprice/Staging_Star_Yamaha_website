# Northstar Yamaha Register — Laravel 13 + Livewire Rewrite

Scope and build estimate for replacing the current PHP/mysqli/jQuery app with a
Laravel 13 + Livewire 3 + Tailwind app, same functionality, modern stack.

## 1. What exists today

- Single-page app: `index.html` + `js/script.js` (jQuery) render markup that
  `php/register.php` returns as HTML/JSON blobs over `$.ajax`. No router, no
  templating — all markup is built as PHP string concatenation.
- One real data table: `register` (~36 columns, mostly financials), plus
  `register_copy1` (a manual backup snapshot, not used at runtime) and
  `users` (auth).
- `php/login.php` is dead code — leftover from an unrelated project ("Jade
  Carving"), references a `login_log` table that doesn't exist in this DB,
  and is never called by the frontend. Not being ported.
- Auth is one shared password: `changepw()` always writes `WHERE id = '1'`,
  regardless of who's logged in. Passwords are `md5(password + salt)`.
- Business rules to preserve exactly:
  - GST is derived client-side as `price / 11` (AU 10% GST extraction).
  - "Sold price" = `bal_paid + trade_price`; "Profit" = sold − cost, where
    cost sums purchase price + ~10 fee fields.
  - List view toggles between last 100 records and all records.
  - Print report = date-range query on `SOLD DATE`, rendered as two
    print-friendly tables with column totals, triggered via `window.print()`.
- Known bugs in current code: `$out` undefined-variable warning when
  `$type` doesn't match any branch ([register.php:560](php/register.php#L560));
  `GST_Piad` is a typo'd column name baked into the schema.

## 2. Target architecture

| Concern | Choice |
|---|---|
| Framework | Laravel 13 |
| UI | Livewire 3 + Blade, Tailwind CSS (no more Bootstrap/jQuery/jQuery UI) |
| Auth | Laravel Breeze (Livewire stack) — real per-user accounts + bcrypt, replacing the shared-password hack |
| DB access | Eloquent models + migrations, no raw SQL strings |
| Entity naming | Model `StockEntry` / table `stock_entries` — deliberately **not** `Register`, to avoid colliding with Laravel's own registration feature naming. "Register" stays the product name in the UI only. |

### Models & schema

- `StockEntry` (replaces `register`): snake_case columns, proper types —
  `date` columns instead of `varchar` dates, `decimal(10,2)` kept for money,
  `gst_piad` renamed to `gst_paid`.
- `User`: Laravel's default fields, bcrypt via `Hash::make`. The `type` int
  column in the current `users` table hints roles were planned but never
  used — left as a plain column for now (see open questions).
- `register_copy1` is a point-in-time backup, not a live table — not
  ported; the old DB dump stays as an archival reference.

### Livewire components

- `Auth\Login`, `Auth\Profile` — from Breeze scaffolding, gives every user
  their own password instead of one shared one.
- `StockEntries\Index` — main screen: last-100/all toggle, click-to-select
  row, the ~30-field detail form, New/Save/Delete with `wire:confirm`
  (replaces the custom Bootstrap modal confirm dance), computed properties
  for cost/sold/profit/GST instead of the jQuery `cost()`/`sold()` helpers.
- `StockEntries\PrintReport` — date-range picker, totals table, dedicated
  print-friendly Blade view with `@media print` instead of the
  `window.print()` + hidden-div hack.

### Data migration

An Artisan command (`stock-entries:import`) reads the existing
`sales_register.sql` dump (or live DB), maps old column names → new
snake_case names, casts `varchar` dates to real dates, and fixes the
`GST_Piad` typo on the way in. Run once, verified against row counts and a
spot-check of totals, before cutover.

## 3. Functional parity checklist

| Current behavior | New implementation |
|---|---|
| Last 100 / All toggle | Livewire property + query scope |
| Click row → load form | Livewire `selectedId` + computed model binding |
| Save (insert/update) | Livewire form object + Form Request validation (currently: **no validation at all**) |
| Delete with confirm | `wire:confirm` + Eloquent delete |
| New record (resets form, defaults today's date) | Livewire `resetForm()` action |
| Cost/Sold/Profit/GST live calculation | Livewire computed properties, recalculated on `updated()` |
| Print report by date range | `PrintReport` component + print Blade view, same two-table + totals layout |
| Change password | Breeze profile page (per-user, not shared) |
| Session-based login gate | Laravel's session auth + middleware |

## 4. Improvements beyond parity

- Per-user accounts with bcrypt instead of one shared md5 password.
- Server-side validation with real error messages (none exists today).
- CSRF protection (absent today since it's raw AJAX with no token).
- `.env`-based DB config instead of credentials hardcoded in
  [database.php](php/database.php).
- Proper column types (real `date`, indexed where useful) instead of
  `varchar` dates parsed by string-splitting on `/`.
- Automated feature tests for auth + CRUD (none exist today).

## 5. Open questions / out of scope for v1

- **Roles**: the unused `type` column on `users` suggests roles were
  planned. Decide now whether to implement (e.g. admin vs sales) or defer.
- **Hosting**: current site runs on cPanel/EA-PHP shared hosting. Laravel
  needs Composer + writable `storage/`/`bootstrap/cache`, and ideally
  PHP-FPM — confirm the production host supports this before cutover, or
  plan a hosting change. Not estimated below.
- **`register_copy1`**: treated as historical only; flag if you actually
  need it queryable in the new app.

## 6. Build effort estimate (scope sizing, not a cost or runtime figure)

Environment check: Composer, Node/npm, and the Laravel installer are
already present locally — no setup overhead.

The hours below size the *scope* of each phase — a relative measure of how
much work it represents — not a prediction of Claude Code's wall-clock
runtime or a dollar/token cost. Wall-clock time and billing depend on
factors this plan can't see (your Claude/Claude Code plan, how many
review/debug round-trips happen, etc.), so don't read this table as either.

| Phase | Work | Effort (scope size) |
|---|---|---|
| 1 | Scaffold Laravel 13 + Breeze (Livewire) + Tailwind | 0.5 hr |
| 2 | Schema design, migrations, models | 1.0 hr |
| 3 | Data import command + verification against old dump | 1.0–1.5 hr |
| 4 | Auth wiring (login, per-user password change) | 0.5–1.0 hr |
| 5 | Main CRUD Livewire component (list, form, computed fields, confirms) | 3.0–4.0 hr |
| 6 | Print report component + print-friendly view | 1.0–1.5 hr |
| 7 | Styling/UI polish pass (Tailwind) | 1.5–2.0 hr |
| 8 | Feature tests + manual browser QA | 1.0–1.5 hr |
| 9 | Buffer for review feedback/iteration | 1.0–2.0 hr |
| **Total** | | **~11–15 hr (scope size)** |

Suggested checkpoints for your review, regardless of how long each phase
actually takes to run: after data-import verification, after a functional
walkthrough of the main CRUD screen, and at final UI/print sign-off before
cutover. Excludes production hosting/deployment setup (see open questions
above).
