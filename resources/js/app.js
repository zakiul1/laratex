// resources/js/app.js
import './bootstrap';

// — Alpine.js setup first —
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

window.Alpine = Alpine;     // ← attach to window
Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.start();

// — React & Gutenberg editor next —
import React from 'react';
import { createRoot } from 'react-dom/client';
import BlockEditor from '@/components/BlockEditor';

// Gutenberg styles
import '@wordpress/block-editor/build-style/style.css';
import '@wordpress/block-library/build-style/style.css';

const editorEl = document.getElementById('block-editor');
if (editorEl) {
  const initial = editorEl.dataset.content;
  const hidden  = document.querySelector('input[name="content"]');

  const root = createRoot(editorEl);
  root.render(
    <BlockEditor
      initialContent={initial}
      onChange={(val) => (hidden.value = val)}
    />
  );
}
