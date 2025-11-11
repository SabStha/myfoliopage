import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './i18n'; // Import i18n service

window.Alpine = Alpine;

// Register Alpine.js component for dual language input BEFORE Alpine starts
// This ensures it's available globally, including for dynamically loaded content
Alpine.data('dualLanguageInput', () => ({
    activeLang: 'en',
    enValue: '',
    jaValue: '',
    translating: false,
    translateTimeout: null,
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
                if (data.translated) {
                    if (toLang === 'en') {
                        this.enValue = data.translated;
                    } else {
                        this.jaValue = data.translated;
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
                    this.translateText(value, 'en', 'ja');
                }
            }, 1000); // Debounce 1 second
        } else {
            this.jaValue = value;
            // Auto-translate to English
            clearTimeout(this.translateTimeout);
            this.translateTimeout = setTimeout(() => {
                if (value && value.trim().length > 0) {
                    this.translateText(value, 'ja', 'en');
                }
            }, 1000); // Debounce 1 second
        }
    }
}));

Alpine.start();

// React Component Loading - wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
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
