import axios from 'axios';
import 'htmx.org';
window.axios = axios;

import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
