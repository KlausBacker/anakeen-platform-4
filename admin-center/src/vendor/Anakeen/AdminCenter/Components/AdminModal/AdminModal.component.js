import { Dialog } from '@progress/kendo-dialog-vue-wrapper';
export default {
    components: {
        Dialog,
    },
    data() {
        return {
            title: "",
            template : "",
            actions: [],
            visible: false,
            modal: true,
            closable: true,
            buttonLayout: 'normal',
            width: "450px",
        };
    },

    mounted() {
        this.$store.subscribeAction((action) => {
            if (action.type === 'showModal') {
                const payload = action.payload;
                if (payload.actions) {
                    this.actions = payload.actions;
                }
                if (payload.template) {
                    this.template = payload.template;
                }
                if (payload.title) {
                    this.title = payload.title;
                }
                this.$forceUpdate();
                this.$refs.kendoDialog.kendoWidget().open();
            }
        });
    },

    methods: {
        getAction(action) {
            if (typeof action !== 'function') {
                return () => true;
            }
        }
    }
};