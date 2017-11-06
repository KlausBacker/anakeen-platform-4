// jscs:disable requirePaddingNewLinesBeforeLineComments
import contentTemplate from './documentTabsContent.template.kd';
import headerTemplate from './documentTabsHeader.template.kd';
import abstractAnakeenComponent from '../componentBase';

export default {

    props: {
        closable: {
            type: Boolean,
            default: true,
        },

        'empty-img': {
            type: String,
            default: 'CORE/Images/anakeen-logo.svg',
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
                this.tabstripElement = this.$(this.$refs.tabstrip).kendoTabStrip({
                    animation: false,
                });
                this.tabsListElement = this.$(this.$refs.tabsList).kendoDropDownList({
                    animation: false,
                    dataSource: this.openedTabs,
                    template: this.$kendo.template(headerTemplate),
                    valueTemplate: this.$kendo.template('<span class="k-icon k-i-menu"></span>'),
                    autoWidth: true,
                    select: this.privateScope.onClickTabList,
                });
                this.privateScope.bindDataChange(this.openedTabs);
                this.$(window).resize(() => {
                    this.privateScope.resizeComponents();
                });
                this.$(this.$refs.tabsWrapper).bind('resize', () => {
                    console.log("I'm resizing");
                });

                this.privateScope.resizeComponents();
            },

            onClickTabList: (e) => {
                this.selectDocument(e.sender.dataItem(e.item));
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

            resizeComponents: () => {
                this.tabstrip.resize();
                this.privateScope.computeTabstripMargin();
            },

            // Compute the tabstrip left and right margin depending on the displayed buttons (paginator, slot)
            computeTabstripMargin: () => {
                const paginatorWidth = this.$(this.$refs.tabsPaginator).outerWidth(true);
                let marginLeft = 0;
                let marginRight = paginatorWidth || 0;
                let tabMargin = this.tabstrip.tabGroup.children().first().css('margin-right') || 0;
                const leftSlot = this.$(this.$refs.slotContent);
                if (leftSlot) {
                    marginLeft = leftSlot.outerWidth(true);
                    if (marginLeft) {
                        this.tabstrip.tabGroup.css('margin-left', `calc(${marginLeft}px  + ${tabMargin})`);
                    } else {
                        this.tabstrip.tabGroup.css('margin-left', 0);
                    }
                }

                const prev = this.tabstripElement.find('.k-tabstrip-prev');
                const next = this.tabstripElement.find('.k-tabstrip-next');
                if (prev.length && next.length) {
                    const nextWidth = next.outerWidth(true);
                    const prevWidth = prev.outerWidth(true);
                    next.css('right', `${paginatorWidth +  nextWidth}px`);
                    prev.css('right', `${paginatorWidth + nextWidth + prevWidth}px`);
                    marginRight = marginRight + (2 * prevWidth) + nextWidth;
                }

                this.tabstrip.tabGroup.css('margin-right', `calc(${marginRight}px + ${tabMargin})`);
            },

            // Bind documents events to tabs system
            bindDocumentEvents: (tabContent, index) => {
                const tab = this.openedTabs[index];
                const documentComponent = this.$(tabContent).find('a4-document');
                documentComponent.on('ready', (e) => {
                    tab.set('title', '');
                    tab.set('icon', '');
                    tab.set('title', e.detail[1].title);
                    tab.set('icon', e.detail[1].icon);
                });
                documentComponent.on('actionClick', (e) => {
                    if (e.detail.length > 2 && e.detail[2].options) {
                        console.log(e.detail[2]);
                        if (e.detail[2].eventId === 'document.load') {
                            e.detail[0].preventDefault();
                            const initid = e.detail[2].options[0];
                            const viewid = e.detail[2].options[1];
                            this.addDocument({ initid, viewid });
                        }
                    }
                });
            },

            // Expose public methods (from method sections) in DOM props
            bindPublicMethods: () => {
                // Bind exposed methods to events
                const _this = this;
                Object.keys(this.$options.methods).forEach((methodName) => {
                    const method = {
                        [methodName]: (...args) => {
                            try {
                                const ret = _this[methodName].call(_this, ...args);
                            } catch (e) {
                                console.error(`From ${methodName} : ${e}`);
                            }
                        },
                    };
                    if (methodName !== '$emit') {
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
                                this.privateScope.computeTabstripMargin();
                                break;
                            // Remove document
                            case 'remove':
                                if (e.items.length === 1) {
                                    this.tabstrip.remove(e.index);
                                } else if (e.items.length > 1) {
                                    this.tabstrip.remove('li');
                                }

                                this.privateScope.computeTabstripMargin();
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
            tabsListElement: null,
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

        tabslist() {
            return this.tabsListElement.data('kendoDropDownList');
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
                index = this.tabsArray.findIndex((d) => d.initid === documentId.initid);
                if (index < 0) {
                    index = 0;
                }
            }

            this.tabslist.select(index);
            this.tabstrip.select(index);
        },

        closeDocument(documentId) {
            let index = -1;
            if (typeof documentId === 'number') {
                index = documentId;
            } else if (typeof documentId === 'object') {
                index = this.tabsArray.findIndex((d) => d.initid === documentId.initid);
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
    },
};
