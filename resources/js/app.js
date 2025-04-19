// resources/js/app.js
import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus    from '@alpinejs/focus';
import Sortable from 'sortablejs';

window.Alpine   = Alpine;
window.Sortable = Sortable;

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.start();

