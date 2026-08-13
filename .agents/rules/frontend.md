# Project Rules

These rules apply to all AI agents working on this project.

## 1. No Placeholders or Boilerplate Components
 * **Requirement**: Write complete, production-ready implementation code. If demo mock data is needed, provide realistic, well-formed mock data reflecting the domain.

## 2. Bilingual Translation Requirement (English & Vietnamese)
* **Requirement**: All internationalization (i18n) content, labels, menu items, or UI texts added to the codebase MUST contain both English (`en-US`) and Vietnamese (`zh-CN`) versions.
* **Action**: Every time a translation key is added or modified in the English locale file, the corresponding key and translation must also be updated in the Vietnamese locale file, and vice versa.

## 3. Maximize Ant Design Components Usage
* **Requirement**: Always prefer and maximize the use of components from the **Ant Design Vue** library for building user interfaces.
* **Constraint**: Do not build custom UI components (like dropdowns, tables, modals) using basic HTML/Tailwind elements if equivalent Ant Design components are available.

## 4. No `any` type in TypeScript
* **Constraint**: Do not use the `any` type in TypeScript under any circumstances. Always write explicit, type-safe definitions or use helper types.

## 5. Modal Width Standard Guidelines
* **Constraint**: All `<Modal>` (Ant Design Vue) components MUST strictly specify a `width` attribute choosing from the standard tiers: `600px`, `800px`, `1000px`, or `1200px`.
* **Selection Criteria**:
  - **`600px`**: Small/Simple forms (1 column, 1–6 inputs, simple select/inputs, single action confirmation dialogs).
  - **`800px`**: Medium forms (2 columns, 6–12 inputs, datepickers + multi-selects).
  - **`1000px`**: Complex forms / Master-detail views (2–3 columns, tables inside modal, multi-step forms, batch inputs).
  - **`1200px`**: Large data views (Full data tables, complex dashboards, multi-tab comprehensive data management).

## 6. Dashboard & Chart UI Design Guidelines
* **Card Container Structure**:
  - **Grid Layout**: Use responsive grids (e.g., `grid grid-cols-1 md:grid-cols-2 gap-5`).
  - **Card Container**: Standard style `border border-border rounded-xl p-4 bg-white dark:bg-gray-900`.
  - **Card Header**: Uppercase, tracking-wider, subtle title (`text-xs font-bold text-foreground uppercase tracking-wider m-0`).

* **Loading & Empty State Pattern**:
  - **Loading Overlay**: Wrap the entire component template inside Ant Design Vue `<Spin :spinning="props.loading">`.
  - **Empty State**: Use `<div v-else class="py-12 flex justify-center"><Empty :description="..." /></div>` when the dataset is empty (`data.length === 0`).
  - **Chart Re-rendering Lifecycle**:
    - Pass `loading: boolean` as a component `prop`.
    - Use `watch(() => props.data, () => updateCharts(), { deep: true, immediate: true })` to re-trigger chart updates whenever data changes.
    - Inside `updateCharts()`, always call `await nextTick()` to ensure DOM refs (`ref<EchartsUIType>()`) are mounted and rendered before calling `renderChart()`.

* **Echarts Chart Standards**:
  - **Signature Color Palette**: Use the standard blue/slate signature color palette for consistency across dashboard charts:
    `['#1890ff', '#5ab1ef', '#3aa0ff', '#94a3b8', '#cbd5e1', '#0076e4', '#7ec2f4', '#64748b']`.
  - **Typography**: `fontFamily: 'system-ui, -apple-system, sans-serif'`, text color `#4b5563`, font size `11px` for legends/labels.
  - **Bar Charts**: Use rounded right edges `borderRadius: [0, 4, 4, 0]`, `barWidth: '52%'`, and position value labels on the right (`position: 'right'`).
  - **Donut / Pie Charts**: Use hollow donut radius `['55%', '78%']`, slice `borderRadius: 8`, `borderColor: '#fff'`, centered summary label (`position: 'center'`), and formatted legend showing calculated percentage with string truncation for long labels.

## 7. TypeScript API Model Naming & Nullable Field Guidelines
* **Snake_case API Naming**: Backend API entities in this project strictly use `snake_case` (e.g., `equipment_code`, `equipment_name`, `maintenance_plan_id`). All frontend API calls and TypeScript interfaces MUST consistently use `snake_case` matching the backend models and must NOT mix `camelCase` (e.g. do NOT use `equipmentCode`, always use `equipment_code`).
* **Nullable Field Typing**: Fields from backend APIs that can return `null` (e.g., `equipment_name`, `notes`, `deleted_at`) MUST be explicitly typed as nullable (`string | null | undefined`) in TypeScript interfaces to avoid `TS2322` type assignment incompatibility errors.

## 8. Nested Dynamic Items List UI Design Guidelines
* **Standard Nested List Layout**: When displaying dynamic list items within cards or forms (such as checklist detail items or manual schedule rows), prefer the clean `divide-y divide-border` list pattern with a scrollable container (`max-h-[300px..360px] overflow-y-auto divide-y divide-border pr-2 scrollbar-thin`) and item rows (`flex items-center gap-3 py-3 first:pt-0 last:pb-0`), instead of wrapping each line in heavy nested cards.
* **Deletion Confirmations**: Item delete actions MUST be wrapped in Ant Design Vue `<Popconfirm>` with clear confirmation titles.
* **Reference Sample Component**: Reference [checklist/detail.vue](file:///c:/Users/khanh/Projects/eamo/frontend/src/views/ops/checklist/detail.vue) for the benchmark implementation of this nested item list style.

## 9. Token Management & Proactive Silent Refresh Rules
* **Proactive Refresh Schedule**: Always maintain background token refresh timers via `scheduleProactiveTokenRefresh(expiresInSeconds)` inside `handleCallback()` and `refreshAccessToken()`.
* **Zero 401 Disruption**: Do not rely solely on reactive HTTP 401 error interceptors. Proactive timer refresh MUST renew the `accessToken` 5 minutes prior to expiration.
* **Tab Visibility Resilience**: Ensure `visibilitychange` listener in `pkce.ts` checks token freshness when returning to active tab.
* **Timer Memory Safety**: Whenever modifying authentication/logout flow, ALWAYS invoke `clearProactiveRefreshTimer()` during logout or session invalidation to prevent memory leaks.





