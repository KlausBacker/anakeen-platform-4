import headerTemplate from './documentsTabsHeader.template.kd';
import contentTemplate from './documentsTabsContent.template.kd';

export default {
    created() {
        this.privateScope = {
            initKendoComponents: () => {
                return new Promise((resolve, reject) => {
                    this.openedDocuments = new this.$kendo.data.ObservableArray([]);
                    this.viewModel = this.$kendo.observable({
                        documents: this.openedDocuments,
                        onSelectTabList: this.privateScope.onSelectTabList,
                    });
                    this.$kendo.bind(this.$(this.$refs.tabsWrapper), this.viewModel);

                    this.$(this.$refs.newActionButton).kendoMenu({
                        animation: false,
                        select: this.privateScope.onClickNewAction,
                        openOnClick: true,
                        dataSource: {
                            text: 'Nouveau',
                            cssClass: 'documentsList__documentsTabs__new__menu',
                            items: [],
                        },
                    });
                    const buttons = this.$(this.$refs.tabsPaginator).find('button').kendoButton();
                    this.privateScope.bindTabsPaginatorEvents(buttons);
                    this.tabstrip = this.$(this.$refs.tabstrip).data('kendoTabStrip');
                    resolve();
                });
            },

            formatTabData: (data) => {
                return {
                    tabId: data.initid,
                    tabHeader: this.$kendo.template(headerTemplate)(data),
                    tabContent: this.$kendo.template(contentTemplate)(data),
                    documentContent: data,
                };
            },

            onStoreChange: (storeData) => {
                if (storeData) {
                    switch (storeData.type) {
                        case 'OPEN_DOCUMENT':
                            if (this.openedDocumentsArray.findIndex(d => d.tabId === storeData.data.initid) < 0) {
                                this.addTab(storeData.data);
                            } else {
                                this.selectTab(storeData.data);
                            }

                            break;
                    }
                }
            },

            updateTabTitleContent: (formattedData) => {
                const tabstrip = this.$(this.$refs.tabstrip).data('kendoTabStrip');
                const tabs = tabstrip.tabGroup.find('li[role=tab]:last');
                tabs.find('.k-link').html(formattedData.tabHeader);
                this.$(tabs).on('click', "[data-type='remove']", (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const item = this.$(e.target).closest('.k-item');
                    this.removeTab(item.index());
                });
            },

            bindDocumentEvents: (tabContent) => {
                const documentInstance = this.$(tabContent).find('a4-document');
                if (documentInstance && documentInstance.length) {
                    documentInstance.on('actionClick', (e) => {
                        console.log(e);
                    });
                }
            },

            bindTabsPaginatorEvents: (buttons) => {
                const closeButton = buttons.filter('.documentsList__documentsTabs__tabs__paginator__close')[0];
                this.$(closeButton).data('kendoButton').bind('click', (e) => {
                    this.removeAllTabs();
                });
            },

            sendGetRequest: (url) => {
                const element = this.$(this.$refs.wrapper);
                this.$kendo.ui.progress(element, true);
                return new Promise((resolve, reject) => {
                    this.$http.get(url)
                        .then((response) => {
                            this.$kendo.ui.progress(element, false);
                            resolve(response);
                        }).catch((error) => {
                        this.$kendo.ui.progress(element, false);
                        reject(error);
                    });
                });
            },

            updateNewActionsItems: (data) => {
                const items = data.map((c) => {
                    return {
                        text: c.html_label,
                        cssClass: 'documentsList__documentsTabs__new__menu__item',
                        imageAttr: {
                            alt: 'Image',
                            height: '16px',
                            width: '16px',
                        },
                        imageUrl: c.image_url,
                        attr: c,
                    };
                });
                const menu = this.$(this.$refs.newActionButton).data('kendoMenu');
                menu.append(items, '.documentsList__documentsTabs__new__menu');
            },

            onClickNewAction: (e) => {
                if (e.item.getAttribute('initid') && e.item.getAttribute('html_label')) {
                    this.addTab({
                        title: `Création ${e.item.getAttribute('html_label')}`,
                        initid: e.item.getAttribute('initid'),
                        viewId: '!defaultCreation',
                    });
                }
            },

            onSelectTabList: (e) => {
                console.log(e);
            },

            bindPublicMethods: () => {
                // Bind exposed methods to events
                Object.keys(this.$options.methods).forEach((methodName) => {
                    if (methodName !== '$emit') {
                        this.$(this.$el.parentElement).on(methodName, (event, ...arg) => {
                            this[methodName].call(this, ...arg);
                        });
                    }
                });
            },
        };
    },

    mounted() {
        document.addEventListener('DOMContentLoaded', (event) => {
            const store = document.getElementById('a4-store');
            if (store) {
                store.addEventListener('store-change', (event) => {
                    const storeData = event.detail && event.detail.length ? event.detail[0] : null;
                    this.privateScope.onStoreChange(storeData);
                });
            }
        });

        // Init Kendo components
        this.privateScope.initKendoComponents().then(() => {
            this.privateScope.sendGetRequest('sba/collections')
                .then((response) => {
                    this.privateScope.updateNewActionsItems(response.data.data.sample.collections);
                });
        });
        this.privateScope.bindPublicMethods();
    },

    computed: {
        emptyData() {
            return !this.openedDocuments.length;
        },

        openedDocumentsArray() {
            return this.openedDocuments.toJSON();
        },
    },

    data() {
        return {
            openedDocuments: [],
            viewModel: null,
            tabstrip: null,
        };
    },

    methods: {
        removeAllTabs() {
            this.openedDocuments.splice(0, this.openedDocuments.length);
        },

        removeTab(index) {
            const item = this.tabstrip.items()[index];
            this.openedDocuments.splice(index, 1);
            if (this.openedDocuments.length > 0 && this.$(item).hasClass('k-state-active')) {
                this.selectTab(0);
            }
        },

        selectTab(tabId) {
            let index = 0;
            if (typeof tabId === 'number') {
                index = tabId;
            } else if (typeof tabId === 'object') {
                index = this.openedDocumentsArray.findIndex(d => d.tabId === tabId.initid);
            }

            this.tabstrip.select(index);
        },

        addTab(data) {
            const formattedData = this.privateScope.formatTabData(data);
            this.openedDocuments.push(formattedData);
            this.privateScope.updateTabTitleContent(formattedData);
            this.selectTab(this.openedDocuments.length - 1);
        },
    },

};
