<!DOCTYPE html>

<html lang="ens">

{{-- ## PHP Variables --}}
@php
    // Enhanced Configuration Variables

    // Paper sizes in inches (width x height) - defined early so we can validate defaults
    $paperSizes = [
        'A4' => ['width' => 8.27, 'height' => 11.69],
        'A3' => ['width' => 11.69, 'height' => 16.54],
        'Letter' => ['width' => 8.5, 'height' => 11],
        'Legal' => ['width' => 8.5, 'height' => 14],
        'Tabloid' => ['width' => 11, 'height' => 17],
        'A5' => ['width' => 5.83, 'height' => 8.27],
    ];

    $siteHeader = false;
    $siteFooter = false;

    // Ensure default page size is A4 and validate incoming value (case-insensitive)
    $paperSize = $paperSize ?? 'A4';
    $validPaperSizes = array_map('strtolower', array_keys($paperSizes));
    if (!in_array(strtolower($paperSize), $validPaperSizes, true)) {
        $paperSize = 'A4';
    }

    $orientation = $orientation ?? 'portrait'; // portrait, landscape
    $showWatermark = $showWatermark ?? true;
    $watermarkText = $watermarkText ?? '';
    $watermarkImage = $watermarkImage ?? '';
    $showHeader = $showHeader ?? true;
    $showFooter = $showFooter ?? true;
    $companyLogo = $companyLogo ?? asset('assets/media/logos/logo.png');
    $companyName = $companyName ?? 'Your Company Name';
    $documentTitle = $documentTitle ?? 'Document';
    $showPageNumbers = $showPageNumbers ?? true;

    // New Enhanced Options
    $showPageBreaks = $showPageBreaks ?? true; // Show visual page breaks in preview
    $pageNumberPosition = $pageNumberPosition ?? 'bottom-right'; // bottom-left, bottom-right, bottom-center, none
    $footerDisplay = $footerDisplay ?? 'all-pages'; // all-pages, last-page-only, none
    $contentScale = $contentScale ?? 'md'; // xs, sm, md, lg, xl, xxl for font scaling
    $showTableTitles = $showTableTitles ?? true; // Show continuation titles on split tables
    $tableTitle = $tableTitle ?? 'Table'; // Default table continuation title

    // Content scaling factors
    $scaleFactors = [
        'xs' => 0.6,
        'sm' => 0.8,
        'md' => 1.0,
        'lg' => 1.2,
        'xl' => 1.4,
        'xxl' => 1.6,
    ];

    $currentSize = $paperSizes[$paperSize];
    $pageWidth = $orientation === 'landscape' ? $currentSize['height'] : $currentSize['width'];
    $pageHeight = $orientation === 'landscape' ? $currentSize['width'] : $currentSize['height'];

    // Convert inches to pixels (96 DPI)
    $pageWidthPx = round($pageWidth * 96);
    $pageHeightPx = round($pageHeight * 96);
    $scaleFactor = $scaleFactors[$contentScale];

    // Current date and time
    $currentDateTime = now()->format('Y-m-d H:i:s');
    $currentUser = auth()->user()->name ?? 'Guest';

    // Define all control panel option lists:
    $paperOptions = [
        'A4' => 'A4 (210×297mm)',
        'A3' => 'A3 (297×420mm)',
        'A5' => 'A5 (148×210mm)',
        'Letter' => 'Letter (8.5×11")',
        'Legal' => 'Legal (8.5×14")',
        'Tabloid' => 'Tabloid (11×17")',
    ];
    $orientationOptions = [
        'portrait' => 'Portrait',
        'landscape' => 'Landscape',
    ];
    $scaleOptions = [
        'xs' => 'Extra Small (60%)',
        'sm' => 'Small (80%)',
        'md' => 'Medium (100%)',
        'lg' => 'Large (120%)',
        'xl' => 'Extra Large (140%)',
        'xxl' => 'XXL (160%)',
    ];
    $zoomOptions = [
        50 => '50%',
        75 => '75%',
        100 => '100%',
        125 => '125%',
        150 => '150%',
    ];
    $pageNumberPositions = [
        'bottom-left' => 'Bottom Left',
        'bottom-center' => 'Bottom Center',
        'bottom-right' => 'Bottom Right',
    ];

@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $documentTitle ?? 'Document' }} - Print Template</title>

    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!-- Print Final CSS -->
    <link href="{{ asset('vendor/artflow-studio/snippets/print/af-print.css') }}" rel="stylesheet" type="text/css" />

    <style>
        /* Dynamic Paper Size Variables - CRITICAL for print layout */
        :root {
            --page-width: {{ $pageWidth }}in;
            --page-height: {{ $pageHeight }}in;
            --header-h: 75px;
            --footer-h: 50px;
            --force-top-spacing: calc(var(--header-h) + 15px);
            --page-margin-in: 0.25;
            --force-bottom-spacing: calc(var(--footer-h) + 10px);
            --table-font-size: 12px;
        }
    </style>
    @stack('styles')

