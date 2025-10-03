# Image Gallery and Expandable Description Migration Guide

## Overview

This guide explains how to apply the image gallery and expandable description features to your R&G installation.

## References

- Issue #36: Product Image Gallery
- Issue #37: Expandable Product Description

## Database Migration

### For SQLite

```bash
sqlite3 database.db "ALTER TABLE products ADD COLUMN images TEXT;"
```

### For MySQL/MariaDB

```sql
ALTER TABLE products ADD COLUMN images TEXT NULL COMMENT 'JSON array of image paths for gallery';
```

Or run the migration file:

```bash
mysql -u your_username -p your_database < database/migrations/20250120_add_product_images.sql
```

## Features Implemented

### 1. Image Gallery (refs #36)

- **Multiple Images**: Products can now have multiple images stored as JSON array
- **Thumbnail Navigation**: Click thumbnails to change the main image
- **Visual Feedback**: Active thumbnail highlighted, hover effects
- **Responsive**: Adapts to mobile screens (thumbnails resize to 60px on mobile)

### 2. Expandable Description (refs #37)

- **Auto-Detection**: Shows "Voir plus" button for descriptions > 300 characters
- **Toggle Animation**: Smooth expand/collapse with rotating chevron icon
- **User-Friendly**: Clear "Voir plus" / "Voir moins" labels

### 3. Admin Interface

- **Multiple Upload**: Use `<input type="file" name="images[]" multiple>` to upload multiple images
- **Visual Preview**: Shows existing images with "Principale" badge on first image
- **Backward Compatible**: Legacy single image upload still works

## Usage

### Adding Multiple Images to a Product

1. Go to admin product edit page
2. Use the "Images supplémentaires" field to select multiple images
3. The first image in the array will be the main image
4. Images are stored in `uploads/products/{productId}/`

### JSON Format for Images

```json
["uploads/products/1/img1.jpg", "uploads/products/1/img2.jpg", "uploads/products/1/img3.jpg"]
```

### Manual Database Update (for testing)

```sql
UPDATE products 
SET images = '["uploads/products/1/img1.jpg", "uploads/products/1/img2.jpg"]',
    image = 'uploads/products/1/img1.jpg'
WHERE id = 1;
```

## Backward Compatibility

- Existing products with only the `image` column will continue to work
- The system automatically falls back to single image if `images` is NULL or empty
- No changes needed for existing products

## API Response

The `product_api.php` now returns:

```json
{
  "id": 1,
  "name": "Product Name",
  "image": "/uploads/products/1/img1.jpg",
  "images": [
    "/uploads/products/1/img1.jpg",
    "/uploads/products/1/img2.jpg",
    "/uploads/products/1/img3.jpg"
  ],
  ...
}
```

## Color Scheme

The implementation maintains the existing R&G color scheme:

- **Primary Blue**: `#1D3557` - Main elements
- **Gold**: `#D3AA36` - Accents and highlights
- **Light Blue**: `#3b82f6` - Hover states

## Mobile Responsiveness

- Desktop: 2-column grid layout
- Mobile (≤768px): Single column layout
- Thumbnails: 80px desktop, 60px mobile
- Sticky image section on desktop (position: sticky)

## Testing

Test scenarios covered:

1. ✅ Product with multiple images - gallery displays correctly
2. ✅ Product with single image - backward compatible
3. ✅ Product with no image - shows placeholder
4. ✅ Long description - toggle button appears
5. ✅ Short description - no toggle button
6. ✅ Mobile responsive - layout adapts correctly
7. ✅ Click thumbnail - main image changes
8. ✅ Click toggle - description expands/collapses

## Troubleshooting

### Images not displaying

- Check that the `uploads/products/{id}/` directory exists and is writable
- Verify image paths in the database don't have leading slashes in the JSON
- Ensure the web server can serve files from the uploads directory

### Gallery not working

- Check browser console for JavaScript errors
- Verify the `changeMainImage()` function is loaded
- Ensure thumbnails have the `onclick` attribute

### Toggle button not appearing

- Description must be > 300 characters for button to show
- Check that the JavaScript is loaded properly
- Verify `toggleDescription()` function exists

## Support

For issues or questions, please refer to:
- Issue #36: https://github.com/adam-KUROPATWA-BUTTE/R-G/issues/36
- Issue #37: https://github.com/adam-KUROPATWA-BUTTE/R-G/issues/37
