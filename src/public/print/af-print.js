/* ==========================================================================
Print Pagination & Splittable Tables
Clean, modular, and well-documented rewrite.
- Keeps behavior: splits large tables into multiple .print-section fragments,
computes page boundaries and marks .force-new-page, warns about overflow,
sets dynamic header/footer CSS vars, and restores originals after print.
- Improvements:
* Single DPI reader (cached)
* Centralized page-metric reading (cached per run)
* Less cloning work: clone only what is necessary
* Clear constants and descriptive names
* Better error handling and minimal DOM thrashing
========================================================================== */

/* =========================
   Constants & Selectors
   ========================= */
const SEL_CONTENT = '.print-content';
const SEL_SECTION = '.print-section';
const SEL_SPLITTABLE = 'table.splittable-table';
const SEL_HEADER = '.print-header';
const SEL_FOOTER = '.print-footer';
const DATA_GENERATED = 'data-generated';
const BACKUP_MAP = new Map(); // backups of original sections for restore

// Tweakable fallbacks / safety values
const DEFAULT_PAGE_WIDTH_IN = 8.27;   // A4 width in inches
const DEFAULT_PAGE_HEIGHT_IN = 11.69; // A4 height in inches
const DEFAULT_PAGE_MARGIN_IN = 0;
const DEFAULT_FORCE_BOTTOM_PX = 24;
const MIN_USABLE_PX = 100;
const SAFETY_PAD_PX = 2; // tiny pad to avoid rounding overlap

// Get table title configuration from window.printConfig or use defaults
function getTableTitleConfig() {
    const config = window.printConfig || {};
    return {
        showTableTitles: config.showTableTitles !== false, // Default true
        tableTitle: config.tableTitle || 'Table'
    };
}

// Use the configuration for continuation fragments.
const DEFAULT_TABLE_FRAGMENT_TITLE = (n) => {
    const { tableTitle } = getTableTitleConfig();
    return `(${tableTitle} - ${n})`;
};

/* =========================
   Cached runtime state (per prepareForPrint run)
   ========================= */
let _cachedDPI = null;

/* =========================
   Utilities
   ========================= */

/**
 * getDPI() - measure device pixels per inch, cached for session
 */
function getDPI() {
    if (_cachedDPI) return _cachedDPI;
    const probe = document.createElement('div');
    probe.style.width = '1in';
    probe.style.position = 'absolute';
    probe.style.left = '-9999px';
    document.body.appendChild(probe);
    const dpi = probe.offsetWidth || 96;
    document.body.removeChild(probe);
    _cachedDPI = dpi;
    return dpi;
}

/**
 * parseCssLengthToPx(raw, dpi)
 * - Accepts values like '24px', '0.5in', '10mm' or plain number
 * - Returns pixel value (number)
 */
function parseCssLengthToPx(raw, dpi = getDPI()) {
    if (!raw && raw !== 0) return 0;
    const s = String(raw).trim();
    if (s.endsWith('px')) return parseFloat(s);
    if (s.endsWith('in')) return parseFloat(s) * dpi;
    if (s.endsWith('mm')) return parseFloat(s) * dpi / 25.4;
    // fallback: number or px-less string
    return parseFloat(s) || 0;
}

/**
 * readRootCssVars()
 * - Central place to read CSS variables used by print layout
 * - Returns object with dpi, page size px, margins (in inches), and forceBottomPx
 */
function readRootCssVars() {
    const dpi = getDPI();
    const rootStyle = getComputedStyle(document.documentElement);
    const pageWIn = parseFloat(rootStyle.getPropertyValue('--page-width')) || DEFAULT_PAGE_WIDTH_IN;
    const pageHIn = parseFloat(rootStyle.getPropertyValue('--page-height')) || DEFAULT_PAGE_HEIGHT_IN;
    const pageMarginIn = parseFloat(rootStyle.getPropertyValue('--page-margin-in')) || DEFAULT_PAGE_MARGIN_IN;
    const forceBottomRaw = rootStyle.getPropertyValue('--force-bottom-spacing') || `${DEFAULT_FORCE_BOTTOM_PX}px`;
    const forceBottomPx = parseCssLengthToPx(forceBottomRaw, dpi);

    return {
        dpi,
        pageWidthPx: Math.round(pageWIn * dpi),
        pageHeightPx: Math.round(pageHIn * dpi),
        pageMarginIn,
        forceBottomPx,
    };
}

