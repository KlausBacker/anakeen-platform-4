export default {
    data() {
        return {
            collapseSplitter: false,
            collection: null,
        };
    },

    mounted() {
        document.addEventListener('DOMContentLoaded', (event) => {
            const store = document.getElementById('a4-store');
            if (store) {
                store.addEventListener('store-change', (event) => {
                    const storeData = event.detail && event.detail.length ? event.detail[0] : null;
                    this.onStoreChange(storeData);
                });
            }
        });
        this.initKendo();
    },

    methods: {
        initKendo() {
            this.$(this.$refs.splitter).kendoSplitter({
                panes: [
                    { collapsible: true, resizable: false, collapsedSize: '50px', size: '25%', scrollable: false },
                    { collapsible: false, resizable: false, size: '24px' },
                    { collapsible: false, resizable: false }
                ],

            });
        },

        onStoreChange(storeData) {
            if (storeData) {
                switch (storeData.type) {
                case 'SELECT_COLLECTION':
                    this.collection = storeData.data;
                break;
            }
            }
        },

        onCollapseSplitter(event) {
            this.collapseSplitter = !this.collapseSplitter;
            const splitter = this.$(this.$refs.splitter).data('kendoSplitter');
            this.$emit('store-save', { action: 'toggleCollections', data: false });
            splitter.toggle('#leftPane', !this.collapseSplitter);
        },
    },
};
