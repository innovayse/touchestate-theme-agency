// Apply the saved theme settings from local storage
document.querySelector("html").setAttribute("data-theme", localStorage.getItem('darkMode') === 'enabled' ? 'dark' : 'light');

document.addEventListener("DOMContentLoaded", function () {
    // Ensure themesettings is defined before inserting
    if (typeof themesettings !== "undefined" && themesettings) {
        document.body.insertAdjacentHTML("beforeend", themesettings);
    }

    // Get the HTML element and ID-based toggle buttons (legacy)
    const htmlElement = document.documentElement;
    const darkModeToggle = document.getElementById("dark-mode-toggle");
    const lightModeToggle = document.getElementById("light-mode-toggle");

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

    // Function to enable dark mode
    function enableDarkMode() {
        htmlElement.setAttribute("data-theme", "dark");
        try {
            localStorage.setItem("darkMode", "enabled");
        } catch (e) {
            console.warn("Failed to save to LocalStorage:", e);
        }
        if (darkModeToggle) darkModeToggle.classList.add("active");
        if (lightModeToggle) lightModeToggle.classList.remove("active");
        syncThemeButtons(true);
    }

    // Function to disable dark mode
    function disableDarkMode() {
        htmlElement.setAttribute("data-theme", "light");
        try {
            localStorage.setItem("darkMode", "disabled");
        } catch (e) {
            console.warn("Failed to save to LocalStorage:", e);
        }
        if (lightModeToggle) lightModeToggle.classList.add("active");
        if (darkModeToggle) darkModeToggle.classList.remove("active");
        syncThemeButtons(false);
    }

    // Apply the correct theme on page load
    if (darkMode === "enabled") {
        enableDarkMode();
    } else {
        disableDarkMode();
    }

    // ID-based buttons (legacy)
    if (darkModeToggle) darkModeToggle.addEventListener("click", enableDarkMode);
    if (lightModeToggle) lightModeToggle.addEventListener("click", disableDarkMode);

    // Class-based buttons in header dropdowns (event delegation)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.theme-dark-toggle')) {
            enableDarkMode();
        } else if (e.target.closest('.theme-light-toggle')) {
            disableDarkMode();
        } else if (e.target.closest('.theme-toggle-single')) {
            // Single toggle button — switch between dark/light
            var current = localStorage.getItem('darkMode');
            if (current === 'enabled') {
                disableDarkMode();
            } else {
                enableDarkMode();
            }
        }
    });
});
