/**
 * Instant Page Navigation Preloader
 * Prefetches HTML documents on link hover (after 65ms) and touchstart for near-instant page transitions.
 */

const prefetchedUrls = new Set();
let hoverTimeout = null;
let lastTouchTimestamp = 0;
const HOVER_DELAY_MS = 65;

function isEligibleAnchor(anchor) {
    if (!anchor || !anchor.href) return false;
    if (anchor.target && anchor.target !== '_self') return false;
    if (anchor.hasAttribute('download')) return false;
    if (anchor.dataset.noInstant !== undefined) return false;

    // Must be same origin (http / https)
    if (anchor.origin !== window.location.origin) return false;

    // Skip logout, form buttons, or delete actions
    const url = new URL(anchor.href);
    if (url.pathname.includes('/logout') || anchor.closest('form')) return false;
    if (url.protocol !== 'http:' && url.protocol !== 'https:') return false;

    // Skip current page and hash-only navigation
    if (url.pathname === window.location.pathname && url.search === window.location.search) {
        return false;
    }

    return true;
}

export function prefetch(url) {
    if (prefetchedUrls.has(url)) return;
    prefetchedUrls.add(url);

    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    link.as = 'document';
    document.head.appendChild(link);
}

function handleMouseOver(e) {
    if (performance.now() - lastTouchTimestamp < 1100) return;
    const anchor = e.target.closest('a');
    if (!isEligibleAnchor(anchor)) return;

    anchor.addEventListener('mouseout', handleMouseOut, { passive: true, once: true });

    hoverTimeout = setTimeout(() => {
        prefetch(anchor.href);
        hoverTimeout = null;
    }, HOVER_DELAY_MS);
}

function handleMouseOut() {
    if (hoverTimeout) {
        clearTimeout(hoverTimeout);
        hoverTimeout = null;
    }
}

function handleTouchStart(e) {
    lastTouchTimestamp = performance.now();
    const anchor = e.target.closest('a');
    if (isEligibleAnchor(anchor)) {
        prefetch(anchor.href);
    }
}

export function initInstantPreload() {
    if (typeof window === 'undefined' || !document.addEventListener) return;

    document.addEventListener('mouseover', handleMouseOver, { passive: true, capture: true });
    document.addEventListener('touchstart', handleTouchStart, { passive: true, capture: true });
}