/**
 * waitForFrames(n)
 * - Await for n animation frames to let layout settle
 */
async function waitForFrames(n = 1) {
    for (let i = 0; i < n; i++) {
        await new Promise(r => requestAnimationFrame(r));
    }
}

/* =========================
   Header/Footer measurement & CSS variable sync
   ========================= */

/**
 * setDynamicHeight(selector, cssVar)
 * - Measure element height and write it to :root CSS variable
 */
function setDynamicHeight(selector, cssVar) {
    const el = document.querySelector(selector);
    if (!el) return 0;
    const height = Math.round(el.getBoundingClientRect().height || 0);
    try {
        document.documentElement.style.setProperty(cssVar, `${height}px`);
    } catch (e) {
        // ignore in restricted environments
    }
    return height;
}

/**
 * applyHeaderFooterHeights()
 * - Updates --header-h and --footer-h based on rendered DOM
 */
function applyHeaderFooterHeights() {
    const headerPx = setDynamicHeight(SEL_HEADER, '--header-h');
    const footerPx = setDynamicHeight(SEL_FOOTER, '--footer-h');
    return { headerPx, footerPx };
}

/**
 * measureHeaderFooter(retries = 2)
 * - Attempts to measure heights reliably; retries a couple frames if heights are zero.
 * - Returns { headerPx, footerPx }
 */
async function measureHeaderFooter(retries = 2) {
    for (let i = 0; i <= retries; i++) {
        const headerEl = document.querySelector(SEL_HEADER);
        const footerEl = document.querySelector(SEL_FOOTER);
        const headerPx = headerEl ? Math.round(headerEl.getBoundingClientRect().height) : parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-h')) || 0;
        const footerPx = footerEl ? Math.round(footerEl.getBoundingClientRect().height) : parseInt(getComputedStyle(document.documentElement).getPropertyValue('--footer-h')) || 0;
        if ((headerPx > 0 || footerPx > 0) || i === retries) return { headerPx, footerPx };
        await waitForFrames(1);
    }
    return { headerPx: 0, footerPx: 0 };
}

/* =========================
   Splitting large tables into multiple .print-section fragments
   - Strategy:
   * Clone only the table and its THEAD/TBODY to measure rows (much lighter than cloning full content).
   * Determine usable page height in px and allow first fragment to use "remaining" space on current page.
   * Build generated sections (fragments) with thead + appropriate subset of tbody rows.
   * Replace original section with generated fragments and save backup for restore.
   ========================= */

