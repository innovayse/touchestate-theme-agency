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
