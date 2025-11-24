# AF Snippets: Print Layout Package

Complete print layout system for Laravel Livewire applications with advanced table pagination, watermarking, and responsive print controls.

## Features

- **Responsive Print Layout** - Fixed header/footer with dynamic pagination
- **Table Splitting** - Automatically splits large tables across pages with header repetition
- **Watermarking** - Configurable watermark with opacity control (15% default opacity)
- **Print Controls Panel** - Sidebar UI to adjust paper size, orientation, scale, and watermark
- **Page Numbering** - Automatic page numbering with customizable positioning
- **Dynamic Configuration** - Save/load print settings via localStorage
- **Multi-format Support** - A4, A3, A5, Letter, Legal, Tabloid

## Installation

### 1. Copy Files to Package
Files are automatically organized in the package:
- Views: `resources/views/print/`
- CSS: `resources/css/af-print.css`
- JS: `resources/js/af-print.js`

### 2. Publish Assets
Run the installation command to publish assets to your public directory:

```bash
php artisan af-snippets:print-assets-install
```

This creates:
- `public/vendor/af-snippets/css/af-print.css`
- `public/vendor/af-snippets/js/af-print.js`

### 3. (Optional) Publish Views
To customize views:

```bash
php artisan vendor:publish --tag=af-snippets-print-views
```

Publishes to: `resources/views/vendor/af-snippets/print/`

## Usage

### Basic Layout Extension

```blade
@extends('snippets::print.print-layout', [
    'documentTitle' => 'My Report',
    'companyName' => 'My Company',
    'paperSize' => 'A4',
    'orientation' => 'portrait',
])

@push('print-header')
    <div>
        <h2>Report Header</h2>
        <p>Your header content here</p>
    </div>
@endpush

@section('content')
    <section class="print-section">
        <h3>Section Title</h3>
        <p>Your content here</p>
    </section>

    <!-- Large tables will auto-split -->
    <section class="print-section">
        <h3>Data Table</h3>
        <table class="splittable-table">
            <thead>...</thead>
            <tbody>...</tbody>
        </table>
    </section>
@endsection

@push('print-footer')
    <div>
        <small>Footer content here</small>
    </div>
@endpush
```

### In Livewire Component

```php
class ReportComponent extends Component
{
    public function render()
    {
        return view('snippets::print.print-layout', [
            'documentTitle' => 'My Report',
            'companyName' => config('app.name'),
            'showWatermark' => true,
            'watermarkImage' => asset('path/to/logo.svg'),
        ]);
    }
}
```

## Configuration Options

Pass these to the layout via view data:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `documentTitle` | string | 'Document' | Page title |
| `companyName` | string | 'Your Company Name' | Company/organization name |
| `companyLogo` | string | `assets/media/logos/logo.png` | Company logo path |
| `watermarkImage` | string | `assets/media/logos/logo.svg` | Watermark logo path |
| `paperSize` | string | 'A4' | Paper size (A4, A3, A5, Letter, Legal, Tabloid) |
| `orientation` | string | 'portrait' | Page orientation (portrait, landscape) |
| `contentScale` | string | 'md' | Content scale (xs, sm, md, lg, xl, xxl) |
| `showHeader` | bool | true | Show fixed header |
| `showFooter` | bool | true | Show fixed footer |
| `showWatermark` | bool | true | Show watermark |
| `showPageNumbers` | bool | true | Show page numbers |
| `showTableTitles` | bool | true | Show continuation titles on split tables |
| `tableTitle` | string | 'Table' | Text for table continuation titles |
| `pageNumberPosition` | string | 'bottom-right' | Page number position (bottom-left, bottom-center, bottom-right) |

## Watermark System

The watermark appears centered on all printed pages at 15% opacity by default.

### Control via UI
Users can adjust watermark opacity (0-100%) and toggle visibility using the print controls panel (slide-in sidebar).

### CSS Variable
The opacity is controlled by CSS variable `--watermark-opacity`. The watermark:
- Uses `z-index: 999` in print mode to appear above all content
- Has `mix-blend-mode: multiply` for visual integration
- Is positioned fixed at page center

### Setting Custom Watermark
```blade
@extends('snippets::print.print-layout', [
    'watermarkImage' => asset('custom/watermark.png'),
])
```

## Table Splitting

Tables with class `splittable-table` automatically split across pages:

```blade
<table class="splittable-table">
    <thead>
        <tr>
            <th>Header 1</th>
            <th>Header 2</th>
        </tr>
    </thead>
    <tbody>
        @foreach($largeDataset as $item)
            <tr>
                <td>{{ $item->col1 }}</td>
                <td>{{ $item->col2 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
```

Features:
- Header row repeats on each page
- Automatic row grouping based on page height
- Configurable continuation titles: "(Table - 2)", "(Table - 3)", etc.
- Continuation titles hide on first fragment, show on subsequent ones

## Print Controls Panel

The sidebar provides real-time control:

1. **Paper Size & Orientation** - Switch between 6 paper sizes in portrait/landscape
2. **Content Scale** - Scale content 60% to 160%
3. **Zoom Level** - Browser zoom 50% to 150%
4. **Watermark** - Toggle visibility and adjust opacity (0-100%)
5. **Table Titles** - Toggle and customize continuation titles
6. **Page Numbers** - Toggle page numbering
7. **Table Controls** - Adjust font size and row padding

Settings are saved to localStorage and persist across sessions.

## CSS Classes

### Container Classes
- `.print-wrapper` - Main print container
- `.print-header` - Fixed header (repeats on every page)
- `.print-footer` - Fixed footer (repeats on every page)
- `.print-content` - Main content area
- `.print-section` - Content section (break-inside: avoid)

### Utility Classes
- `.splittable-table` - Tables that auto-split across pages
- `.no-print` - Elements hidden during printing
- `.page-no` - Page number display element

## Styling

The layout includes:
- Professional base styling with Bootstrap integration
- Print-specific CSS via `@media print`
- Dynamic header/footer heights
- Responsive spacing and typography
- Dark mode compatible

## File Structure

```
vendor/artflow-studio/snippets/
├── resources/
│   ├── views/print/
│   │   ├── print-layout.blade.php    # Main layout
│   │   └── controls.blade.php         # Print controls UI
│   ├── css/
│   │   └── af-print.css              # Print styles
│   └── js/
│       └── af-print.js               # Pagination & controls JS
├── src/Print/
│   ├── PrintLayoutServiceProvider.php # Service provider
│   └── Commands/
│       └── PublishPrintAssets.php     # Asset publishing command
└── composer.json
```

## JavaScript API

Available globally after loading `af-print.js`:

```javascript
// Prepare content for printing
await window.prepareForPrint();

// Manually split tables
await window.splitAllSplittableTables();

// Simulate pagination
await window.simulatePaginationAndApply();

// Restore original tables
window.restoreSplittableTables();

// Apply header/footer heights
window.applyHeaderFooterHeights();
```

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Opera 76+

## Performance Notes

- DPI detection is cached per session
- Table cloning only occurs for `splittable-table` elements
- Pagination uses greedy algorithm for optimal page breaks
- Minimal reflow with efficient DOM updates

## License

MIT License © 2024 Artflow Studio

## Support

For issues or suggestions, please contact the development team.