async function splitAllSplittableTables() {
    const tables = Array.from(document.querySelectorAll(SEL_SPLITTABLE));
    if (tables.length === 0) return;

    const content = document.querySelector(SEL_CONTENT);
    if (!content) return;

    const rootMetrics = readRootCssVars();
    const { dpi } = rootMetrics;
    const { headerPx, footerPx } = await measureHeaderFooter();

    // content padding to align JS math with visible area
    const contentStyles = window.getComputedStyle(content);
    const contentPadTop = parseFloat(contentStyles.paddingTop) || 0;
    const contentPadBottom = parseFloat(contentStyles.paddingBottom) || 0;

    const pageHeightPx = rootMetrics.pageHeightPx - Math.round((rootMetrics.pageMarginIn * 2) * dpi);

    //const usableSinglePagePx = Math.max(MIN_USABLE_PX, Math.round(pageHeightPx - headerPx - footerPx - rootMetrics.forceBottomPx - contentPadTop - contentPadBottom - SAFETY_PAD_PX));

    //Avoiding Extra Space at the Bottom of the page.
    const usableSinglePagePx = Math.max(MIN_USABLE_PX, Math.round(pageHeightPx - headerPx - footerPx));


    // Build a lightweight clone of the entire content only when needed to compute "remaining" space for a section.
    // We'll create it on demand (per table) to avoid cloning for every table unnecessarily.
    for (const table of tables) {
        const section = table.closest(SEL_SECTION);
        if (!section) continue;

        // Backup original so restore is exact
        BACKUP_MAP.set(section, {
            orig: section.cloneNode(true),
            parent: section.parentNode,
            nextSibling: section.nextSibling
        });

        // Clone only the table (thead + tbody). This prevents heavy cloning of the whole page.
        const tableCloneWrap = document.createElement('div');
        tableCloneWrap.style.visibility = 'hidden';
        tableCloneWrap.style.position = 'absolute';
        tableCloneWrap.style.left = '-99999px';
        tableCloneWrap.style.top = '-99999px';
        tableCloneWrap.style.width = content.getBoundingClientRect().width + 'px';

        const ctable = table.cloneNode(true);
        tableCloneWrap.appendChild(ctable);
        document.body.appendChild(tableCloneWrap);

        const cthead = ctable.querySelector('thead');
        const ctbody = ctable.querySelector('tbody');
        if (!ctbody) {
            document.body.removeChild(tableCloneWrap);
            continue; // nothing to split if no tbody
        }

        const rows = Array.from(ctbody.querySelectorAll('tr'));
        const theadHeight = cthead ? Math.round(cthead.getBoundingClientRect().height) : 0;

        // Determine "remaining space" on the page where this section is currently placed.
        // For that we need an approximate page layout of sections — clone whole content once per table.
        const pageClone = document.createElement('div');
        pageClone.style.visibility = 'hidden';
        pageClone.style.position = 'absolute';
        pageClone.style.left = '-99999px';
        pageClone.style.top = '-99999px';
        pageClone.style.width = content.getBoundingClientRect().width + 'px';
        const wholeClone = content.cloneNode(true);
        pageClone.appendChild(wholeClone);
        document.body.appendChild(pageClone);

        const cloneSections = Array.from(wholeClone.querySelectorAll(SEL_SECTION));
        // Try to find the matching section by h3 text (best-effort); fallback to first match
        let secIndex = cloneSections.findIndex(cs => {
            const a = cs.querySelector('h3') ? cs.querySelector('h3').textContent.trim() : '';
            const b = section.querySelector('h3') ? section.querySelector('h3').textContent.trim() : '';
            return a && b && a === b;
        });
        if (secIndex === -1) secIndex = cloneSections.indexOf(cloneSections.find(x => x.innerHTML.includes(table.innerHTML))) || 0;

        // Greedy paginate the cloned sections to find remaining space before this section
        const pageSections = [];
        let curUsed = 0;
        let curPage = [];
        for (let i = 0; i < cloneSections.length; i++) {
            const cs = cloneSections[i];
            const style = window.getComputedStyle(cs);
            const mb = parseFloat(style.marginBottom) || 0;
            const heightNoMargin = Math.round(cs.getBoundingClientRect().height);
            if (heightNoMargin > usableSinglePagePx) {
                if (curPage.length) { pageSections.push(curPage); curPage = []; curUsed = 0; }
                pageSections.push([i]);
                continue;
            }
            if (curUsed + heightNoMargin > usableSinglePagePx) {
                pageSections.push(curPage);
                curPage = [i];
                curUsed = heightNoMargin + mb;
            } else {
                curPage.push(i);
                curUsed += heightNoMargin + mb;
            }
        }
        if (curPage.length) pageSections.push(curPage);

        // Find which page contains our section
        let containingPageIdx = 0;
        for (let p = 0; p < pageSections.length; p++) {
            if (pageSections[p].includes(secIndex)) { containingPageIdx = p; break; }
        }
        const sectionIndicesOnPage = pageSections[containingPageIdx] || [];

        // Calculate used height before the section on that page
        let usedBeforeThisSection = 0;
        for (const si of sectionIndicesOnPage) {
            if (si >= secIndex) break;
            const hh = Math.round(cloneSections[si].getBoundingClientRect().height + (parseFloat(window.getComputedStyle(cloneSections[si]).marginBottom) || 0));
            usedBeforeThisSection += hh;
        }
        const remainingOnPage = Math.max(0, usableSinglePagePx - usedBeforeThisSection - SAFETY_PAD_PX);

        // Now compute splits for rows. First fragment may use remainingOnPage; subsequent use full page usable.
        const splits = [];
        let start = 0;
        while (start < rows.length) {
            let used = theadHeight; // keep THEAD on every fragment
            let end = start;
            const limit = (start === 0 && remainingOnPage > 0 && remainingOnPage < usableSinglePagePx) ? remainingOnPage : usableSinglePagePx;
            while (end < rows.length) {
                const h = Math.round(rows[end].getBoundingClientRect().height);
                if (used + h > limit) break;
                used += h;
                end++;
            }
            if (end === start) end = start + 1; // always at least one row per fragment
            splits.push([start, end]);
            start = end;
        }

        // Clean up clones used for measurement
        document.body.removeChild(pageClone);
        document.body.removeChild(tableCloneWrap);

        // Build fragments and replace original section
        const fragment = document.createDocumentFragment();
        splits.forEach((range, fragIndex) => {
            const [s, e] = range;
            const newSection = document.createElement('section');
            newSection.className = 'print-section';
            newSection.setAttribute(DATA_GENERATED, 'true');
            // Copy title/meta for the first fragment; for others create a continuation title
            if (fragIndex === 0) {
                const title = section.querySelector('h3');
                if (title) newSection.appendChild(title.cloneNode(true));
                const meta = section.querySelector('.meta');
                if (meta) newSection.appendChild(meta.cloneNode(true));
            } else {
                // Add continuation title only if showTableTitles is enabled
                const { showTableTitles } = getTableTitleConfig();
                if (showTableTitles) {
                    const title = document.createElement('h3');
                    title.textContent = DEFAULT_TABLE_FRAGMENT_TITLE(fragIndex + 1);
                    newSection.appendChild(title);
                }
            }
            // Build table for this fragment
            const newTable = document.createElement('table');
            newTable.style.width = '100%';
            newTable.style.borderCollapse = 'collapse';
            newTable.style.fontSize = 'var(--table-font-size)';
            // clone and append thead if present
            if (ctable.querySelector('thead')) {
                newTable.appendChild(ctable.querySelector('thead').cloneNode(true));
            }
            const newTbody = document.createElement('tbody');
            for (let i = s; i < e; i++) {
                const tr = rows[i].cloneNode(true);
                // adjust numbering in first td if present
                const firstTd = tr.querySelector('td');
                if (firstTd) firstTd.textContent = (i + 1).toString();
                newTbody.appendChild(tr);
            }
            newTable.appendChild(newTbody);
            newSection.appendChild(newTable);
            fragment.appendChild(newSection);
        });

        // Replace original section with fragments
        section.parentNode.insertBefore(fragment, section);
        section.remove();
    } // end tables loop
}

