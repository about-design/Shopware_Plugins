import './pages/experience-index';

const { Module } = B2bPlatform;

Module.register('meta_b2b_experiences', {
    name: 'meta_b2b_experiences',
    routePrefixPath: 'experiences',
    routes: {
        index: {
            component: 'experience-index',
            path: null,
        },
        detail: {
            component: 'experience-index',
            path: ':assignmentId',
        },
    },
});
