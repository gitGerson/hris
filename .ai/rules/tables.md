---
paths:
  - 'app/Filament/Resources/**/Tables/**'
---

# Tables

## Filament table pattern: session persistence and icon-only row actions
Every resource table follows the same shape:

1. Persistence — chain these after `->defaultSort(...)` so a user's view survives navigation:
   `->persistFiltersInSession()->persistSearchInSession()->persistSortInSession()->persistColumnsInSession()`
   Any column used by `defaultSort` must be `->sortable()`.

2. Row actions are icon-only, with a tooltip carrying the label and colour carrying the meaning:
   `ViewAction::make()->iconButton()->tooltip('View')->color('info')`
   `EditAction::make()->iconButton()->tooltip('Edit')->color('warning')`
   Registered colours: primary, gray, info, danger, warning, success. Do not use `primary` for row actions — the panel overrides it to Rose.

3. State changes (activate/deactivate etc.) belong in the table, not the form, as an `Action` with `->requiresConfirmation()` and direction-aware `modalHeading`/`modalDescription`. `ToggleColumn` cannot confirm — `requiresConfirmation()` lives on Actions only — so use an icon button (`Heroicon::OutlinedPower`, `danger` when on / `success` when off).

4. Booleans render as a badge, not an icon: `TextColumn::make('is_active')->label('Status')->badge()->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')->color(fn (bool $state) => $state ? 'success' : 'danger')`.

5. `TernaryFilter` must set `->trueLabel('Active')->falseLabel('Inactive')` — `->label()` only renames the field, leaving the options as Yes/No.

Filters stay in Filament's default dropdown; do not set `filtersLayout`.
