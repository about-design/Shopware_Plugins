import template from './meta-b2b-experience-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('meta-b2b-experience-detail', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],

    props: {
        experienceId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            experience: null,
            isLoading: false,
            isSaveSuccessful: false,
            ruleConditions: null,
            selectedRuleId: null,
        };
    },

    computed: {
        experienceRepository() {
            return this.repositoryFactory.create('meta_b2b_experience');
        },

        ruleRepository() {
            return this.repositoryFactory.create('rule');
        },

        ...mapPropertyErrors('experience', ['title', 'cmsPageId']),
        cmsPageCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('type', 'page'));

            return criteria;
        },

        platformMenuParentCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('type', 'platform'));

            return criteria;
        },
    },

    watch: {
        experienceId: {
            immediate: true,
            handler() {
                this.loadEntity();
            },
        },
        selectedRuleId(value) {
            if (!this.experience) {
                return;
            }

            this.experience.ruleId = value;
            this.loadRuleConditions(value);
        },
    },

    methods: {
        loadEntity() {
            if (!this.experienceId) {
                this.experience = this.experienceRepository.create();
                this.experience.priority = 0;
                this.experience.position = 0;
                this.experience.active = true;
                this.experience.visibleForCustomer = true;
                this.experience.visibleForSalesRepresentative = false;
                this.experience.showInPlatformMenu = false;
                this.experience.includeInContentHub = true;
                this.experience.platformMenuParentId = null;
                this.experience.menuIconName = 'regular-content';
                this.selectedRuleId = null;
                this.ruleConditions = null;
                this.isLoading = false;
                return;
            }

            this.isLoading = true;

            const criteria = new Criteria(1, 1);
            criteria.addAssociation('cmsPage');
            criteria.addAssociation('rule');
            criteria.addAssociation('salesChannel');
            criteria.addAssociation('translations');

            this.experienceRepository.get(this.experienceId, Shopware.Context.api, criteria).then((entity) => {
                this.experience = entity;
                this.selectedRuleId = entity.ruleId;
                this.loadRuleConditions(entity.ruleId);
                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
                this.createNotificationError({
                    message: this.$tc('meta-b2b-experience.detail.errorLoad'),
                });
            });
        },

        loadRuleConditions(ruleId) {
            if (!ruleId) {
                this.ruleConditions = null;
                return;
            }

            const criteria = new Criteria(1, 1);
            criteria.addAssociation('conditions');

            this.ruleRepository.get(ruleId, Shopware.Context.api, criteria).then((rule) => {
                this.ruleConditions = rule.conditions || null;
            }).catch(() => {
                this.ruleConditions = null;
            });
        },

        onConditionsChanged(conditions) {
            this.ruleConditions = conditions;
            this.selectedRuleId = null;

            if (this.experience) {
                this.experience.ruleId = null;
            }
        },

        clearRule() {
            this.ruleConditions = null;
            this.selectedRuleId = null;

            if (this.experience) {
                this.experience.ruleId = null;
            }
        },

        async syncRule() {
            if (this.selectedRuleId) {
                return this.selectedRuleId;
            }

            if (!this.ruleConditions || !this.hasConditions(this.ruleConditions)) {
                return null;
            }

            const title = this.experience.title || this.experience.id || 'Assignment';
            const rule = this.ruleRepository.create();
            rule.name = `B2B Experience: ${title}`;
            rule.priority = 100;
            rule.conditions = this.ruleConditions;

            await this.ruleRepository.save(rule, Shopware.Context.api);

            return rule.id;
        },

        hasConditions(conditions) {
            if (!conditions) {
                return false;
            }

            if (Array.isArray(conditions)) {
                return conditions.length > 0;
            }

            return !!conditions.type;
        },

        async onSave() {
            if (!this.experience) {
                return;
            }

            this.isSaveSuccessful = false;

            try {
                this.experience.ruleId = await this.syncRule();
                await this.experienceRepository.save(this.experience, Shopware.Context.api);
                this.isSaveSuccessful = true;
                this.createNotificationSuccess({
                    message: this.$tc('meta-b2b-experience.detail.saveSuccess'),
                });

                if (!this.experienceId && this.experience.id) {
                    await this.$router.replace({
                        name: 'meta.b2b.experience.detail',
                        params: { id: this.experience.id },
                    });
                }

                this.loadEntity();
            } catch (error) {
                this.createNotificationError({
                    message: error?.response?.data?.errors?.[0]?.detail || error?.message || this.$tc('meta-b2b-experience.detail.errorLoad'),
                });
            }
        },

        onCancel() {
            this.$router.push({ name: 'meta.b2b.experience.list' });
        },
    },
});
