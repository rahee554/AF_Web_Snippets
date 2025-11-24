# Print Layout Package - Documentation

## Overview

The AF Snippets Print Layout package provides a professional, production-ready print template system for Laravel applications. It offers advanced features for document generation, page management, and customizable printing capabilities.

## Features

### Core Features
- **Responsive Paper Sizes**: A3, A4, A5, Letter, Legal, Tabloid
- **Portrait & Landscape Orientation**: Full directional support
- **Dynamic Watermarks**: Image-based watermarks with opacity control
- **Header & Footer Management**: Fixed headers/footers on every printed page
- **Automatic Page Breaks**: Smart table pagination with header repetition
- **Page Numbering**: Configurable position and display options
- **Content Scaling**: 6 predefined scaling levels (60% - 160%)
- **Print Controls**: Interactive UI for live preview adjustment
- **LocalStorage Persistence**: User preferences saved between sessions

### Print Controls
- Paper size and orientation selection
- Content scale adjustment
- Zoom levels (50% - 150%)
- Watermark visibility and opacity
- Table title visibility
- Page number positioning
- Table font size and row height customization

## Installation

### Step 1: Install Package
```bash
composer require artflow-studio/snippets
```

### Step 2: Publish Assets
```bash
php artisan af-snippets:print-assets-install
```

For force replacement of existing files:
```bash
php artisan af-snippets:print-assets-install --force
```

### Step 3: Verify Installation
Assets should be available at: `public/assets/print/`
- `public/assets/print/af-print.css`
- `public/assets/print/af-print.js`

## Usage

### Basic Print Layout (Blade Template)

```blade
@extends('af-snippets::print.print-layout', [
    'documentTitle' => 'Invoice',
    'companyName' => 'Your Company',
    'paperSize' => 'A4',
    'orientation' => 'portrait',
])

@section('content')
    <h2>Your Document Content</h2>
    <p>Content goes here...</p>
@endsection
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `documentTitle` | string | 'Document' | Document title shown in browser and controls |
| `companyName` | string | 'Your Company Name' | Company name displayed in header |
| `paperSize` | string | 'A4' | Paper size (A4, A3, A5, Letter, Legal, Tabloid) |
| `orientation` | string | 'portrait' | Portrait or landscape |
| `contentScale` | string | 'md' | Content scaling (xs, sm, md, lg, xl, xxl) |
| `showWatermark` | bool | true | Display watermark image |
| `watermarkImage` | string | company logo | Watermark image URL |
| `watermarkText` | string | '' | Watermark text overlay |
| `showHeader` | bool | true | Display fixed header |
| `showFooter` | bool | true | Display fixed footer |
| `showPageNumbers` | bool | true | Display page numbers |
| `pageNumberPosition` | string | 'bottom-right' | Position (bottom-left, bottom-center, bottom-right) |
| `showTableTitles` | bool | true | Show table continuation titles on page breaks |
| `tableTitle` | string | 'Table' | Default table title text |
| `companyLogo` | string | asset path | Company logo URL |

### Using Stack Sections

#### Header Section
```blade
@push('print-header')
    <div style="text-align: center;">
        <h1>{{ $documentTitle }}</h1>
        <p>{{ $companyName }}</p>
    </div>
@endpush
```

#### Footer Section
```blade
@push('print-footer')
    <div style="display: flex; justify-content: space-between;">
        <span>{{ $companyName }}</span>
        <span class="page-no">Page 1</span>
    </div>
@endpush
```

### Auto-Splitting Tables

For large tables that span multiple pages, use the `splittable-table` class:

```blade
<table class="splittable-table">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
        </tr>
    </thead>
    <tbody>
        {{-- Table rows here --}}
    </tbody>
</table>
```

The table will:
- Automatically split across pages
- Repeat the header row on each page
- Show continuation titles (if enabled)

## Livewire Integration

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class PrintDocument extends Component
{
    public function render()
    {
        return view('livewire.print-document', [
            'documentTitle' => 'My Document',
            'companyName' => 'My Company',
            'paperSize' => 'A4',
        ]);
    }
}
```

**Note**: Livewire page components require a layout. Use `components/layouts/app.blade.php`:

```blade
<!-- resources/views/components/layouts/app.blade.php -->
<div>
    {{ $slot }}
</div>
```

## CSS & JavaScript

The package includes:
- **af-print.css**: Print styling and layout CSS
- **af-print.js**: Print controls and page management JavaScript

These are automatically included in the print layout view.

## Advanced Styling

Custom CSS variables for print layout:

```css
:root {
    --page-width: 8.27in;
    --page-height: 11.69in;
    --header-h: 75px;
    --footer-h: 50px;
    --table-font-size: 12px;
}
```

Override these in your stylesheet for custom layouts.

## Examples

See `resources/views/test/print-test-2.blade.php` for a complete example with:
- Trip summary cards
- Passenger manifest table
- Multi-column inclusions
- Financial summary
- 65-row itemized ledger with auto-pagination
- Notes section

## Publishing Assets

### Via Artisan
```bash
php artisan af-snippets:print-assets-install --force
```

### Publish Views
```bash
php artisan vendor:publish --tag=af-snippets-print-views --force
```

### Publish Assets
```bash
php artisan vendor:publish --tag=af-snippets-print-assets --force
```

## Project Structure

```
vendor/artflow-studio/snippets/
├── src/
│   ├── Console/Commands/
│   │   └── PublishPrintAssets.php
│   ├── public/print/
│   │   ├── af-print.css
│   │   └── af-print.js
│   ├── views/
│   └── SnippetsServiceProvider.php
├── resources/
│   └── views/
│       └── print/
│           ├── print-layout.blade.php
│           └── controls.blade.php
├── PRINT.md (this file)
└── composer.json
```

## Asset Locations

After publishing:
- **Views**: `resources/views/vendor/af-snippets/print/`
- **CSS/JS**: `public/assets/print/`

## Browser Support

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Print preview: Full support in all browsers

## Troubleshooting

### Command Not Found
```bash
composer dump-autoload
php artisan cache:clear
```

### Assets Not Loading
```bash
php artisan af-snippets:print-assets-install --force
```

### Page Breaks Not Working
Ensure tables have the `splittable-table` class applied.

### Livewire Multiple Root Elements Error
Create `resources/views/components/layouts/app.blade.php` with a single root div.

## Support & Contributing

For issues or contributions, refer to the main package repository.

---

**Version**: 1.0.0  
**Last Updated**: November 24, 2025  
**Maintainer**: Artflow Studio
