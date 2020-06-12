/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./components', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Vue from 'vue';
import i18n from './locales';
import Buefy from 'buefy';
import VueScrollactive from 'vue-scrollactive';
import VueCookies from 'vue-cookies'
import router from "./router";
import VueProgressBar from 'vue-progressbar'

Vue.use(Buefy, {
    defaultIconPack: 'fas',
});

Vue.use(VueScrollactive);
Vue.use(VueCookies);

Vue.use(VueProgressBar, {
    color: 'rgb(72,182,30)',
    failedColor: 'red',
    height: '2px'
});

export default new Vue({
    el: '#app',
    i18n,
    router
});
