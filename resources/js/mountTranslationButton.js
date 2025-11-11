import { createRoot } from 'react-dom/client';
import React from 'react';
import LanguageSwitcher from './Components/LanguageSwitcher.jsx';

// Mount language switcher when DOM is ready
const mountTranslationButton = () => {
  const translationButtonRoot = document.getElementById('translation-button-root');

  if (translationButtonRoot) {
    try {
      const root = createRoot(translationButtonRoot);
      root.render(React.createElement(LanguageSwitcher));
    } catch (error) {
      console.error('Error mounting LanguageSwitcher:', error);
    }
  }
};

// Mount immediately if DOM is ready, otherwise wait
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountTranslationButton);
} else {
  mountTranslationButton();
}

export default mountTranslationButton;

