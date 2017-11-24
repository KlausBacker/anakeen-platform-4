import mixin from '../componentBase';
const thresholdWidth = 958;
export default {
    mixins: [mixin],
    data() {
        return {
            collapseSplitter: false,
            collection: null,
        };
    },

    created() {
        this.privateScope = {
            initKendo: () => {
                this.$(this.$refs.splitter).kendoSplitter({
                    panes: [
                        { collapsible: true, resizable: false, size: '25%', scrollable: false },
                        { collapsible: false, resizable: false, size: '25px' },
                        { collapsible: false, resizable: false, scrollable: false },
                    ],

                });
                this.$(window).resize(() => {
                    this.privateScope.resizeSplitter();
                });
                this.privateScope.resizeSplitter();
            },

            resizeSplitter: (e) => {
                const windowWidth = this.$(window).width();
                if (windowWidth < thresholdWidth) {
                    const splitter = this.$(this.$refs.splitter).data('kendoSplitter');
                    const collapseButtonWidth = this.$('.documentsList__collapsible__handler').width();
                    const newLeftPaneWidthPercent = 100 - ((collapseButtonWidth * 100) / windowWidth);
                    splitter.size('#leftPane', `${newLeftPaneWidthPercent}%`);
                } else {
                    const splitter = this.$(this.$refs.splitter).data('kendoSplitter');
                    splitter.size('#leftPane', '25%');
                }
            },
        };
    },

    mounted() {
        this.privateScope.initKendo();
    },

    methods: {
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
