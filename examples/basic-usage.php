<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_newproducts PrestaShop module.
 *
 * ps_newproducts displays a block of recently added products on the storefront.
 * Products are filtered by creation date using a configurable "new product"
 * window (e.g., added within the last 20 days). Renders via the widget system.
 *
 * This file documents common usage patterns.
 */

// --- Widget invocation in Smarty/Twig template ---
// {widget name="ps_newproducts" hook="displayHome"}

// --- Querying new products programmatically ---
// Use PrestaShop's ProductController or Product model:
//
// $newDays  = (int) Configuration::get('PS_NB_DAYS_NEW_PRODUCT'); // default: 20
// $products = Product::getNewProducts(
//     id_lang: (int) Context::getContext()->language->id,
//     pageNumber: 0,
//     nbProducts: 8,
// );
//
// foreach ($products as $product) {
//     $addedDate = new DateTime($product['date_add']);
//     echo $product['name'] . ' — added: ' . $addedDate->format('Y-m-d') . "\n";
// }

// --- Back Office configuration ---
// Modules > New Products:
//   - Number of products to display (default: 8)
//   - Hook placement (home, left/right column)
// Shop Parameters > Products > "New products" window (days)

// --- Template override ---
// themes/{theme}/modules/ps_newproducts/views/templates/hook/ps_newproducts.tpl