/* =========================
   Restore function
   - Restores backed-up original sections and removes generated fragments
   ========================= */
function restoreSplittableTables() {
    const container = document.querySelector(SEL_CONTENT);
    if (!container) return;

    // Remove generated fragments first
    const generated = Array.from(container.querySelectorAll(`[${DATA_GENERATED}="true"]`));
    generated.forEach(el => el.remove());

    // Restore originals in their saved positions
    for (const [origSection, info] of BACKUP_MAP.entries()) {
        try {
            const parent = info.parent || container;
            if (info.nextSibling && parent.contains(info.nextSibling)) {
                parent.insertBefore(info.orig, info.nextSibling);
            } else {
                parent.insertBefore(info.orig, parent.firstChild);
            }
        } catch (e) {
            // best-effort fallback
            container.insertBefore(info.orig, container.firstChild);
        }
    }
    BACKUP_MAP.clear();
}

/* =========================
   Pagination: compute pages and apply markers (.force-new-page and overflow warnings)
   - Uses a single lightweight clone of content for measurement
   ========================= */
async function simulatePaginationAndApply() {
    const content = document.querySelector(SEL_CONTENT);
    const realSections = Array.from(document.querySelectorAll(SEL_SECTION));
    if (!content || realSections.length === 0) return;

    // Clear previous markers
    realSections.forEach(s => s.classList.remove('force-new-page', 'content-overflow-warning'));

    const metrics = readRootCssVars();
    const { headerPx, footerPx } = await measureHeaderFooter();
    const pageHeightPx = metrics.pageHeightPx - Math.round((metrics.pageMarginIn * 2) * metrics.dpi);

    //const usableHeight = Math.max(MIN_USABLE_PX, Math.round(pageHeightPx - headerPx - footerPx - metrics.forceBottomPx - SAFETY_PAD_PX));

    // Compute usable height correctly by subtracting header/footer and any forced bottom spacing.
    // This ensures dynamic header/footer sizes are respected in pagination (prevents content
    // from being placed behind the footer when header/footer grows in print preview).
    const usableHeight = Math.max(
        MIN_USABLE_PX,
        Math.round(pageHeightPx - headerPx - footerPx)
    );
    // Clone content once to compute section heights in isolation
    const cloneWrap = document.createElement('div');
    cloneWrap.style.visibility = 'hidden';
    cloneWrap.style.position = 'absolute';
    cloneWrap.style.left = '-99999px';
    cloneWrap.style.top = '-99999px';
    cloneWrap.style.width = content.getBoundingClientRect().width + 'px';
    const cloneContent = content.cloneNode(true);
    cloneContent.querySelectorAll('.force-new-page').forEach(el => el.classList.remove('force-new-page'));
    cloneWrap.appendChild(cloneContent);
    document.body.appendChild(cloneWrap);

    const cloneSections = Array.from(cloneContent.querySelectorAll(SEL_SECTION));

    // Greedy pagination on clone to get page groups
    const pages = [];
    let currentPage = [];
    let usedOnPage = 0;
    cloneSections.forEach((csec, idx) => {
        const style = window.getComputedStyle(csec);
        const marginBottom = parseFloat(style.marginBottom) || 0;
        const sectionHeight = Math.round(csec.getBoundingClientRect().height); // excludes margin
        if (sectionHeight > usableHeight) {
            if (currentPage.length) pages.push(currentPage);
            pages.push([idx]); // oversized section occupies its own page (overflow)
            currentPage = [];
            usedOnPage = 0;
            return;
        }
        if (usedOnPage + sectionHeight > usableHeight) {
            pages.push(currentPage);
            currentPage = [idx];
            usedOnPage = sectionHeight + marginBottom;
        } else {
            currentPage.push(idx);
            usedOnPage += sectionHeight + marginBottom;
        }
    });
    if (currentPage.length) pages.push(currentPage);

    // Cleanup clone
    document.body.removeChild(cloneWrap);


    // Apply .force-new-page on real DOM for first section of each page (except page 0)
    pages.forEach((pg, pageNo) => {
        if (pageNo === 0) return;
        const firstIdx = pg[0];
        const target = realSections[firstIdx];
        if (target) target.classList.add('force-new-page');
    });

    // Mark overflow sections in real DOM
    realSections.forEach(rsec => {
        const rstyle = window.getComputedStyle(rsec);
        const rmb = parseFloat(rstyle.marginBottom) || 0;
        const rh = Math.round(rsec.getBoundingClientRect().height + rmb);
        if (rh > usableHeight) rsec.classList.add('content-overflow-warning');
    });
}

