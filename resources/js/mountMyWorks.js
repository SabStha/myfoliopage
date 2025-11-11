import { createRoot } from 'react-dom/client';
import React from 'react';
import MyProgressSection from './Components/MyProgressSection.jsx';

// Mount React component immediately (DOM is already ready when this is called)
const rootElement = document.getElementById('my-works-root');

if (rootElement) {
  try {
    const root = createRoot(rootElement);
    root.render(React.createElement(MyProgressSection));
  } catch (error) {
    console.error('Error mounting MyProgressSection:', error);
    rootElement.innerHTML = '<div class="py-20 px-4 text-center text-neutral-500">Error loading component</div>';
  }
}

