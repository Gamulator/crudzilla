import Vue from 'vue';

require('./bootstrap');

require('./tools/progressbar');
require('./tools/customEvents');
require('./tools/swal');
require('./tools/vform');

import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

// import SuiVue from 'semantic-ui-vue';
// import 'semantic-ui-css/semantic.min.css';
// Vue.use(SuiVue);

import App from './components/App';
import router from './router';

const app = new Vue({
    el: '#app',
    router,
    render: h => h(App)
});
