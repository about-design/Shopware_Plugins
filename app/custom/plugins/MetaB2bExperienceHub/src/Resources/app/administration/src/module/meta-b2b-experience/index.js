import './page/meta-b2b-experience-list';
import './page/meta-b2b-experience-detail';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

const { Module } = Shopware;

Module.register('meta-b2b-experience', {
    type: 'plugin',
    name: 'meta-b2b-experience',
    title: 'META B2B Inhalte',
    description: 'CMS-Erlebniswelten für die B2B Platform zuweisen',
    color: '#189eff',
    icon: 'regular-content',

    routes: {
        list: {
            component: 'meta-b2b-experience-list',
            path: 'list',
            meta: {
                parentPath: 'sw.catalogue.index',
            },
        },
        detail: {
            component: 'meta-b2b-experience-detail',
            path: 'detail/:id',
            props: {
                default: (route) => ({ experienceId: route.params.id }),
            },
            meta: {
                parentPath: 'meta.b2b.experience.list',
            },
        },
        create: {
            component: 'meta-b2b-experience-detail',
            path: 'create',
            props: {
                default: () => ({ experienceId: null }),
            },
            meta: {
                parentPath: 'meta.b2b.experience.list',
            },
        },
    },

    navigation: [{
        id: 'meta-b2b-experience-list',
        label: 'B2B Inhalte',
        path: 'meta.b2b.experience.list',
        icon: 'regular-content',
        parent: 'sw-catalogue',
        position: 55,
    }],
});
