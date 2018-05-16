import { mapActions, mapGetters } from 'vuex';
import AdminSideMenu from './AdminSideMenu/AdminSideMenu.vue';
export default {
    components: {
        AdminSideMenu
    },
    mounted() {
        this.loadPluginsList();
    },
    computed: {
        ...mapGetters(['getPluginsList'])
    },
    methods: {
        ...mapActions(['loadPluginsList'])
    }
};