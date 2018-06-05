
export default {
    data() {
        return {
            kendoModal: null,
            config: {
                width: "450px",
                buttonLayout: "normal",
                closable: false,
                modal: true,
                visible: false,
            }
        };
    },

    mounted() {
        const _this = this;
        this.$store.subscribeAction((action) => {
            if (action.type === 'showModal') {
                const payload = action.payload;
                if (payload.actions) {
                    this.config.actions = payload.actions;
                }
                this.kendoModal = this.$(this.$refs.kendoModal)
                    .kendoDialog(this.config)
                    .data('kendoDialog');
                if (payload.template) {
                    this.kendoModal.content(payload.template);
                }
                if (payload.title) {
                    this.kendoModal.title(payload.title);
                }
                this.kendoModal.open();
            }
        });
    }
}