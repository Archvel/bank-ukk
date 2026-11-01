

import Alpine from 'alpinejs';
import { initInstantPreload } from './instantpage';

window.Alpine = Alpine;

Alpine.start();

// Initialize fast navigation preloading
initInstantPreload();

