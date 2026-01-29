# Products by Attribute Shortcode

## Usage

The `[products_by_attribute]` shortcode displays products filtered by a specific attribute in a Swiper.js carousel.

### Basic Example

```
[products_by_attribute attribute="brand" value="adidas,nike" title="Brand Products"]
```

### Parameters

- **attribute** (required): The attribute name to filter by (e.g., "brand", "color", "size")
- **value** (required): The attribute value(s) to filter by. Multiple values can be comma-separated (e.g., "adidas,nike")
- **title** (optional): The heading text displayed above the carousel. Default: "Products"
- **limit** (optional): Maximum number of products to display. Default: 12

### Examples

#### Single Brand
```
[products_by_attribute attribute="brand" value="adidas" title="Adidas Products"]
```

#### Multiple Brands
```
[products_by_attribute attribute="brand" value="adidas,nike" title="Top Brands"]
```

#### With Custom Limit
```
[products_by_attribute attribute="brand" value="adidas" title="Adidas Products" limit="8"]
```

#### Different Attributes
```
[products_by_attribute attribute="color" value="red,blue" title="Colorful Products"]
```

## API Endpoint

The shortcode calls: `api/attribute/{attribute}/{value}`

Example: `api/attribute/brand/adidas,nike`

## Expected API Response

```json
{
  "success": true,
  "attribute": "brand",
  "value": "adidas",
  "count": 3,
  "products": [
    {
      "id": 123,
      "sku": "AD-001",
      "type": "simple",
      "name": "Adidas Product",
      "price": 79.9,
      "url_key": "adidas-product",
      "created_at": "2025-11-22T..."
    }
  ]
}
```

## Features

- Responsive Swiper.js carousel
- Navigation arrows
- Pagination dots
- Breakpoints:
  - Mobile (0-639px): 1 slide per view
  - Tablet (640-1023px): 2 slides per view
  - Desktop (1024px+): 4 slides per view
- Product images with fallback handling
- Product links to single product pages
- Price and SKU display
