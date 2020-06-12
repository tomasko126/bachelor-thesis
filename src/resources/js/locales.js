import Vue from 'vue';
import VueI18n from "vue-i18n";
import czMessages from '../lang/cs/messages';
import engMessages from '../lang/en/messages';

Vue.use(VueI18n);

const messages = {
    ces: czMessages,
    eng: engMessages,
};

export default new VueI18n({
    locale: 'ces',
    messages,
});
