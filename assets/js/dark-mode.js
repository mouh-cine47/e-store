/* ═══════════════════════════════════════════════════════════════════
   E-STORE DARK MODE TOGGLE
   Manages light/dark mode with localStorage persistence
   ═══════════════════════════════════════════════════════════════════ */

class ThemeManager {
  constructor() {
    this.THEME_KEY = 'e-store-theme';
    this.DARK = 'dark';
    this.LIGHT = 'light';
    this.init();
  }

  /**
   * Initialize theme on page load
   */
  init() {
    // Get saved preference or detect system preference
    const savedTheme = this.getSavedTheme();
    const theme = savedTheme || this.getSystemTheme();
    
    // Apply theme
    this.setTheme(theme, false);

    // Listen for system theme changes
    if (window.matchMedia) {
      window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', (e) => {
          if (!this.getSavedTheme()) {
            this.setTheme(e.matches ? this.DARK : this.LIGHT);
          }
        });
    }
  }

  /**
   * Get saved theme from localStorage
   */
  getSavedTheme() {
    return localStorage.getItem(this.THEME_KEY);
  }

  /**
   * Get system theme preference
   */
  getSystemTheme() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return this.DARK;
    }
    return this.LIGHT;
  }

  /**
   * Set theme and save preference
   */
  setTheme(theme, savePreference = true) {
    if (theme !== this.DARK && theme !== this.LIGHT) {
      console.warn(`Invalid theme: ${theme}`);
      return;
    }

    // Update HTML attribute
    document.documentElement.setAttribute('data-theme', theme);

    // Also update body class for fallback
    document.body.classList.remove('dark-mode', 'light-mode');
    document.body.classList.add(theme === this.DARK ? 'dark-mode' : 'light-mode');

    // Save preference
    if (savePreference) {
      localStorage.setItem(this.THEME_KEY, theme);
    }

    // Dispatch custom event for other components
    window.dispatchEvent(
      new CustomEvent('themechange', {
        detail: { theme },
      })
    );
  }

  /**
   * Toggle between light and dark theme
   */
  toggle() {
    const current = document.documentElement.getAttribute('data-theme') || this.getSystemTheme();
    const newTheme = current === this.DARK ? this.LIGHT : this.DARK;
    this.setTheme(newTheme);
  }

  /**
   * Get current theme
   */
  getCurrentTheme() {
    return document.documentElement.getAttribute('data-theme') || this.getSystemTheme();
  }

  /**
   * Check if dark mode is active
   */
  isDarkMode() {
    return this.getCurrentTheme() === this.DARK;
  }

  /**
   * Reset to system preference
   */
  resetToSystem() {
    localStorage.removeItem(this.THEME_KEY);
    this.setTheme(this.getSystemTheme(), false);
  }
}

// Initialize theme manager when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
  });
} else {
  window.themeManager = new ThemeManager();
}

/**
 * Global function to toggle theme
 * Can be called from HTML attributes: onclick="toggleDarkMode()"
 */
function toggleDarkMode() {
  if (window.themeManager) {
    window.themeManager.toggle();
  }
}
