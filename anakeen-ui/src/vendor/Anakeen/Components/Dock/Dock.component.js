import AnkMixin from '../AnkVueComponentMixin/AnkVueComponentMixin';

export default {
    name: 'ank-dock',

    mixins: [AnkMixin],

    props: {
        // Position of the dock, must be left, right, top or bottom
        position: {
            type: String,
            validator(value) {
                return ['left', 'right', 'top', 'bottom'].indexOf(value) !== -1;
            },

            default: 'left',
        },

        // Define if the dock is expandable or not
        expandable: {
            type: Boolean,
            default: true,
        },

        // Define the initial format of the dock (compact or expanded)
        expanded: {
            type: Boolean,
            default: false,
        },

        // Size of the dock when it is not expanded
        // TODO Define better default value (adapted to both horizontal and vertical placement)
        compactSize: {
            type: String,
            default: '3rem',
        },

        // Size of the dock when it is expanded
        // TODO Define better default value (adapted to both horizontal and vertical placement)
        largeSize: {
            type: String,
            default: '10rem',
        },
    },

    data() {
        return {
            tabs: [],
            headerComponents: [],
            footerComponents: [],
            expandedDock: false,
            selectedTab: '-1',
            size: '',
        };
    },

    methods: {
        // Add a tab and its content
        addTab(tab) {
            let eventName = 'beforeTabAdd';
            let options = {
                cancelable: true,
                detail: [tab],
            };
            let event;
            if (typeof window.CustomEvent === 'function') {
                event = new CustomEvent(eventName, options);
            } else {
                event = document.createEvent('CustomEvent');
                event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
            }

            this.$el.parentNode.dispatchEvent(event);

            if (!event.defaultPrevented) {
                this.tabs.push(tab);
                this.$emit('tabAdded', tab);
            } else {
                this.$emit('tabAddCanceled');
            }
        },

        // Add a header component to the dock (displayed befor the expansion button)
        addHeaderComponent(component) {
            let eventName = 'beforeHeaderComponentAdd';
            let options = {
                cancelable: true,
                detail: [component],
            };
            let event;
            if (typeof window.CustomEvent === 'function') {
                event = new CustomEvent(eventName, options);
            } else {
                event = document.createEvent('CustomEvent');
                event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
            }

            this.$el.parentNode.dispatchEvent(event);

            if (!event.defaultPrevented) {
                this.headerComponents.push(component);
                this.$emit('headerComponentAdded', component);
            } else {
                this.$emit('headerComponentAddCanceled');
            }
        },

        //
        addFooterComponent(component) {
            let eventName = 'beforeFooterComponentAdd';
            let options = {
                cancelable: true,
                detail: [component],
            };
            let event;
            if (typeof window.CustomEvent === 'function') {
                event = new CustomEvent(eventName, options);
            } else {
                event = document.createEvent('CustomEvent');
                event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
            }

            this.$el.parentNode.dispatchEvent(event);

            if (!event.defaultPrevented) {
                this.footerComponents.push(component);
                this.$emit('footerComponentAdded', component);
            } else {
                this.$emit('footerComponentAddCanceled');
            }
        },

        //
        addTabWithProperties(compact, expanded, content) {
            let newTabCompact = $(compact).attr('vue-slot', 'compact');
            let newTabExpanded = $(expanded).attr('vue-slot', 'expanded');
            let newTabContent = $(content).attr('vue-slot', 'content');

            let $newTab = $('<ank-dock-tab></ank-dock-tab>');
            $newTab.append(newTabCompact);
            $newTab.append(newTabExpanded);
            $newTab.append(newTabContent);

            $('#originalDom').append($newTab);
        },

        // Remove a tab with its Id
        removeTabWithId(id) {
            let positionToRemove = this.tabs.findIndex(element =>  element.id === id);
            this.removeTabWithPosition(positionToRemove);
        },

        // Remove a tab with its position in the dock
        removeTabWithPosition(position) {
            if (this.tabs[position].id === this.selectedTab) {
                this.selectedTab = -1;
            }

            this.tabs.splice(position, 1);
        },

        moveTab(actualPosition, newPosition) {
            // TODO
        },

        // Expand the dock to its large width
        expand() {
            if (this.expandable) {
                let eventName = 'beforeDockExpansion';
                let options = {
                    cancelable: true,
                };
                let event;
                if (typeof window.CustomEvent === 'function') {
                    event = new CustomEvent(eventName, options);
                } else {
                    event = document.createEvent('CustomEvent');
                    event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
                }

                this.$el.parentNode.dispatchEvent(event);

                if (!event.defaultPrevented) {
                    this.expandedDock = true;
                    this.size = this.largeSize;
                    this.$emit('dockExpanded');
                } else {
                    this.$emit('dockExpansionCanceled');
                }
            }
        },

        // Contract the dock to its compact width
        contract() {
            if (this.expandable) {
                let eventName = 'beforeDockContraction';
                let options = {
                    cancelable: true,
                };
                let event;
                if (typeof window.CustomEvent === 'function') {
                    event = new CustomEvent(eventName, options);
                } else {
                    event = document.createEvent('CustomEvent');
                    event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
                }

                this.$el.parentNode.dispatchEvent(event);

                if (!event.defaultPrevented) {
                    this.expandedDock = false;
                    this.size = this.compactSize;
                    this.$emit('dockContracted');
                } else {
                    this.$emit('dockContractionCanceled');
                }
            }
        },

        // Toggle expansion of the dock
        toggleExpansion() {
            if (this.expandable) {
                this.expandedDock ? this.contract() : this.expand();
            }
        },

        selectTab(tabId) {
            if (this.tabs.find(element => element.id === tabId) !== undefined) {
                this.selectedTab = tabId;
            }
        },

        selected(tab) {
            if (tab.id === this.selectedTab) {
                return 'selected';
            } else {
                return '';
            }
        },
    },

    computed: {
        compactSizeStyle() {
            if (this.position === 'left' || this.position === 'right') {
                return 'width: ' + this.compactSize;
            } else {
                return 'width: ' + this.compactSize;
            }
        },

        expandedSizeStyle() {
            if (this.position === 'left' || this.position === 'right') {
                return 'width: calc(' + this.largeSize + ' - ' + this.compactSize + ')';
            } else {
                return 'width: calc(' + this.largeSize + ' - ' + this.compactSize + ')';
            }
        },

        dockSizeStyle() {
            if (this.position === 'left' || this.position === 'right') {
                return 'width: ' + this.size;
            } else {
                return 'height: ' + this.compactSize;
            }
        },

        tabSizeStyle() {
            if (this.expandedDock) {
                return 'width: ' + this.largeSize;
            }

            return 'width: ' + this.compactSize;
        },

        buttonSizeStyle() {
            if ((this.position === 'left' || this.position === 'right') && (this.expandedDock)) {
                return 'width: ' + this.largeSize;
            } else {
                return 'width: ' + this.compactSize;
            }
        },

        headerSizeStyle() {
            if (this.expandedDock || this.position === 'top' || this.position === 'bottom') {
                return 'width: ' + this.largeSize;
            }

            return 'width: ' + this.compactSize;
        },

        footerSizeStyle() {
            if (!this.expandedDock || this.position === 'top' || this.position === 'bottom') {
                return 'width: ' + this.compactSize;
            }

            return 'width: ' + this.largeSize;
        },
    },

    created() {
        this.$dockEventBus.$on('tabLoaded', (tab) => {
            this.addTab(tab);
            if (tab.selected) {
                this.selectedTab = tab.id;
            }
        });

        this.$dockEventBus.$on('headerComponentLoaded', (component) => {
            this.addHeaderComponent(component);
        });

        this.$dockEventBus.$on('footerComponentLoaded', (component) => {
            this.addFooterComponent(component);
        });

        this.expandedDock = this.expanded;
        if (this.expanded) {
            this.size = this.largeSize;
        } else {
            this.size = this.compactSize;
        }
    },
};