/* =========================
   Page numbers
   - Fills per-section page number display if present.
   - Finds element by:
     1) looking for `.page-no` within the section
     2) falling back to a .print-footer inside section or parent
     3) last resort: element with id "#page-no" (keeps backward compatibility)
   ========================= */
function addPageNumbersToFooters() {
    const config = window.printConfig || {};
    const showPageNumbers = config.showPageNumbers !== false; // Default true

    if (!showPageNumbers) {
        // Clear existing page numbers if disabled
        const pageNoElements = document.querySelectorAll('.page-no, #page-no');
        pageNoElements.forEach(el => el.textContent = '');
        return;
    }

    const sections = Array.from(document.querySelectorAll(SEL_SECTION));
    let pageNo = 1;
    sections.forEach((section, index) => {
        if (index === 0 || section.classList.contains('force-new-page')) {
            // prefer class-based selectors and avoid duplicate IDs in HTML
            let footerEl = section.querySelector('.page-no') || section.querySelector(SEL_FOOTER + ' .page-no');
            if (!footerEl && section.parentElement) {
                footerEl = section.parentElement.querySelector('.page-no');
            }
            // Backwards-compatible: fall back to id-based #page-no if developer used it
            if (!footerEl) footerEl = section.querySelector('#page-no') || section.parentElement.querySelector('#page-no');

            if (footerEl) footerEl.textContent = `Page ${pageNo}`;
            pageNo++;
        }
    });
}

