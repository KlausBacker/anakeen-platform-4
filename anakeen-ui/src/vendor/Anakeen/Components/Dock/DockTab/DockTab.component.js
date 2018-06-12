export default {
    name: 'ank-dock-tab',

    props: {
        // Define if the tab should be selected at dock loading
        selectedTab: {
            type: Boolean,
            default: false,
        },

        // Define if the tab is a header component of the dock
        headerComponent: {
            type: Boolean,
            default: false,
        },

        // Define if the tab is a footer component of the dock
        footerComponent: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            id: '',
            compact: '',
            expanded: '',
            content: '',
        };
    },

    methods: {
        // Send the item to the parent dock with an event
        emitTab() {
            this.$dockEventBus.$emit('tabLoaded', {
                id: this.id,
                compact: this.compact,
                expanded: this.expanded,
                content: this.content,
                selected: this.selectedTab,
            });
        },

        // Send the item as a header component to the parent dock with an event
        emitHeaderComponent() {
            this.$dockEventBus.$emit('headerComponentLoaded', {
                id: this.id,
                compact: this.compact,
                expanded: this.expanded,
            });
        },

        //
        emitFooterComponent() {
            this.$dockEventBus.$emit('footerComponentLoaded', {
                id: this.id,
                compact: this.compact,
                expanded: this.expanded,
            });
        },
    },

    mounted() {
        this.id = this._uid;
        this.compact = this.$('#compactFragment').html();
        this.expanded = this.$('#expandedFragment').html();
        this.content = this.$('#contentFragment').html();

        this.$('#compactFragment').remove();
        this.$('#expandedFragment').remove();
        this.$('#contentFragment').remove();

        if (this.compact && this.expanded && this.content) {
            if (this.headerComponent) {
                this.emitHeaderComponent();
            } else if (this.footerComponent) {
                this.emitFooterComponent();
            } else {
                this.emitTab();
            }
        }
    },
};
