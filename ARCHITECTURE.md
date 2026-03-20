# Architecture: ps_newproducts

## Purpose

A PrestaShop front-office widget module that displays newly-added products on the homepage. Configurable number of products and "new" window (in days).

## Directory Structure

```
ps_newproducts.php              - Module class; hook listeners, widget renderer, config form
views/templates/hook/           - Smarty template for the new-products widget
tests/                          - PHPUnit test stubs and PHPStan bootstrap
translations/                   - Locale string overrides
```

## Key Design Decisions

- **WidgetInterface**: Implements `WidgetInterface` for compatibility with PrestaShop's widget injection system, making the block positionable from the theme editor.
- **Cache-first rendering**: Template output is Smarty-cached per language/shop context; any product mutation event flushes the cache to prevent stale data.
- **Configuration keys**: `NEW_PRODUCTS_NBR` (count) and `PS_NB_DAYS_NEW_PRODUCT` (recency window in days, shared with the core).

## Extension Points

- Override `getWidgetVariables()` to add extra data to the template context.
- Adjust the hook registrations in `install()` to display the widget in alternative positions.

## Dependency Flow

```
ps_newproducts (Module + WidgetInterface)
  └─> renderWidget()        — Smarty-cached template render
        └─> getWidgetVariables()
              └─> getNewProducts()
                    └─> Product::getNewProducts() (PrestaShop ORM)
  └─> getContent()          — Admin config form
  └─> hookActionProduct*()  — Cache invalidation on product changes
```
