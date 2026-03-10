# Tõlkesüsteemi kasutamine Page Template'ides

## Funktsioonid

### 1. `nailedit_get_current_lang()`
Tagastab praeguse keele (`'et'`, `'en'` või `'ru'`) multisite blog ID põhjal:
- Blog ID 1 → `'et'`
- Blog ID 3 → `'en'`
- Blog ID 4 → `'ru'`

### 2. `nailedit_t( $key )`
Tõlgib ja kuvab teksti (echo):
```php
<h1><?php nailedit_t('cart_empty'); ?></h1>
// Väljund ET saidil: Sinu ostukorv on tühi.
// Väljund EN saidil: Your cart is empty.
// Väljund RU saidil: Ваша корзина пуста.
```

### 3. `nailedit_get_t( $key )`
Tõlgib ja tagastab teksti (return):
```php
$message = nailedit_get_t('cart_empty');
echo '<p>' . esc_html($message) . '</p>';
```

## Näited

### HTML väljundis
**Enne:**
```php
<p><?php esc_html_e( 'Sinu ostukorv on tühi.', 'nailedit' ); ?></p>
```

**Pärast:**
```php
<p><?php nailedit_t('cart_empty'); ?></p>
```

### JavaScript stringides
**Enne:**
```javascript
root.innerHTML = '<p><?php echo esc_js( __( 'Sinu ostukorv on tühi.', 'nailedit' ) ); ?></p>';
```

**Pärast:**
```javascript
root.innerHTML = '<p><?php echo esc_js( nailedit_get_t('cart_empty') ); ?></p>';
```

### Alert/Confirm dialoogides
**Enne:**
```javascript
if (confirm('<?php echo esc_js( __( 'Kas oled kindel?', 'nailedit' ) ); ?>')) {
```

**Pärast:**
```javascript
if (confirm('<?php echo esc_js( nailedit_get_t('confirm_remove_item') ); ?>')) {
```

## Tõlkevõtmed (Translation Keys)

Kõik tõlkevõtmed on defineeritud:
- `/inc/translations/et.php` - eesti keele tõlked
- `/inc/translations/en.php` - inglise keele tõlked
- `/inc/translations/ru.php` - vene keele tõlked

### Ostukorv (Cart)
- `cart_empty` - "Sinu ostukorv on tühi." / "Your cart is empty."
- `product` - "Toode" / "Product"
- `quantity` - "Kogus" / "Quantity"
- `price` - "Hind" / "Price"
- `total` - "Kokku" / "Total"
- `actions` - "Tegevused" / "Actions"
- `remove` - "Kustuta" / "Remove"
- `order_summary` - "Tellimuse kokkuvõte" / "Order Summary"
- `total_amount` - "Summa kokku:" / "Total Amount:"
- `have_coupon` - "Kas sul on kupongikood?" / "Have a coupon code?"
- `coupon_code` - "Kupongikood" / "Coupon Code"
- `apply` - "Rakenda" / "Apply"
- `remove_coupon` - "Eemalda kupong" / "Remove Coupon"
- `go_to_checkout` - "Mine kassasse" / "Go to Checkout"
- `confirm_remove_item` - "Kas oled kindel, et soovid selle toote eemaldada?" / "Are you sure you want to remove this item?"

### Üldised (General)
- `home` - "Avaleht" / "Home"
- `description` - "Kirjeldus" / "Description"
- `specifications` - "Spetsifikatsioonid" / "Specifications"
- `reviews` - "Arvustused" / "Reviews"
- `buy` - "Osta" / "Buy"
- `add_to_cart` - "Lisa ostukorvi" / "Add to cart"
- `loading` - "Laen..." / "Loading..."
- `error` - "Viga" / "Error"

## Uute tõlgete lisamine

1. Lisa tõlkevõti kõikidesse failidesse:

**`/inc/translations/et.php`:**
```php
'my_new_key' => 'Minu uus tekst',
```

**`/inc/translations/en.php`:**
```php
'my_new_key' => 'My new text',
```

**`/inc/translations/ru.php`:**
```php
'my_new_key' => 'Мой новый текст',
```

2. Kasuta template'is:
```php
<p><?php nailedit_t('my_new_key'); ?></p>
```

## Failid, mis vajavad uuendamist

- ✅ `/inc/shortcodes.php` - shortcode'id kasutavad juba tõlkeid
- ⏳ `/page-cart.php` - ostukorv
- ⏳ `/page-checkout.php` - kassa
- ⏳ `/page-orders.php` - tellimused
- ⏳ `/page-products.php` - tooted
- ⏳ `/page-thank-you.php` - tänu leht
- ⏳ `/page-register.php` - registreerimine
- ⏳ `/page-profile.php` - profiil
- ⏳ `/page-wishlist.php` - soovinimekiri
- ⏳ `/single-product.php` - üksik toode

## Asendamise muster

Otsi kõigist failidest:
```regex
__\(|esc_html__|esc_attr__|esc_js.*__
```

Asenda:
- `__( 'Text', 'nailedit' )` → `nailedit_get_t('key')`
- `esc_html__( 'Text', 'nailedit' )` → `nailedit_get_t('key')`
- `esc_html_e( 'Text', 'nailedit' )` → `nailedit_t('key')`
