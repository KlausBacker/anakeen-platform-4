import adminCenterGlobalParameters from './GlobalParameters/GlobalParameters.vue';
import adminCenterUserParameters from './UserParameters/UserParameters.vue';

export default {
    name: 'admin-center-parameters',

    components: { adminCenterGlobalParameters, adminCenterUserParameters },

    data() {
        return {
            globalParameters: true,
        };
    },

    methods: {
        switchParameters() {
            this.globalParameters = !this.globalParameters;
        },
    },
};