/* =========================
   Orchestrator: prepareForPrint()
   - Runs the ordered steps to prepare the DOM for printing:
     1. Update header/footer heights (CSS vars)
     2. Wait a frame or two for layout to settle
     3. Split splittable tables (heavy)
     4. Compute pagination (force-new-page & overflow warnings)
     5. Add page numbers
   - All steps are resilient (catch errors but don't block printing)
   ========================= */
async function prepareForPrint() {
    try {
        // refresh CSS var values for header/footer
        applyHeaderFooterHeights();
        // let layout settle so measurements are accurate
        await waitForFrames(2);
        // Re-apply after a small settle — some browsers only apply print stylesheet during beforeprint
        // calling again ensures header/footer heights reflect print-mode CSS.
        applyHeaderFooterHeights();
        await waitForFrames(1);
        // do heavy lifting
        await splitAllSplittableTables();
        await simulatePaginationAndApply();
        addPageNumbersToFooters();
    } catch (err) {
        // best-effort: don't prevent printing on failure
        // console.warn('prepareForPrint error:', err);
    }
}

/* =========================
   Event wiring
   ========================= */
// When print button is used, run prepare then open print preview.
// This prevents double-work if browser also triggers beforeprint.
window.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-print');
    if (!btn) return;
    btn.addEventListener('click', async (e) => {
        e && e.preventDefault();
        await prepareForPrint();
        window.print();
    });
});

// Also prepare when browser triggers beforeprint (covers user->File->Print native flows)
window.addEventListener('beforeprint', async () => {
    await prepareForPrint();
});

// After print restore original tables/sections.
window.addEventListener('afterprint', () => {
    try {
        restoreSplittableTables();
    } catch (err) {
        // swallow errors; restore is best-effort
        // console.warn('restoreSplittableTables failed', err);
    }
});

/* =========================
   Exports (for debugging / optional manual invocation)
   - Comment out these if you want zero globals.
   ========================= */
window.prepareForPrint = prepareForPrint;
window.simulatePaginationAndApply = simulatePaginationAndApply;
window.splitAllSplittableTables = splitAllSplittableTables;
window.restoreSplittableTables = restoreSplittableTables;
window.applyHeaderFooterHeights = applyHeaderFooterHeights;
