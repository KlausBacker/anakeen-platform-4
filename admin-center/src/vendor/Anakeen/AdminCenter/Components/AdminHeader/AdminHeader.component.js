import { mapMutations, mapGetters } from 'vuex';
import BreadCrumb from '../AdminBreadcrumb/AdminBreadcrumb.vue';

export default {
    components: {
        'admin-breadcrumb': BreadCrumb,
    },
    data() {
        return {
            adminTitle: 'Admin Center',
            currentRoutePath: [],
        };
    },
    computed: {
        ...mapGetters({
            appName: 'getAppName',
            rootPlugin: 'getRootPlugin',
            getPluginsFromPath: 'getPluginsFromPath',
        }),
    },
    mounted() {
        this.$router.afterEach((to) => {
            const plugins = this.getPluginsFromPath(to.path);
            this.currentRoutePath = to.path.split('/').filter(r => !!r).map((r, index) => {
                return {
                    label: plugins[index].title,
                    id: r,
                };
            });
        });
    },
    methods: {
        onUserLoaded(event) {
            const userData = event.detail[0];
            this.SET_USER(userData);
        },
        ...mapMutations(['SET_USER']),
        onItemClick(item) {
            this.$router.replace(item.path);
        }
    },
};