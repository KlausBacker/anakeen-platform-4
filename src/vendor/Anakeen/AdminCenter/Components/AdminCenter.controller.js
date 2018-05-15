import AdminHeader from './AdminHeader/AdminHeader.vue';
import AdminContent from './AdminContent/AdminContent.vue';
export default {
    components: {
        AdminHeader,
        AdminContent
    },
    mounted() {
        this.$("#admin-center-notification").kendoNotification({
            position: {
                top: 40,
                right: 20
            }
        });
    }
};