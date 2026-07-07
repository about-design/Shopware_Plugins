import template from './template.html.twig';

const { Component, Platform } = B2bPlatform;

Component.register('meta-experience-cms-page', {
    template,

    inject: ['apiService'],

    props: {
        cmsPageId: {
            type: String,
            required: true,
        },
        isActive: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            isLoading: false,
            cmsPage: null,
            loadError: false,
        };
    },

    computed: {
        transformed() {
            return { template: this.cmsPage };
        },
    },

    watch: {
        cmsPageId: {
            immediate: true,
            handler() {
                this.fetchCmsPage();
            },
        },
        isActive(active) {
            if (active && !this.cmsPage && !this.isLoading) {
                this.fetchCmsPage();
            }
        },
    },

    methods: {
        fetchCmsPage() {
            if (!this.cmsPageId || !this.isActive) {
                return;
            }

            this.isLoading = true;
            this.cmsPage = null;
            this.loadError = false;

            this.apiService.customRequest({
                url: 'platform-cms/' + this.cmsPageId,
                method: 'get',
                baseURL: Platform.appContext.basePath,
                headers: {
                    'sw-access-key': Platform.apiContext.accessKey,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then((response) => {
                this.cmsPage = response.data;
                this.isLoading = false;

                if (window.PluginManager) {
                    window.PluginManager.initializePlugins();
                }
            }).catch(() => {
                this.isLoading = false;
                this.loadError = true;
            });
        },
    },
});
