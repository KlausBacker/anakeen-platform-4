import { mapActions, mapGetters } from 'vuex';
import AdminSideMenu from './AdminSideMenu/AdminSideMenu.vue';
export default {
    components: {
        AdminSideMenu
    },
    mounted() {
        this.loadModulesList();
    },
    computed: {
        ...mapGetters(['getModulesList'])
    },
    methods: {
        ...mapActions(['loadModulesList'])
    }
};