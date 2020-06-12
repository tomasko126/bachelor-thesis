import moment from 'moment';

import locales from "../locales";
import router from '../router';

import {ToastProgrammatic as Toast} from 'buefy';

import app from "../app";

// Format given date to the DD.MM.YYYY format
export const formatDate = (date) => {
    if (!date) {
        return '-';
    }

    return moment(date).format('DD.MM.YYYY');
};

// Format given datetime to the DD.MM.YYYY HH:mm:ss format
export const formatDateTime = (date) => {
    if (!date) {
        return '-';
    }

    return moment(date).format('DD.MM.YYYY HH:mm:ss');
};

// Check, whether user can access given page
export const hasRightTo = async (routerData, apiData) => {
    try {
        if (app) {
            app.$Progress.start();
        }

        const response = await axios.get('/api/users/hasRightTo', {
            params: {
                ability: apiData.ability,
                model: apiData.model,
                model_id: apiData.model_id,
                deleted: apiData.deleted
            }
        });

        const canAccess = response.data;

        if (!canAccess) {
            if (app) {
                app.$Progress.fail();
            }

            router.push({ name: 'unauthorized' });
            return;
        }

        routerData.next();

        if (app) {
            app.$Progress.finish();
        }
    } catch (e) {
        Toast.open({ message: locales.t('common.unable_to_authorize'), type: 'is-danger' });
        if (app) {
            app.$Progress.fail();
        }
        throw e;
    }
};
