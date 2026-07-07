import template from './template.html.twig';

const { Component } = B2bPlatform;

Component.register('experience-index', {
    template,

    inject: ['apiService'],

    data() {
        return {
            isLoading: false,
            experiences: [],
            activeExperienceId: null,
            loadError: false,
        };
    },

    computed: {
        isSingleMenuView() {
            return !!this.$route.params.assignmentId;
        },

        displayExperiences() {
            if (!this.isSingleMenuView) {
                return this.experiences;
            }

            return this.experiences.filter((item) => item.id === this.$route.params.assignmentId);
        },

        activeExperience() {
            if (!this.displayExperiences.length) {
                return null;
            }

            if (this.activeExperienceId) {
                return this.displayExperiences.find((item) => item.id === this.activeExperienceId) || this.displayExperiences[0];
            }

            return this.displayExperiences[0];
        },

        showTabs() {
            return !this.isSingleMenuView && this.displayExperiences.length > 1;
        },

        activeCmsPageId() {
            return this.getCmsPageId(this.activeExperience);
        },
    },

    watch: {
        '$route.params.assignmentId': {
            immediate: true,
            handler(assignmentId) {
                this.activeExperienceId = assignmentId || null;
                this.loadExperiences();
            },
        },
        activeExperienceId(id) {
            if (id) {
                this.trackExperienceView(id);
            }
        },
    },

    methods: {
        getPageTitle() {
            if (this.isSingleMenuView && this.activeExperience) {
                return this.getExperienceTitle(this.activeExperience);
            }

            return this.$trans('metaB2bExperienceHub.pageTitle');
        },

        getExperienceTitle(experience) {
            return experience?.translated?.title || experience?.title || '';
        },

        getCmsPageId(experience) {
            return experience?.cmsPageId || experience?.cmsPage?.id || null;
        },

        loadExperiences() {
            this.isLoading = true;
            this.loadError = false;

            this.apiService.get('meta-b2b-experiences').then((response) => {
                const payload = response.data || {};
                this.experiences = payload.elements || [];

                return this.ensureActiveExperienceLoaded();
            }).then(() => {
                this.isLoading = false;

                if (!this.activeExperienceId && this.displayExperiences.length === 1) {
                    this.activeExperienceId = this.displayExperiences[0].id;
                }

                if (this.$route.params.assignmentId) {
                    this.activeExperienceId = this.$route.params.assignmentId;
                } else if (this.activeExperienceId) {
                    this.trackExperienceView(this.activeExperienceId);
                }
            }).catch(() => {
                this.isLoading = false;
                this.loadError = true;
            });
        },

        ensureActiveExperienceLoaded() {
            const assignmentId = this.$route.params.assignmentId;

            if (!assignmentId) {
                return Promise.resolve();
            }

            const exists = this.experiences.some((item) => item.id === assignmentId);
            if (exists) {
                return Promise.resolve();
            }

            return this.apiService.get('meta-b2b-experiences/' + assignmentId).then((response) => {
                if (response.data) {
                    this.experiences = [response.data];
                }
            }).catch(() => {
                this.loadError = true;
            });
        },

        selectExperience(experience) {
            this.activeExperienceId = experience.id;
            this.$router.push('/experiences/' + experience.id).catch(() => {});
        },

        trackExperienceView(experienceId) {
            this.apiService.get('meta-b2b-experiences/' + experienceId).catch(() => {});
        },
    },
});
