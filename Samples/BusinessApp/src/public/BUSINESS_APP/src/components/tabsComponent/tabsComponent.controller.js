// jscs:disable requirePaddingNewLinesBeforeLineComments
import contentTemplate from './documentsTabsContent.template.kd';
export default {

    props: {
        closable: {
            type: Boolean,
            default: true,
        },
    },

    watch: {
        closable(newValue, oldValue) {
            if (newValue !== oldValue) {
                this.tabstrip.tabGroup.children().each((i, t) => {
                    this.privateScope.configureCloseTab(t, this.newValue);
                });
            }
        },
    },

    created() {
        this.privateScope = {
            // Init the model and view kendo element
            initKendoComponents: () => {
                this.openedTabs = new this.$kendo.data.ObservableArray([]);
                this.listViewModel = this.$kendo.observable({
                    tabsList: this.openedTabs,
                });
                this.$kendo.bind(this.$refs.tabsPaginator, this.listViewModel);
                this.tabstripElement = this.$(this.$refs.tabstrip).kendoTabStrip({
                    animation: false,
                });
                this.privateScope.bindDataChange(this.openedTabs);
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((m) => {
                        switch (m.type) {
                            case 'childList':
                                const addedNodes = m.addedNodes;
                                if (addedNodes.length > 1 && addedNodes[0].classList.contains('k-tabstrip-prev')) {
                                    this.privateScope.replacePaginatorButtons();
                                }

                                break;
                            default:
                                break;
                        }

                    });
                });
                observer.observe(this.tabstripElement[0], { attributes: false, childList: true, characterData: false });
            },

            formatTabContentData: (data) => this.$kendo.template(contentTemplate)(data),

            // Enable or disable close tab button
            configureCloseTab: (tab, forceClose) => {
                const $tab = this.$(tab);
                const closable = forceClose !== undefined ? forceClose : this.closable;
                if (closable) {
                    $tab.append('<span data-type="remove" class="k-link"><span class="k-icon k-i-x"></span></span>');
                    $tab.on('click', "[data-type='remove']", (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        const item = this.$(e.target).closest('.k-item');
                        this.closeDocument(item.index());
                    });
                } else {
                    $tab.off('click', "[data-type='remove']");
                    $tab.find("span[data-type='remove']").remove();
                }
            },

            replacePaginatorButtons: () => {
                const prev = this.tabstripElement.find('.k-tabstrip-prev');
                const next = this.tabstripElement.find('.k-tabstrip-next');
                if (prev.length && next.length) {
                    const paginatorWidth = this.$(this.$refs.tabsPaginator).outerWidth(true);
                    const nextWidth = next.outerWidth(true);
                    const prevWidth = prev.outerWidth(true);
                    this.tabstrip.tabGroup.css('margin-right', `${paginatorWidth + (2 * prevWidth) + nextWidth}px`);
                    this.tabstrip.tabGroup.css('margin-left', 0);
                    next.css('right', `${paginatorWidth +  nextWidth}px`);
                    prev.css('right', `${paginatorWidth + nextWidth + prevWidth}px`);
                }
            },

            // Bind documents events to tabs system
            bindDocumentEvents: (tabContent, index) => {
                this.$(tabContent).find('a4-document').on('ready', (e) => {
                    this.openedTabs[index].set('icon', e.detail[1].icon);
                    this.openedTabs[index].set('title', e.detail[1].title);
                });
            },

            // Expose public methods (from method sections) in DOM props
            bindPublicMethods: () => {
                // Bind exposed methods to events
                const _this = this;
                Object.keys(this.$options.methods).forEach((methodName) => {
                    const method = {
                        [methodName]: (...args) => new Promise((resolve, reject) => {
                            try {
                                const ret = _this[methodName].call(_this, ...args);
                                resolve(ret);
                            } catch (e) {
                                reject(e);
                            }
                        }),
                    };
                    if (methodName !== '$emit') {
                        // Set a subtree prop for the object
                        this.$(this.$el).closest('a4-document-tabs').prop('publicMethods', (index, oldPropVal) => {
                            if (!oldPropVal) {
                                return method;
                            } else {
                                return Object.assign({}, oldPropVal, method);
                            }
                        });
                    }
                });
            },

            // Listen model changes and update view
            bindDataChange: (data) => {
                if (data.bind) {
                    data.bind('change', (e) => {
                        switch (e.action) {
                            // Add new document
                            case 'add':
                                this.tabstrip.append({
                                    text: `<i class="fa fa-spinner fa-pulse tab__document__icon"></i>
                                            <span class="tab__document__title">Chargement en cours...</span>`,
                                    encoded: false,
                                    content: this.privateScope.formatTabContentData(e.items[0]),
                                });
                                this.privateScope
                                    .configureCloseTab(this.tabstrip.items()[e.index]);
                                this.privateScope
                                    .bindDocumentEvents(this.tabstrip.contentElement(e.index), e.index);
                                break;
                            // Remove document
                            case 'remove':
                                if (e.items.length === 1) {
                                    this.tabstrip.remove(e.index);
                                } else if (e.items.length > 1) {
                                    this.tabstrip.remove('li');
                                }

                                break;
                            // Modify a tab
                            case 'itemchange':
                                const index = this.tabsArray.findIndex((d) => d.initid === e.items[0].initid);
                                const newValue = e.items[0][e.field];
                                const $indexedItem = this.$(this.tabstrip.items()[index]);
                                switch (e.field) {
                                    case 'title':
                                        $indexedItem.find('.tab__document__title').text(newValue);
                                        break;
                                    case 'icon':
                                        $indexedItem.find('.tab__document__icon')
                                            .replaceWith(`<img class="tab__document__icon" src="${newValue}"/>`);
                                        break;
                                }

                                break;
                        }
                    });
                }
            },
        };
    },

    mounted() {
        this.privateScope.initKendoComponents();
        this.privateScope.bindPublicMethods();
        const ready = () => {
            this.$emit('document-tabs-ready', this.$el);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },

    data() {
        return {
            openedTabs: [],
            tabstripElement: null,
            listViewModel: null,
        };
    },

    computed: {
        emptyState() {
            return !this.openedTabs.length;
        },

        tabsArray() {
            return this.openedTabs.toJSON();
        },

        tabstrip() {
            return this.tabstripElement.data('kendoTabStrip');
        },
    },

    methods: {
        addDocument(document) {
            const index = this.tabsArray.findIndex((d) => d.initid === document.initid);
            if (index < 0) {
                this.openedTabs.push(document);
                this.selectDocument(this.openedTabs.length - 1);
            } else {
                this.selectDocument(index);
            }
        },

        selectDocument(documentId) {
            let index = 0;
            if (typeof documentId === 'number') {
                index = documentId;
            } else if (typeof documentId === 'object') {
                index = this.tabsArray.toJSON().findIndex((d) => d.initid === document.initid);
                if (index < 0) {
                    index = 0;
                }
            }

            this.tabstrip.select(index);
        },

        closeDocument(documentId) {
            let index = -1;
            if (typeof documentId === 'number') {
                index = documentId;
            } else if (typeof documentId === 'object') {
                index = this.openedTabs.toJSON().findIndex((d) => d.initid === document.initid);
            }

            if (index >= 0) {
                const item = this.tabstrip.items()[index];
                this.openedTabs.splice(index, 1);
                if (this.openedTabs.length > 0 && this.$(item).hasClass('k-state-active')) {
                    this.selectDocument(0);
                }
            }
        },

        closeAllDocuments() {
            this.openedTabs.splice(0, this.openedTabs.length);
        },

        debugAddTab() {
            this.openedTabs.push({ initid: 1106 });
            this.selectDocument(this.openedTabs.length - 1);
        },
    },
};
