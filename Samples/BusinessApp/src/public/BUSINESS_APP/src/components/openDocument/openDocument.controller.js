export default {
    created() {
        this.privateScope = {
            configureCloseTab: (tab) => {
                this.$(tab).on('click', "[data-type='remove']", (e) => {
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
        this.initKendo().then(() => {
            this.sendGetRequest('sba/collections')
              .then((response) => {
                this.updateNewActionsItems(response.data.data.sample.collections);
            });
        });
    },

    data() {
        return {
            tabstripEl: null,
            openedDocuments: [],
        };
    },

    computed: {
        emptyData() {
            return !this.openedDocuments.length;
        },

        tabstrip() {
            return this.tabstripEl ? this.tabstripEl.data('kendoTabStrip') : null;
        },
    },

    methods: {
        onStoreChange(storeData) {
            if (storeData) {
                switch (storeData.type) {
                case 'OPEN_DOCUMENT':
                    this.addTab(storeData.data);
                break;
            }
            }
        },

        initKendo() {
            return new Promise((resolve) => {
                this.$(this.$refs.newActionButton).kendoMenu({
                    animation: false,
                    select: this.onClickNewAction,
                    openOnClick: true,
                    dataSource: [
                        {
                            text: 'Nouveau',
                            cssClass: 'documentsList__openDocuments__new__menu',
                            items: [],
                        },
                    ],
                });
                this.tabstripEl = this.$(this.$refs.tabstrip).kendoTabStrip({
                    animation: false,
                    scrollable: true,
                });
                const buttons = this.$(this.$refs.tabsPaginator).find('button').kendoButton();
                this.privateScope.bindTabsPaginatorEvents(buttons);
                const tabList = this.$(this.$refs.tabsPaginator).find('ul').kendoMenu({
                    animation: false,
                    select: this.onSelectTab,
                    openOnClick: true,
                    dataSource: [
                        {
                            text: '<span class="k-icon k-i-menu"></span>',
                            encoded: false,
                            items: [],
                        },
                    ],
                });

                resolve();
            });
        },

        onClickNewAction(e) {
            if (e.item.getAttribute('initid') && e.item.getAttribute('html_label')) {
                this.addTab({
                    title: `Création ${e.item.getAttribute('html_label')}`,
                    initid: e.item.getAttribute('initid'),
                    viewId: '!defaultCreation',
                });
            }
        },

        onSelectTab(e) {
            console.log(e);
        },
        updateNewActionsItems(data) {
            const items = data.map((c) => {
                return {
                    text: c.html_label,
                    cssClass: 'documentsList__openDocuments__new__menu__item',
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
            menu.append(items, '.documentsList__openDocuments__new__menu');
        },

        sendGetRequest(url) {
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

        headerTemplate(data) {
            const template = `<span>${data.title}</span>
            <span class="documentsList__documentsTabs__tabs__tab__close" data-type="remove">
                <span class="k-icon k-i-x"></span>
            </span>`;

            return template;

        },

        contentTemplate(data) {
            let option = `${data.initid ? `initid="${data.initid}"` : ''}`;
            option += `${data.viewId ? ` viewid="${data.viewId}"` : ''}`;
            return `<a4-document ${option} ></a4-document>`;
        },

        addTab(data) {
            this.openedDocuments.push(data);
            this.tabstrip.append({
                text: this.headerTemplate(data),
                encoded: false,
                content: this.contentTemplate(data),
            });
            this.tabstrip.select(this.tabstrip.items().length - 1);
            const tab = this.tabstrip.items()[this.tabstrip.items().length - 1];
            const content = this.tabstrip.contentElement(this.tabstrip.items().length - 1);
            this.privateScope.configureCloseTab(tab);
            this.privateScope.bindDocumentEvents(content);
        },

        removeTab(index) {
            this.openedDocuments.splice(index, 1);
            const item = this.tabstrip.items()[index];
            this.tabstrip.remove(index);
            if (this.tabstrip.items().length > 0 && this.$(item).hasClass('k-state-active')) {
                this.tabstrip.select(0);
            }
        },

        removeAllTabs() {
            this.openedDocuments = [];
            this.tabstrip.remove('li');
        },

        onClickCloseAllTabs() {
            this.removeAllTabs();
        },

        onDocumentActionClick(event, document, options) {
            console.log(event, document, options);
        },

        onAttributeAnchorClick(event, document, options) {
            console.log(event, document, options);
        },
    },
};
