import template from './meta-b2b-experience-list.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('meta-b2b-experience-list', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
        Mixin.getByName('acl'),
    ],

    data() {
        return {
            experiences: null,
            sortBy: 'position',
            sortDirection: 'ASC',
            isLoading: false,
            searchConfigEntity: 'meta_b2b_experience',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        experienceRepository() {
            return this.repositoryFactory.create('meta_b2b_experience');
        },

        experienceCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addAssociation('cmsPage');
            criteria.addAssociation('rule');
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            return criteria;
        },
    },

    methods: {
        async getList() {
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.experienceCriteria);

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;
                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.experienceRepository.search(criteria, Shopware.Context.api).then((result) => {
                this.total = result.total;
                this.experiences = result;
                this.selection = {};
                this.isLoading = false;

                return result;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        getColumns() {
            return [
                {
                    property: 'title',
                    label: this.$tc('meta-b2b-experience.list.titleColumn'),
                    allowResize: true,
                    routerLink: 'meta.b2b.experience.detail',
                    primary: true,
                },
                {
                    property: 'cmsPage.name',
                    label: this.$tc('meta-b2b-experience.list.cmsPageColumn'),
                    allowResize: true,
                },
                {
                    property: 'rule.name',
                    label: this.$tc('meta-b2b-experience.list.ruleColumn'),
                    allowResize: true,
                },
                {
                    property: 'priority',
                    label: this.$tc('meta-b2b-experience.list.priorityColumn'),
                    allowResize: true,
                    width: '90px',
                },
                {
                    property: 'position',
                    label: this.$tc('meta-b2b-experience.list.positionColumn'),
                    allowResize: true,
                    width: '90px',
                },
                {
                    property: 'active',
                    label: this.$tc('meta-b2b-experience.list.activeColumn'),
                    allowResize: true,
                    width: '80px',
                },
                {
                    property: 'visibleForCustomer',
                    label: this.$tc('meta-b2b-experience.list.customerAudienceColumn'),
                    allowResize: true,
                    width: '90px',
                },
                {
                    property: 'visibleForSalesRepresentative',
                    label: this.$tc('meta-b2b-experience.list.salesRepAudienceColumn'),
                    allowResize: true,
                    width: '90px',
                },
                {
                    property: 'showInPlatformMenu',
                    label: this.$tc('meta-b2b-experience.list.menuItemColumn'),
                    allowResize: true,
                    width: '90px',
                },
                {
                    property: 'includeInContentHub',
                    label: this.$tc('meta-b2b-experience.list.contentHubColumn'),
                    allowResize: true,
                    width: '90px',
                },
            ];
        },
    },
});
