import mixin from '../componentBase';
export default {
    mixins: [mixin],
    data() {
        return {
            collapseSplitter: false,
            collection: null,
        };
    },

    mounted() {
        this.initKendo();
    },

    methods: {
        initKendo() {
            this.$(this.$refs.splitter).kendoSplitter({
                panes: [
                    { collapsible: true, resizable: false, collapsedSize: '50px', size: '25%', scrollable: false },
                    { collapsible: false, resizable: false, size: '25px' },
                    { collapsible: false, resizable: false, scrollable: false },
                ],

            });
        },

        onCollapseSplitter(event) {
            this.toggleSplitter(!this.collapseSplitter);
        },

        toggleSplitter(collapse) {
            if (collapse) {
                this.closeSplitter();
            } else {
                this.openSplitter();
            }
        },

        openSplitter() {
            this.collapseSplitter = false;
            const splitter = this.$(this.$refs.splitter).data('kendoSplitter');
            splitter.toggle('#leftPane', true);
            this.$emit('open');
        },

        closeSplitter() {
            this.collapseSplitter = true;
            const splitter = this.$(this.$refs.splitter).data('kendoSplitter');
            splitter.toggle('#leftPane', false);
            this.$emit('close');
        },

        setCollection(c) {
            this.collection = c;
        },

    },
};
