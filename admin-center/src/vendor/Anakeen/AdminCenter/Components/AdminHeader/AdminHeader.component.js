import { mapMutations, mapGetters } from 'vuex';

export default {
    data() {
        return {
            adminTitle: 'Admin Center',
            appName: 'Application name',
        };
    },
    computed: {
    },
    methods: {
        onUserLoaded(event) {
            const userData = event.detail[0];
            this.SET_USER(userData);
        },
        ...mapMutations(['SET_USER'])
    },
};