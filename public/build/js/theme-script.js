// Apply the saved theme settings from local storage
document.querySelector("html").setAttribute("data-theme", localStorage.getItem('darkMode') === 'enabled' ? 'dark' : 'light');

document.addEventListener("DOMContentLoaded", function () {
    const htmlElement = document.documentElement;

    let darkMode;
    try {
        darkMode = localStorage.getItem("darkMode");
    } catch (e) {
        console.warn("LocalStorage is not accessible:", e);
    }

    // Sync active class on all class-based theme buttons
    function syncThemeButtons(isDark) {
        document.querySelectorAll('.theme-dark-toggle').forEach(function(el) {
            el.classList.toggle('active', isDark);
        });
        document.querySelectorAll('.theme-light-toggle').forEach(function(el) {
            el.classList.toggle('active', !isDark);
        });
        // Update burger menu label text
        document.querySelectorAll('.theme-label-text').forEach(function(el) {
            var btn = el.closest('[data-light]');
            if (btn) {
                el.textContent = isDark ? btn.getAttribute('data-dark') : btn.getAttribute('data-light');
            }
        });
        // Update toggle button icons (topbar + mobile menu)
        document.querySelectorAll('.theme-toggle-single .material-icons-outlined').forEach(function(el) {
            if (el.textContent === 'wb_sunny' || el.textContent === 'dark_mode') {
                el.textContent = isDark ? 'dark_mode' : 'wb_sunny';
            }
        });
    }

    function enableDarkMode() {
        htmlElement.setAttribute("data-theme", "dark");
        try {
            localStorage.setItem("darkMode", "enabled");
        } catch (e) {
            console.warn("Failed to save to LocalStorage:", e);
        }
        syncThemeButtons(true);
    }

    function disableDarkMode() {
        htmlElement.setAttribute("data-theme", "light");
        try {
            localStorage.setItem("darkMode", "disabled");
        } catch (e) {
            console.warn("Failed to save to LocalStorage:", e);
        }
        syncThemeButtons(false);
    }

    // Apply the correct theme on page load
    if (darkMode === "enabled") {
        enableDarkMode();
    } else {
        disableDarkMode();
    }

    // Class-based buttons in header dropdowns + single topbar/mobile toggle (event delegation)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.theme-dark-toggle')) {
            enableDarkMode();
        } else if (e.target.closest('.theme-light-toggle')) {
            disableDarkMode();
        } else if (e.target.closest('.theme-toggle-single')) {
            localStorage.getItem('darkMode') === 'enabled' ? disableDarkMode() : enableDarkMode();
        }
    });
});

// ─── Shared numbered pagination renderer ─────────────────────────────────────
// renderPagination(containerEl, currentPage, totalPages, onGo) → draws ‹ 1 … n › and
// calls onGo(page) on click. Used by the property listing and the map panel/sheet.
window.renderPagination = function (container, current, totalPages, onGo) {
    if (!container) return;
    totalPages = parseInt(totalPages, 10) || 0;
    if (totalPages <= 1) { container.innerHTML = ''; return; }
    current = Math.min(Math.max(1, parseInt(current, 10) || 1), totalPages);

    var seq = [], i;
    if (totalPages <= 7) { for (i = 1; i <= totalPages; i++) seq.push(i); }
    else {
        seq.push(1);
        var start = Math.max(2, current - 1), end = Math.min(totalPages - 1, current + 1);
        if (start > 2) seq.push('…');
        for (i = start; i <= end; i++) seq.push(i);
        if (end < totalPages - 1) seq.push('…');
        seq.push(totalPages);
    }

    var html = '<button class="tp-pg-btn tp-pg-nav" data-pg="' + (current - 1) + '"' + (current <= 1 ? ' disabled' : '') + ' aria-label="Prev">‹</button>';
    seq.forEach(function (v) {
        if (v === '…') html += '<span class="tp-pg-ellipsis">…</span>';
        else html += '<button class="tp-pg-btn' + (v === current ? ' is-active' : '') + '" data-pg="' + v + '">' + v + '</button>';
    });
    html += '<button class="tp-pg-btn tp-pg-nav" data-pg="' + (current + 1) + '"' + (current >= totalPages ? ' disabled' : '') + ' aria-label="Next">›</button>';
    container.innerHTML = html;

    container.querySelectorAll('.tp-pg-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var n = parseInt(btn.getAttribute('data-pg'), 10);
            if (!isNaN(n) && n >= 1 && n <= totalPages && n !== current && !btn.disabled) onGo(n);
        });
    });
};
