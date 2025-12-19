# FatObject Casino Plugin

**Version:** 1.0  
**Author:** SiteForYou  
**License:** GPL2

---

## Description

FatObject Casino is a WordPress plugin for integrating with a casino API.  
It allows you to fetch casino brands, options for select fields, and details for a specific brand.

---

## Installation

1. Copy the plugin folder to `wp-content/plugins/`.
2. Install Composer dependencies and generate the autoloader:

```
cd wp-content/plugins/fatobject-casino
composer install
```

3. Activate the plugin via WordPress Admin → Plugins.
4. Go to **Settings → FOC API** to configure the Base URL and API Token.

---

## Settings

- **Base URL** – the API base URL.
- **API Token** – the authentication token for the API.

---

## Template Overrides

FatObject Casino provides default templates for its custom post types.

- Example: Overriding single-brand.php

1. In your theme folder, create a fatobject-casino directory:

```
theme/
└─ fatobject-casino/
```

2. Add your custom template for the Brand post type:

```
theme/
└─ fatobject-casino/
    ├─ single-brand.php
    └─ single-slot.php
```

3. WordPress will use your theme’s template if it exists. Otherwise, it will fallback to the plugin’s default template:

```
plugins/fatobject-casino/templates/single-brand.php
plugins/fatobject-casino/templates/single-slot.php
```

## Setting up Nginx Proxy for Image Uploads

Follow these steps to add proxy rules for image directories in your main Nginx configuration and reload Nginx:

1. Add proxy rules to Nginx

```
# Proxy for casino brand images
location /uploads/casino_brand/ {
    proxy_pass https://EXAMPLE.com/uploads/casino_brand/;
    proxy_set_header Host fatobject.studio;
}

# Proxy for payment system images
location /uploads/payment_system/ {
    proxy_pass https://EXAMPLE.com/uploads/payment_system/;
    proxy_set_header Host fatobject.studio;
}

# Proxy for software provider images
location /uploads/software-provider/ {
    proxy_pass https://EXAMPLE.com/uploads/software-provider/;
    proxy_set_header Host fatobject.studio;
}

# Proxy for license images
location /uploads/license/ {
    proxy_pass https://EXAMPLE.com/uploads/license/;
    proxy_set_header Host fatobject.studio;
}

# Proxy for slot images
location /uploads/slot/ {
    proxy_pass https://EXAMPLE.com/uploads/slot/;
    proxy_set_header Host fatobject.studio;
}

# Proxy for game images
location /uploads/game/ {
    proxy_pass https://EXAMPLE.com/uploads/game/;
    proxy_set_header Host fatobject.studio;
}
```

2. Reload Nginx

## API Strategy Pattern Diagram

The FOC API integration uses a Strategy Pattern to handle multiple API modules in a consistent and flexible way.

```
FocApiInterface
│
├─ Defines methods: getPaginated(), getOptions(), getById()
│
FocApi (base class)
│
├─ Implements shared API logic (request sending, parsing, common query params)
│
├─ Concrete modules extend FocApi and implement FocApiInterface
│   ├─ FocApiBrand
│   ├─ FocApiSlot
│   └─ ... other API modules
│
└─ FocApiProxyTrait
    └─ Provides convenient public methods that wrap protected FocApi methods

FocApiService
│
├─ Holds a "strategy" property of type FocApiInterface
├─ setStrategy() → dynamically assign a module (Brand, Bonus, Slot)
└─ getPaginated(), getOptions(), getById() → delegates to current strategy

Usage Example:
───────────────
$service = new FocApiService();

$service->setStrategy(new FocApiBrand($baseUrl, $token));
$brands = $service->getPaginated();

$service->setStrategy(new FocApiBonus($baseUrl, $token));
$bonuses = $service->getPaginated();

$service->setStrategy(new FocApiSlot($baseUrl, $token));
$slots = $service->getPaginated();
```

## Shortcodes

The plugin provides several shortcodes for displaying casino-related content. All shortcodes can be used in posts, pages, Gutenberg blocks, or widgets.

1. Brand bonuses (displays all available bonuses for the current brand).
```
[foc_brand_bonuses]
```