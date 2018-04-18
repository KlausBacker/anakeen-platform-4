import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            adminTitle: 'Admin Center',
            appName: 'Application name',
        };
    },
    mounted() {
        this.$store.dispatch('loadUser');
    },
    computed: {
        getUserInfo() {
            return this.$store.getters.getUserInfo;
        },
        userFullName() {
            const firstname = this.getUserInfo.firstname || '';
            const lastname = this.getUserInfo.lastname ||'';
            return `${firstname} ${lastname}`;
        }
    },
    methods: {
        logout() {
            this.$store.dispatch('logout')
                .then(() => {
                    location.reload();
                })
                .catch((err) => {
                    console.error(err);
                });
        }
    }
};