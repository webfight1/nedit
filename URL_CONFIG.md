# Language-Specific URL Configuration

## Overview

The theme now supports language-specific URLs for main pages. URLs automatically switch based on the current multisite blog ID.

## Configuration Files

URLs are defined in the translation files under the `_urls` array:

- **Estonian** (`inc/translations/et.php`): `/ostukorv/`, `/kassa/`, `/aitah/`, etc.
- **English** (`inc/translations/en.php`): `/cart/`, `/checkout/`, `/thank-you/`, etc.

## Available URL Keys

| Key | Estonian URL | English URL | Description |
|-----|-------------|-------------|-------------|
| `cart` | `/ostukorv/` | `/cart/` | Shopping cart page |
| `checkout` | `/kassa/` | `/checkout/` | Checkout page |
| `thank_you` | `/aitah/` | `/thank-you/` | Order confirmation page |
| `orders` | `/tellimused/` | `/orders/` | Customer orders page |
| `profile` | `/profiil/` | `/profile/` | Customer profile page |
| `wishlist` | `/soovinimekiri/` | `/wishlist/` | Wishlist page |
| `address` | `/aadressid/` | `/addresses/` | Address management page |
| `products` | `/tooted/` | `/products/` | Products listing page |
| `register` | `/registreeru/` | `/register/` | Registration page |

## Usage

### In PHP Templates

Use the `nailedit_get_url()` helper function:

```php
// Cart link
<a href="<?php echo esc_url( nailedit_get_url( 'cart' ) ); ?>">
    <?php nailedit_t( 'go_to_cart' ); ?>
</a>

// Checkout button
<a href="<?php echo esc_url( nailedit_get_url( 'checkout' ) ); ?>">
    <?php nailedit_t( 'go_to_checkout' ); ?>
</a>

// Register link
<a href="<?php echo esc_url( nailedit_get_url( 'register' ) ); ?>">
    <?php nailedit_t( 'register' ); ?>
</a>
```

### In JavaScript (via wp_localize_script)

```php
wp_localize_script(
    'my-script',
    'MyConfig',
    array(
        'cartUrl'     => nailedit_get_url( 'cart' ),
        'checkoutUrl' => nailedit_get_url( 'checkout' ),
        'thankYouUrl' => nailedit_get_url( 'thank_you' ),
    )
);
```

Then in JavaScript:
```javascript
window.location.href = MyConfig.checkoutUrl;
```

## How It Works

1. **Language Detection**: The `nailedit_get_url()` function uses `nailedit_get_current_lang()` to detect the current language based on blog ID
2. **URL Lookup**: It loads the appropriate translation file and retrieves the URL from the `_urls` array
3. **Full URL Generation**: It wraps the path with `home_url()` to create the complete URL

## Adding New URLs

To add a new language-specific URL:

1. Add the key and path to `inc/translations/et.php`:
```php
'_urls' => array(
    // ... existing URLs
    'my_new_page' => '/minu-uus-leht/',
),
```

2. Add the same key with English path to `inc/translations/en.php`:
```php
'_urls' => array(
    // ... existing URLs
    'my_new_page' => '/my-new-page/',
),
```

3. Use in templates:
```php
<a href="<?php echo esc_url( nailedit_get_url( 'my_new_page' ) ); ?>">Link</a>
```

## Important Notes

- **Product and Category URLs** (`/product/123/`, `/category/slug/`) are NOT language-specific - they remain the same in both languages as they come from Bagisto API
- Always use `esc_url()` when outputting URLs in HTML
- The function returns a full URL with `home_url()`, so no need to add domain manually
- If a URL key is not found, it falls back to `home_url( '/' . $key . '/' )`

## Updated Files

The following files have been updated to use `nailedit_get_url()`:

- `page-cart.php` - Checkout button
- `functions.php` - Thank you page URL in checkout config
- `header.php` - Cart icon link, Register link
- `page-wishlist.php` - Login/address link

## Testing

To verify URL switching works:

1. Visit Estonian site (blog-id-1): Links should use `/ostukorv/`, `/kassa/`, etc.
2. Visit English site (blog-id-3): Links should use `/cart/`, `/checkout/`, etc.
3. Check browser console for any 404 errors
4. Test checkout flow end-to-end on both sites
