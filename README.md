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
│   ├─ FocApiBonus
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