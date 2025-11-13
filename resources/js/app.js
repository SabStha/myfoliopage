import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './i18n'; // Import i18n service

window.Alpine = Alpine;

// Global registry for dual-language-input components
window.dualLanguageInputRegistry = {
    components: new Set(),
    hasPendingTranslations() {
        for (const component of this.components) {
            if (component.translating || component.translateTimeout) {
                return true;
            }
        }
        return false;
    },
    getPendingCount() {
        let count = 0;
        for (const component of this.components) {
            if (component.translating || component.translateTimeout) {
                count++;
            }
        }
        return count;
    },
    waitForTranslations() {
        return new Promise((resolve) => {
            const checkInterval = setInterval(() => {
                if (!this.hasPendingTranslations()) {
                    clearInterval(checkInterval);
                    resolve();
                }
            }, 100); // Check every 100ms
            
            // Timeout after 10 seconds to prevent infinite waiting
            setTimeout(() => {
                clearInterval(checkInterval);
                resolve();
            }, 10000);
        });
    }
};

// Register Alpine.js component for dual language input BEFORE Alpine starts
// This ensures it's available globally, including for dynamically loaded content
Alpine.data('dualLanguageInput', () => ({
    activeLang: 'en',
    enValue: '',
    jaValue: '',
    translating: false,
    translateTimeout: null,
    init() {
        // Register this component in the global registry
        window.dualLanguageInputRegistry.components.add(this);
        
        // Cleanup on component destroy
        this.$el.addEventListener('alpine:destroyed', () => {
            window.dualLanguageInputRegistry.components.delete(this);
        });
    },
    async translateText(text, fromLang, toLang) {
        if (!text || text.trim().length === 0) return;
        
        this.translating = true;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch('/api/translate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    text: text,
                    from: fromLang,
                    to: toLang
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.translated && data.translated.trim() !== '') {
                    // Check if the translation is an error message
                    const translated = data.translated.trim();
                    if (translated.includes('QUERY LENGTH LIMIT') || 
                        translated.includes('ERROR') || 
                        translated.includes('EXCEEDED')) {
                        console.warn('Translation API returned an error message, skipping...');
                        // Don't update the value if it's an error message
                        return;
                    }
                    
                    if (toLang === 'en') {
                        this.enValue = translated;
                    } else {
                        this.jaValue = translated;
                    }
                }
            }
        } catch (error) {
            console.error('Translation error:', error);
        } finally {
            this.translating = false;
        }
    },
    handleInput(value, currentLang) {
        if (currentLang === 'en') {
            this.enValue = value;
            // Auto-translate to Japanese
            clearTimeout(this.translateTimeout);
            this.translateTimeout = setTimeout(() => {
                if (value && value.trim().length > 0) {
                    this.translateText(value, 'en', 'ja').finally(() => {
                        this.translateTimeout = null;
                    });
                } else {
                    this.translateTimeout = null;
                }
            }, 1000); // Debounce 1 second
        } else {
            this.jaValue = value;
            // Auto-translate to English
            clearTimeout(this.translateTimeout);
            this.translateTimeout = setTimeout(() => {
                if (value && value.trim().length > 0) {
                    this.translateText(value, 'ja', 'en').finally(() => {
                        this.translateTimeout = null;
                    });
                } else {
                    this.translateTimeout = null;
                }
            }, 1000); // Debounce 1 second
        }
    }
}));

Alpine.start();