</head>


<body>
    <!-- Watermark Element (fixed, appears on all pages during print) -->
    <img id="printWatermark" class="print-watermark" alt="watermark" />

    <div class="app">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay" style="display: none;">
            <div class="loading-spinner"></div>
        </div>

        <!-- On-screen print controls & meta info (not printed) -->
        @include('af-print::controls')

        <!-- Sidebar Toggle Button -->
        <div class="print-controls-toggle no-print" onclick="togglePrintSidebar()">
            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
            </svg>
        </div>

        <script>
            // Sidebar toggle functionality
            function togglePrintSidebar() {
                const sidebar = document.querySelector('.print-controls');
                const toggle = document.querySelector('.print-controls-toggle');
                sidebar.classList.toggle('open');
                toggle.classList.toggle('hidden');
            }

            // Print configuration object
            window.printConfig = {
                showTableTitles: {{ $showTableTitles ? 'true' : 'false' }},
                tableTitle: "{{ $tableTitle }}",
                showPageNumbers: true,
                showWatermark: {{ $showWatermark ? 'true' : 'false' }},
                watermarkOpacity: 15
            };
        </script>

        @if($siteHeader == true)
           
        <header class="site-header no-print">
            <div>
                <h1>{{ $companyName }}</h1>
                <p style="margin: 4px 0 0 0; font-size: 13px;">Prepared by {{ $currentUser }} on {{ $currentDateTime }}</p>
            </div>
        </header>
        @endif

        <!-- Screen content area -->
        <main class="screen-content">
           
            <!-- Print Wrapper - CRITICAL: Container for all print content -->
            <div id="printWrapper"
                class="bg-white zoom-100 page-number-{{ $pageNumberPosition }} {{ !$showPageNumbers ? 'hide-page-numbers' : '' }}">

                {{-- Print header (fixed & repeated on print) --}}
                @if($showHeader)
                    <header class="print-header" aria-hidden="true">
                        @stack('print-header')
                    </header>
                @endif

                {{-- Main content container with proper margin to avoid header/footer overlap --}}
                {{-- CRITICAL: This padding is what prevents content from appearing behind fixed header/footer on print --}}
                <div class="print-content" id="printContent">
                    @yield('content')
                    @stack('content')
                </div>

                {{-- Print footer (fixed & repeated on print) --}}
                @if($showFooter)
                    <div class="print-footer" aria-hidden="true">
                        @stack('print-footer')
                    </div>
                @endif

                {{-- Dynamic Watermark text (if needed) --}}
                @if ($showWatermark)
                    <div class="watermark">{{ $watermarkText }}</div>
                @endif
            </div>
        </main>

        @if($siteFooter == true)
        <!-- Site Footer -->
        <footer class="site-footer">
            Artflow Studio — www.artflow.pk
        </footer>
        @endif
    </div>

    {{-- Scripts --}}
    @stack('scripts')
    
    <!-- Print Configuration & Initialization -->
    <script>
        // Configuration for print-final.js
        window.printConfig = {
            showTableTitles: {{ $showTableTitles ? 'true' : 'false' }},
            tableTitle: '{{ $tableTitle }}',
            showPageNumbers: {{ $showPageNumbers ? 'true' : 'false' }}
        };

        /**
         * Initializes and displays a watermark logo on print pages
         * - Uses provided watermark image or defaults to company logo
         * - 75% width of page, vertically centered
         * - Transparent (configurable opacity via CSS variable)
         * - Fixed positioning so it appears on every printed page
         * 
         * @function initializeWatermark
         * @returns {void}
         */
        function initializeWatermark() {
            const watermarkEl = document.getElementById('printWatermark');
            
            if (!watermarkEl) {
                console.warn('Watermark element not found');
                return;
            }

            // Use provided watermark image or company logo
            const watermarkSrc = '{{ $watermarkImage ?? $companyLogo }}';
            
            // Fallback to logo.svg if neither is provided
            if (!watermarkSrc || watermarkSrc === '') {
                watermarkEl.src = '{{ asset("assets/media/logos/logo.svg") }}';
            } else {
                watermarkEl.src = watermarkSrc;
            }
            
            watermarkEl.setAttribute('aria-label', 'Page watermark');
            
            // Set initial opacity from config
            const config = window.printConfig || {};
            const initialOpacity = (config.watermarkOpacity || 15) / 100;
            watermarkEl.style.opacity = initialOpacity;
            document.documentElement.style.setProperty('--watermark-opacity', initialOpacity);
            
            // Show/hide based on config
            if (config.showWatermark === false) {
                watermarkEl.style.display = 'none';
            }
            
            console.log('✓ Watermark initialized');
        }

        // Initialize watermark when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeWatermark);
        } else {
            initializeWatermark();
        }
    </script>

    <!-- Print Pagination & Utilities Script -->
    <script src="{{ asset('vendor/artflow-studio/snippets/print/af-print.js') }}" defer></script>
</body>

</html>