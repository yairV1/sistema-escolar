import './bootstrap';
import 'bootstrap';
import { initHttp } from './core/csrf';
import { initTheme } from './core/theme';

initHttp();
document.addEventListener('DOMContentLoaded', initTheme);
