 

<!-- ENHANCED Print Controls -->
        <!-- Collapsible Print Controls -->
        <div id="printControlsToggle" class="print-controls-toggle no-print"
            style="position: fixed; top: 18px; right: 18px; z-index: 1100; cursor: pointer;">
            <span style="font-size: 24px; color: #007bff;"><i class="fas fa-ellipsis-v"></i></span>
        </div>
        <div class="print-controls no-print" id="printControlsPanel" style="display: block;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <h5 style="margin-bottom: 0;">🖨️ Print Configuration</h5>
                <span id="closePrintControls"
                    style="font-size: 22px; color: #888; cursor: pointer; margin-left: 10px;"><i
                        class="fas fa-times"></i></span>
            </div>
            <hr style="margin: 10px 0;">

            <div class="control-group">
                <label>📄 Paper Size & Orientation:</label>
                <select id="paperSize" onchange="updatePaperSize()">
                    @foreach ($paperOptions as $val => $label)
                        <option value="{{ $val }}" {{ $paperSize === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <select id="orientation" onchange="updatePaperSize()">
                    @foreach ($orientationOptions as $val => $label)
                        <option value="{{ $val }}" {{ $orientation === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="control-group">
                <label>📏 Content Scale:</label>
                <select id="contentScale" onchange="updateContentScale()">
                    @foreach ($scaleOptions as $val => $label)
                        <option value="{{ $val }}" {{ $contentScale === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="control-group">
                <label>🔍 Zoom Level:</label>
                @foreach ($zoomOptions as $val => $label)
                    <button onclick="setZoom({{ $val }})" id="zoom-{{ $val }}" class="{{ $val === 100 ? 'active' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>


            <div class="control-group">
                <div class="control-row">
                    <label>💧 Show Watermark:</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="showWatermark" onchange="toggleWatermark()" {{ $showWatermark ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="control-row" style="margin-top:8px;">
                    <label>Watermark Opacity: <span id="watermarkOpacityValue">8%</span></label>
                    <input type="range" id="watermarkOpacity" min="0" max="100" value="8" 
                        oninput="updateWatermarkOpacity(this.value)" 
                        style="flex:1;margin-left:8px;">
                </div>
            </div>

            <div class="control-group">
                <div class="control-row">
                    <label>📊 Show Table Titles:</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="showTableTitles" onchange="toggleTableTitles()" {{ $showTableTitles ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="control-row" style="margin-top:8px;">
                    <label>Table Title Text:</label>
                    <input type="text" id="tableTitleInput" value="{{ $tableTitle }}" 
                        onchange="updateTableTitle()" placeholder="Table" 
                        style="padding:4px 8px;border:1px solid #ddd;border-radius:4px;flex:1;">
                </div>
            </div>

            <div class="control-group">
                <div class="control-row">
                    <label>🔢 Show Page Numbers:</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="showPageNumbers" onchange="togglePageNumbers()" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>


            <div class="control-group">
                <label>Table Controls: 🔤 Font Size & ↕️ Row Height</label>
                <div style="display:flex;align-items:center;gap:8px;">
                    <!-- secondary font-size select (kept as separate control to avoid duplicate id conflicts) -->
                    <select id="tableFontSizeSelectSecondary" class="table-font-size-select"
                        onchange="setTableFontSize(this.value)">
                        <option value="12">Small (12px)</option>
                        <option value="14">Normal (14px)</option>
                        <option value="16">Large (16px)</option>
                        <option value="18">XL (18px)</option>
                        <option value="20">XXL (20px)</option>
                    </select>

                    <select id="tableRowPaddingSelect" onchange="setTableRowPadding(this.value)">
                        <option value="2">Ultra Compact (2px)</option>
                        <option value="4">Compact (4px)</option>
                        <option value="6">Tight (6px)</option>
                        <option value="8">Normal (8px)</option>
                        <option value="12">Spacious (12px)</option>
                        <option value="16">Extra Spacious (16px)</option>
                    </select>
                    <button class="btn btn-sm" onclick="savePrintConfig()">Save</button>
                </div>
                <small class="text-muted">Use the selects to adjust table font size and vertical padding to fit more
                    rows when printing/PDF.</small>
            </div>

            <script>
                (function () {
                    const root = document.documentElement;
                    const wrapperSelector = '#printWrapper .print-content table';

                    function applySize(px) {
                        root.style.setProperty('--table-font-size', px + 'px');
                        document.querySelectorAll(wrapperSelector).forEach(function (t) {
                            t.style.fontSize = px + 'px';
                        });
                    }

                    function applyRowPadding(px) {
                        root.style.setProperty('--table-row-vertical-padding', px + 'px');
                        const cells = document.querySelectorAll(wrapperSelector + ' th, ' + wrapperSelector + ' td');
                        cells.forEach(function (c) {
                            c.style.paddingTop = px + 'px';
                            c.style.paddingBottom = px + 'px';
                        });
                    }

                    window.setTableFontSize = function (val) {
                        const px = parseInt(val, 10) || 14;
                        applySize(px);

                        if (window.printConfig) {
                            window.printConfig.defaults = window.printConfig.defaults || {};
                            window.printConfig.defaults.tableFontSize = px;
                        }

                        // Sync all related selects and input
                        const selects = Array.from(document.querySelectorAll('.table-font-size-select'));
                        const primary = document.getElementById('tableFontSizeSelect');
                        if (primary) {
                            selects.push(primary);
                        }
                        selects.forEach(function (s) {
                            s.value = String(px);
                        });
                        const input = document.getElementById('tableFontSizeInput');
                        if (input) {
                            input.value = px;
                        }
                    };

                    window.setTableRowPadding = function (val) {
                        const px = parseInt(val, 10);
                        const finalPx = isNaN(px) ? 8 : px;
                        applyRowPadding(finalPx);

                        if (window.printConfig) {
                            window.printConfig.defaults = window.printConfig.defaults || {};
                            window.printConfig.defaults.tableRowPadding = finalPx;
                        }

                        const rowSelect = document.getElementById('tableRowPaddingSelect');
                        if (rowSelect) {
                            rowSelect.value = String(finalPx);
                        }
                    };

                    // Helper used by the "Apply" button near the main font-size input
                    window.applyCustomTableFont = function () {
                        const input = document.getElementById('tableFontSizeInput');
                        if (input) {
                            setTableFontSize(input.value);
                        }
                    };

                    // Init: read existing CSS vars or defaults and set selects
                    const initialFont = (getComputedStyle(root).getPropertyValue('--table-font-size') || '14px').trim();
                    const initialFontPx = parseInt(initialFont, 10) || 14;

                    const fontSelects = Array.from(document.querySelectorAll('.table-font-size-select'));
                    const primarySelect = document.getElementById('tableFontSizeSelect');
                    if (primarySelect) {
                        fontSelects.push(primarySelect);
                    }
                    fontSelects.forEach(function (s) {
                        // Ensure the select has a matching option, otherwise set the value directly
                        if (Array.from(s.options).some(o => parseInt(o.value, 10) === initialFontPx)) {
                            s.value = String(initialFontPx);
                        } else {
                            s.value = String(initialFontPx);
                        }
                    });
                    applySize(initialFontPx);

                    const initialRow = (getComputedStyle(root).getPropertyValue('--table-row-vertical-padding') || '8px').trim();
                    const initialRowPx = parseInt(initialRow, 10) || 8;
                    const rowSelect = document.getElementById('tableRowPaddingSelect');
                    if (rowSelect) {
                        // Ensure select reflects the computed value
                        if (Array.from(rowSelect.options).some(o => parseInt(o.value, 10) === initialRowPx)) {
                            rowSelect.value = String(initialRowPx);
                        } else {
                            rowSelect.value = String(initialRowPx);
                        }
                    }
                    applyRowPadding(initialRowPx);
                })();

                // Local storage key
                const STORAGE_KEY = 'al_emaaan_print_config_v1';

                // Collect values from the control panel and save to localStorage
                window.savePrintConfig = function () {
                    try {
                        const getVal = (id) => {
                            const el = document.getElementById(id);
                            return el ? el.value : null;
                        };

                        const zoomActiveBtn = document.querySelector('#printControlsPanel button.active[id^="zoom-"]');
                        let zoom = null;
                        if (zoomActiveBtn && zoomActiveBtn.id) {
                            const match = zoomActiveBtn.id.match(/zoom-(\d+)/);
                            if (match) {
                                zoom = parseInt(match[1], 10);
                            }
                        } else {
                            // fallback: check any zoom button with active class in whole document
                            const anyActive = document.querySelector('button.active[id^="zoom-"]');
                            if (anyActive && anyActive.id) {
                                const match = anyActive.id.match(/zoom-(\d+)/);
                                if (match) {
                                    zoom = parseInt(match[1], 10);
                                }
                            }
                        }

                        const obj = {
                            paperSize: getVal('paperSize') || null,
                            orientation: getVal('orientation') || null,
                            contentScale: getVal('contentScale') || null,
                            zoom: zoom,
                            showWatermark: !!(document.getElementById('showWatermark') && document.getElementById('showWatermark').checked),
                            tableFontSize: getVal('tableFontSizeSelectSecondary') ? parseInt(getVal('tableFontSizeSelectSecondary'), 10) : null,
                            tableRowPadding: getVal('tableRowPaddingSelect') ? parseInt(getVal('tableRowPaddingSelect'), 10) : null,
                            pageNumberPosition: getVal('pageNumberPosition') || null,
                            showPageBreaks: !!(document.getElementById('showPageBreaks') && document.getElementById('showPageBreaks').checked),
                        };

                        localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
                        // update global printConfig if present
                        if (window.printConfig) {
                            window.printConfig.defaults = window.printConfig.defaults || {};
                            Object.assign(window.printConfig.defaults, obj);
                        }
                        // feedback (small, transient)
                        const btn = event && event.target ? event.target : null;
                        if (btn) {
                            const original = btn.innerText;
                            btn.innerText = 'Saved';
                            setTimeout(() => { btn.innerText = original; }, 1000);
                        }
                    } catch (e) {
                        console.error('Failed to save print settings', e);
                    }
                };

                // Load saved config (if any) and apply to controls
                (function loadSavedPrintConfigOnDomReady() {
                    document.addEventListener('DOMContentLoaded', function () {
                        try {
                            const raw = localStorage.getItem(STORAGE_KEY);
                            if (!raw) {
                                return;
                            }
                            const saved = JSON.parse(raw);

                            const applyIfExists = function (id, value, triggerFnName) {
                                if (!value) {
                                    return;
                                }
                                const el = document.getElementById(id);
                                if (el) {
                                    if (el.type === 'checkbox') {
                                        el.checked = !!value;
                                    } else {
                                        el.value = String(value);
                                    }
                                    // trigger associated function if provided
                                    if (triggerFnName && typeof window[triggerFnName] === 'function') {
                                        window[triggerFnName](value);
                                    } else {
                                        // dispatch change to call inline onchange handlers
                                        const evt = new Event('change', { bubbles: true });
                                        el.dispatchEvent(evt);
                                    }
                                } else if (triggerFnName && typeof window[triggerFnName] === 'function') {
                                    window[triggerFnName](value);
                                }
                            };

                            applyIfExists('paperSize', saved.paperSize, 'updatePaperSize');
                            applyIfExists('orientation', saved.orientation, 'updatePaperSize');
                            applyIfExists('contentScale', saved.contentScale, 'updateContentScale');

                            if (saved.zoom && typeof window.setZoom === 'function') {
                                window.setZoom(saved.zoom);
                            } else if (saved.zoom) {
                                // try to mark the corresponding button active
                                const zBtn = document.getElementById('zoom-' + String(saved.zoom));
                                if (zBtn) {
                                    zBtn.click();
                                }
                            }

                            applyIfExists('showWatermark', saved.showWatermark, 'toggleWatermark');

                            if (saved.tableFontSize) {
                                if (typeof window.setTableFontSize === 'function') {
                                    window.setTableFontSize(saved.tableFontSize);
                                } else {
                                    const sel = document.getElementById('tableFontSizeSelectSecondary');
                                    if (sel) {
                                        sel.value = String(saved.tableFontSize);
                                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }

                            if (saved.tableRowPadding) {
                                if (typeof window.setTableRowPadding === 'function') {
                                    window.setTableRowPadding(saved.tableRowPadding);
                                } else {
                                    const sel = document.getElementById('tableRowPaddingSelect');
                                    if (sel) {
                                        sel.value = String(saved.tableRowPadding);
                                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }

                            if (saved.pageNumberPosition) {
                                applyIfExists('pageNumberPosition', saved.pageNumberPosition);
                            }

                            // Update global printConfig defaults so other scripts can use it
                            if (window.printConfig) {
                                window.printConfig.defaults = window.printConfig.defaults || {};
                                Object.assign(window.printConfig.defaults, saved);
                            }
                        } catch (e) {
                            console.error('Failed to load saved print settings', e);
                        }
                    });
                })();
            </script>


            <div class="control-group">
                <button id="btn-print" class="btn-print">🖨️ Print Document</button>
            </div>
        </div>

        
        <script>
            // Collapsible print controls logic
            document.addEventListener('DOMContentLoaded', function () {
                var controlsToggle = document.getElementById('printControlsToggle');
                var controlsPanel = document.getElementById('printControlsPanel');
                var closeBtn = document.getElementById('closePrintControls');

                // Show controls panel by default
                if (controlsPanel) {
                    controlsPanel.style.display = 'block';
                }
                if (controlsToggle) {
                    controlsToggle.style.display = 'none';
                }

                if (controlsToggle && controlsPanel && closeBtn) {
                    controlsToggle.addEventListener('click', function () {
                        controlsPanel.style.display = 'block';
                        controlsToggle.style.display = 'none';
                    });

                    closeBtn.addEventListener('click', function () {
                        controlsPanel.style.display = 'none';
                        controlsToggle.style.display = 'block';
                    });
                }
            });
        </script>

        <script>
            // New configuration toggle functions
            function toggleTableTitles() {
                const checkbox = document.getElementById('showTableTitles');
                if (window.printConfig) {
                    window.printConfig.showTableTitles = checkbox.checked;
                }
                savePrintConfig();
            }

            function updateTableTitle() {
                const input = document.getElementById('tableTitleInput');
                if (window.printConfig) {
                    window.printConfig.tableTitle = input.value || 'Table';
                }
                savePrintConfig();
            }

            function togglePageNumbers() {
                const checkbox = document.getElementById('showPageNumbers');
                if (window.printConfig) {
                    window.printConfig.showPageNumbers = checkbox.checked;
                }
                savePrintConfig();
            }

            function toggleWatermark() {
                const checkbox = document.getElementById('showWatermark');
                const watermarkEl = document.getElementById('printWatermark');
                
                if (watermarkEl) {
                    if (checkbox.checked) {
                        watermarkEl.style.display = '';
                    } else {
                        watermarkEl.style.display = 'none';
                    }
                }
                
                if (window.printConfig) {
                    window.printConfig.showWatermark = checkbox.checked;
                }
                savePrintConfig();
            }

            function updateWatermarkOpacity(value) {
                const watermarkEl = document.getElementById('printWatermark');
                const valueDisplay = document.getElementById('watermarkOpacityValue');
                
                if (valueDisplay) {
                    valueDisplay.textContent = value + '%';
                }
                
                if (watermarkEl) {
                    // On screen: use the value / 100
                    // On print: CSS will handle it via @media print
                    watermarkEl.style.opacity = value / 100;
                }
                
                // Store in root CSS variable for print media query
                document.documentElement.style.setProperty('--watermark-opacity', value / 100);
                
                if (window.printConfig) {
                    window.printConfig.watermarkOpacity = value;
                }
                savePrintConfig();
            }
        </script>

        <!-- Enhanced Page Info -->
        <div class="page-info no-print">
            📄 {{ $paperSize }} {{ ucfirst($orientation) }} | {{ $pageWidthPx }}×{{ $pageHeightPx }}px
        </div>

        <!-- Scale Indicator -->
        <div class="scale-indicator no-print">
            📏 Scale: {{ strtoupper($contentScale) }} ({{ intval($scaleFactor * 100) }}%)
        </div>