// Global form submission interceptor to ensure translations complete
document.addEventListener('DOMContentLoaded', () => {
    // Intercept all form submissions
    document.addEventListener('submit', async function(e) {
        const form = e.target;
        
        // Skip if form has data-no-translation-check attribute
        if (form.hasAttribute('data-no-translation-check')) {
            return;
        }
        
        // Check if form contains dual-language-input components
        const hasDualLanguageInputs = form.querySelector('[x-data*="dualLanguageInput"]') !== null;
        
        if (hasDualLanguageInputs && window.dualLanguageInputRegistry.hasPendingTranslations()) {
            // Prevent form submission
            e.preventDefault();
            e.stopPropagation();
            
            // Show user-friendly message
            const pendingCount = window.dualLanguageInputRegistry.getPendingCount();
            let message;
            if (window.translations && window.translations.common) {
                if (pendingCount === 1) {
                    message = window.translations.common.wait_translation_single || 'Please wait for translation to complete before submitting...';
                } else {
                    message = (window.translations.common.wait_translation_multiple || 'Please wait for :count translations to complete before submitting...').replace(':count', pendingCount);
                }
            } else {
                message = pendingCount === 1 
                    ? 'Please wait for translation to complete before submitting...'
                    : `Please wait for ${pendingCount} translations to complete before submitting...`;
            }
            
            // Create or update notification
            let notification = document.getElementById('translation-wait-notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.id = 'translation-wait-notification';
                notification.className = 'fixed top-4 right-4 z-50 bg-yellow-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 max-w-md';
                notification.innerHTML = `
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="flex-1"></span>
                    <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">×</button>
                `;
                document.body.appendChild(notification);
            }
            
            const messageSpan = notification.querySelector('span');
            if (messageSpan) {
                messageSpan.textContent = message;
            }
            
            // Wait for translations to complete
            await window.dualLanguageInputRegistry.waitForTranslations();
            
            // Remove notification
            notification.remove();
            
            // Re-submit the form
            form.submit();
        }
    });
    
    // Also intercept programmatic form submissions (fetch/AJAX)
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        // Check if this is a form submission
        const [url, options] = args;
        if (options && options.method && ['POST', 'PUT', 'PATCH'].includes(options.method.toUpperCase())) {
            // Check if translations are pending
            if (window.dualLanguageInputRegistry.hasPendingTranslations()) {
                // Show notification
                let notification = document.getElementById('translation-wait-notification');
                if (!notification) {
                    notification = document.createElement('div');
                    notification.id = 'translation-wait-notification';
                    notification.className = 'fixed top-4 right-4 z-50 bg-yellow-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 max-w-md';
                    notification.innerHTML = `
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>${window.translations?.common?.wait_translation_single || 'Please wait for translation to complete...'}</span>
                        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">×</button>
                    `;
                    document.body.appendChild(notification);
                }
                
                // Wait for translations
                await window.dualLanguageInputRegistry.waitForTranslations();
                
                // Remove notification
                notification.remove();
            }
        }
        
        return originalFetch.apply(this, args);
    };
    
    // React Component Loading - wait for DOM to be ready
  console.log('DOMContentLoaded event fired, checking for my-works-root...');
  const rootElement = document.getElementById('my-works-root');
  console.log('Found my-works-root element:', rootElement);
  
  if (rootElement) {
    // Dynamically import and mount React component
    console.log('Importing mountMyWorks.js...');
    import('./mountMyWorks.js').then(() => {
      console.log('mountMyWorks.js imported successfully');
    }).catch((err) => {
      console.error('Could not load MyWorksSection component:', err);
      // Fallback placeholder
      rootElement.innerHTML = '<div class="min-h-screen bg-gray-900 text-white py-20 px-4 flex items-center justify-center"><p class="text-center">My Works Section<br><small class="text-gray-400">React component loading error: ' + err.message + '</small></p></div>';
    });
  } else {
    console.warn('my-works-root element not found on page');
  }

  // Mount translation button
  const translationButtonRoot = document.getElementById('translation-button-root');
  if (translationButtonRoot) {
    import('./mountTranslationButton.js').then(() => {
      console.log('Translation button mounted successfully');
    }).catch((err) => {
      console.error('Could not load translation button:', err);
    });
  }
});

// Dashboard chart init
if (document.getElementById('roomsChart')) {
    const ctx = document.getElementById('roomsChart').getContext('2d');
    const chartData = window.dashboardChartData || { labels: [], data: [] };
    /* eslint-disable no-new */
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Activity by Month',
                data: chartData.data,
                backgroundColor: '#f59e0b',
                borderRadius: 6,
                maxBarThickness: 24,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#6b7280' } },
                y: { grid: { color: '#e5e7eb' }, ticks: { color: '#6b7280' }, beginAtZero: true }
            }
        }
    });
}

// Overall doughnut
if (document.getElementById('overallChart')) {
    const ctx = document.getElementById('overallChart').getContext('2d');
    const d = window.dashboardOverallData || { labels: [], data: [] };
    /* eslint-disable no-new */
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: ['#f59e0b', '#fde68a', '#fbbf24'],
                borderColor: '#ffffff',
                borderWidth: 2,
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom', labels: { color: '#374151' } } },
        }
    });
}
