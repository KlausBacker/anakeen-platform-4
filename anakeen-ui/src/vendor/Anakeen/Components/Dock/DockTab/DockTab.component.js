export default {
    name: "ank-dock-tab",

    props: {
        selectedTab: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            id: '',
            compact: '',
            expanded: '',
            content: '',
        }
    },

    methods: {
        // If compact, expanded and content loaded, send the item to the parent dock with an event
        emitTab() {
            this.$dockEventBus.$emit('tabLoaded', {
                id: this.id,
                compact: this.compact,
                expanded: this.expanded,
                content: this.content,
                selected: this.selectedTab
            });
        }
    },

    mounted() {
        this.id = this._uid;
        this.compact = this.$("#compactFragment").html();
        this.expanded = this.$("#expandedFragment").html();
        this.content = this.$("#contentFragment").html();

        this.$("#compactFragment").remove();
        this.$("#expandedFragment").remove();
        this.$("#contentFragment").remove();

        if (this.compact && this.expanded && this.content) {
            this.emitTab();
        }
    }
}
