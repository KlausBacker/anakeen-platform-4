// jscs:disable requirePaddingNewLinesBeforeLineComments
import contentTemplate from './templates/tab/documentTabsContent.template.kd';
import headerTemplate from './templates/tab/documentTabsHeader.template.kd';
import welcomeTemplateHeader from './templates/welcomeTab/documentTabsWelcomeHeader.template.kd';
import welcomeTemplateContent from './templates/welcomeTab/documentTabsWelcomeContent.template.kd';
import openedTabListItemTemplate from './templates/openedTabList/documentOpenedTabListItem.template.kd';
import abstractAnakeenComponent from '../componentBase';
import TabModel from './model/tabModel';

const Constants = {
    WELCOME_TAB_ID: 'welcome_tab',
    NEW_TAB_ID: 'new_tab',
    LAZY_TAB_ID: 'lazy_tab_id',
};
export default {
    mixins: [abstractAnakeenComponent],
    props: {
        closable: {
            type: Boolean,
            default: true,
        },

        'empty-img': {
            type: String,
            default: 'CORE/Images/anakeenplatform-logo-fondblanc.svg',
        },

        'document-css': {
            type: String,
            default: '',
        },

    },

    data() {
        return {
            collections: [],
            currentUser: null,
            tabModel: null,
            tabstripEl: null,
            tabslistEl: null,
            tabslistSource: null,
        };
    },

    computed: {
        emptyState() {
            if (this.tabModel) {
                return this.tabModel.isEmpty();
            } else {
                return true;
            }
        },

        tabstrip() {
            if (this.tabstripEl) {
                return this.tabstripEl.data('kendoTabStrip');
            }

            return null;
        },

        tabslist() {
            if (this.tabslistEl) {
                return this.tabslistEl.data('kendoDropDownList');
            }

            return null;
        },

        newLazyTab() {
            return {
                tabId: Constants.LAZY_TAB_ID,
                headerTemplate,
                contentTemplate,
                data: {
                    initid: 0,
                },
            };
        },

        lazyTabDocument() {
            const index = this.privateScope.getLazyTabIndex();
            if (index > -1) {
                return this.$(this.tabstrip.contentElement(index)).find('a4-document');
            }

            return null;
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
            createKendoComponents: () => {
                this.privateScope.createKendoTabStrip();
                this.privateScope.createKendoOpenedTabsList();
                this.privateScope.sendGetRequest('sba/collections')
                    .then((response) => {
                        this.collections = response.data.data.collections;
                        this.currentUser = response.data.data.user;
                        this.privateScope.initTabModel();
                    });
                this.$(window).resize(() => {
                    this.privateScope.resizeComponents();
                });
                this.privateScope.resizeComponents();
            },

            createKendoTabStrip: () => {
                this.tabstripEl = this.$(this.$refs.tabstrip).kendoTabStrip({
                    animation: false,
                    select: this.privateScope.onTabstripSelect,
                });
                this.tabModel = new TabModel();
                this.tabModel.on('add', this.privateScope.onModelAddItem);
                this.tabModel.on('remove', this.privateScope.onModelRemoveItem);
                this.tabModel.on('itemchange', this.privateScope.onModelItemChange);
            },

            createKendoOpenedTabsList: () => {
                this.tabsListSource = new this.$kendo.data.DataSource({
                    data: [],
                });
                this.tabslistEl = this.$(this.$refs.tabsList).kendoDropDownList({
                    animation: false,
                    dataSource: this.tabsListSource,
                    template: this.$kendo.template(openedTabListItemTemplate),
                    valueTemplate: '<i class="material-icons">list</i>',
                    dataBound: this.privateScope.onOpenedTabsListDataBound,
                    autoWidth: true,
                    select: this.privateScope.onOpenedTabsListItemClick,
                    noDataTemplate: 'Aucun document ouvert',
                    headerTemplate: `<button class="documentsList__documentsTabs__tabsList__list__close__all">
                                        Fermer tous les onglets
                                     </button>`,
                });
                this.tabslist.list.addClass('documentsList__documentsTabs__tabsList__list');
                this.tabslist.list
                    .find('.documentsList__documentsTabs__tabsList__list__close__all')
                    .on('click', this.closeAllDocuments);
            },

            sendGetRequest: (url, config, loadingElement) => {
                const element = this.$(loadingElement);
                this.$kendo.ui.progress(element, true);
                return new Promise((resolve, reject) => {
                    this.$http.get(url, config)
                        .then((response) => {
                            this.$kendo.ui.progress(element, false);
                            resolve(response);
                        }).catch((error) => {
                        this.$kendo.ui.progress(element, false);
                        reject(error);
                    });
                });
            },

            resizeComponents: () => {
                this.tabstrip.resize();
                this.privateScope.setTabstripPagination();
            },

            setTabstripPagination: () => {
                const paginatorWidth = this.$(this.$refs.tabsPaginator).outerWidth(true);
                let marginRight = paginatorWidth || 0;
                const prev = this.tabstripEl.find('.k-tabstrip-prev');
                const next = this.tabstripEl.find('.k-tabstrip-next');
                if (prev.length && next.length) {
                    const nextWidth = next.outerWidth(true);
                    const prevWidth = prev.outerWidth(true);
                    next.css('right', `${paginatorWidth}px`);
                    prev.css('right', `${paginatorWidth + nextWidth}px`);
                    marginRight = marginRight + prevWidth + nextWidth;
                }

                this.tabstrip.tabGroup.css('margin-right', `${marginRight}px`);
            },

            initTabModel: () => {
                const welcomeTab = {
                    tabId: Constants.WELCOME_TAB_ID,
                    headerTemplate: welcomeTemplateHeader,
                    contentTemplate: welcomeTemplateContent,
                    data: {
                        user: this.currentUser.firstName,
                        welcomeMessage: 'bienvenue sur Business App.',
                        promptMessage: 'Que voulez-vous faire ?',
                        collections: JSON.stringify(this.collections),
                        title: 'Bienvenue',
                    },
                };
                if (this.privateScope.getLazyTabIndex() > -1) {
                    this.tabModel.add(welcomeTab);
                } else {
                    this.tabModel.add(welcomeTab, this.newLazyTab);
                }

                this.selectDocument(0);
            },

            canUseLazyTab: () => {
                if (this.lazyTabDocument) {
                    if (this.lazyTabDocument.prop('publicMethods').isLoaded()) {
                        return true;
                    }
                }

                return false;
            },

            getLazyTabIndex: () => {
                if (this.tabModel) {
                    return this.tabModel.findIndex(t => t.tabId === Constants.LAZY_TAB_ID);
                }

                return -1;
            },

            setAddTabButton: () => {
                let newTabButton = this.$('#documentsList__documentsTabs__new__tab__button');
                if (!newTabButton.length) {
                    newTabButton = this.$('<button id="documentsList__documentsTabs__new__tab__button" class="tab__new__button"><i class="material-icons">add</i></button>');
                    newTabButton.on('click', this.privateScope.onAddTabClick);
                }

                this.tabstrip.tabGroup.append(newTabButton);
            },

            setCloseTabButton: (tab, forceClose) => {
                const $tab = this.$(tab);
                const closable = forceClose !== undefined ? forceClose : this.closable;
                if (closable) {
                    $tab.find('.tab__document__header__content')
                        .append('<span data-type="remove" class="k-link"><span class="k-icon k-i-x"></span></span>');
                    $tab.on('click', "[data-type='remove']", this.privateScope.onCloseTabClick);
                } else {
                    $tab.off('click', "[data-type='remove']");
                    $tab.find("span[data-type='remove']").remove();
                }
            },

            setVisitedTagToDocument: (document) => {
                this.$http.put(`documents/${document.initid}/usertags/open_document`, {
                    counter: 1,
                }).then((response) => {
                    // console.log(response);
                }).catch((error) => {
                    console.error(error);
                });
            },

            loadLazyTabDocument: (data) => {
                const tab = this.$(this.tabstrip.items()[this.privateScope.getLazyTabIndex()]);
                tab.find('.tab__document__title').text(data.data.title);
                tab.find('.tab__document__icon')
                    .replaceWith(`<img class="tab__document__icon" src="${data.data.icon}" />`);
                this.privateScope.onAddDocumentTab(this.privateScope.getLazyTabIndex());
                this.$(this.tabstrip.items()[this.privateScope.getLazyTabIndex()]).show();
                this.$(this.lazyTabDocument).prop('documentvalue', JSON.stringify(data.data));
                this.tabModel.get(this.privateScope.getLazyTabIndex()).tabId = data.tabId;
                this.tabsListSource.add(data);
            },

            bindWelcomeTabEvents: ($newTab, index) => {
                $newTab.on('document-creation', e => this.privateScope.onCreateDocumentClick(e, index));
                $newTab.on('document-selected', (e) => {
                    this.setDocument(e.detail[0], index);
                });
            },

            bindLazyTabEvents: () => {

            },

            bindDocumentTabEvents: ($doc, index) => {
                const documentComponent = $doc;
                documentComponent.on('ready', e => this.privateScope.onDocumentReady(e, index));
                documentComponent.on('actionClick', e => this.privateScope.onDocumentActionClick(e, index));
                documentComponent.on('afterSave', e => this.privateScope.onDocumentAfterSave(e, index));
                documentComponent.on('afterDelete', e => this.privateScope.onDocumentAfterDelete(e, index));
            },

            onModelAddItem: (event) => {
                const addedItems = event.items;
                addedItems.forEach((item, pos) => {
                    const header = this.$kendo.template(item.headerTemplate)(item.data);
                    const content = this.$kendo.template(item.contentTemplate)(item.data);
                    const tabAdded = { text: header, encoded: false, content: content };
                    const index = event.index + pos;
                    if (index === this.tabModel.size() - addedItems.length) {
                        this.tabstrip.append(tabAdded);
                    } else if (index === 0) {
                        this.tabstrip.insertBefore(tabAdded, this.tabstrip.items()[0]);
                    } else {
                        this.tabstrip.insertAfter(tabAdded, this.tabstrip.items()[index - 1]);
                    }

                    this.privateScope.onAddGenericTab(index);
                    if (item.tabId === Constants.NEW_TAB_ID
                        || item.tabId === Constants.WELCOME_TAB_ID) {
                        this.privateScope.onAddWelcomeTab(index);
                    } else if (item.tabId === Constants.LAZY_TAB_ID) {
                        this.privateScope.onAddLazyTab(index);
                    } else {
                        this.privateScope.onAddDocumentTab(index);
                        this.tabsListSource.add(item);
                    }
                });

            },

            onModelRemoveItem: (event, model) => {
                if (event.items.length === 1) {
                    if (this.$(this.tabstrip.items()[event.index]).hasClass('k-state-active')
                    && !this.tabModel.isEmpty()) {
                        this.selectDocument(0);
                    }

                    this.tabstrip.remove(event.index);
                } else if (event.items.length > 1) {
                    this.tabstrip.remove('li');
                }

                if (this.tabModel.isEmpty() || this.tabModel.findIndex(t => t.tabId !== Constants.LAZY_TAB_ID) === -1) {
                    this.privateScope.initTabModel();
                }

                this.privateScope.setTabstripPagination();
                event.items.forEach(i => this.tabsListSource.remove(i));
            },

            onModelItemChange: (event, model) => {
                const index = this.tabModel.findIndex((d) => d.tabId === event.items[0].tabId);
                const props = event.field.split('.');
                let newValue;
                const $indexedItem = this.$(this.tabstrip.items()[index]);
                switch (event.field) {
                    case 'data.title':
                        newValue = event.items[0][props[0]][props[1]];
                        $indexedItem.find('span.tab__document__title').text(newValue);
                        break;
                    case 'data.icon':
                        newValue = event.items[0][props[0]][props[1]];
                        $indexedItem.find('img.tab__document__icon').prop('src', newValue);
                        break;
                }
            },

            onAddGenericTab: (index) => {
                this.privateScope
                    .setAddTabButton();
                this.privateScope
                    .setCloseTabButton(this.tabstrip.items()[index]);
                this.privateScope.setTabstripPagination();
            },

            onAddWelcomeTab: (index) => {
                const tabContent = this.tabstrip.contentElement(index);
                const $newTab = this.$(tabContent).find('a4-welcome-tab');
                this.privateScope.bindWelcomeTabEvents($newTab, index);
            },

            onAddLazyTab: (index) => {
                this.$(this.tabstrip.items()[index]).hide();
                this.$(this.tabstrip.contentElement(index)).hide();
                const tabContent = this.tabstrip.contentElement(index);
                this.privateScope.bindLazyTabEvents(tabContent, index);
            },

            onAddDocumentTab: (index) => {
                const tabContent = this.tabstrip.contentElement(index);
                const $doc = this.$(tabContent).find('a4-document');
                this.privateScope
                    .bindDocumentTabEvents($doc, index);
                $doc.one('ready', () => {
                    this.$(tabContent)
                        .find('.documentsList__documentsTabs__tab__content--document').show();
                    this.$(tabContent)
                        .find('.documentsList__documentsTabs__tab__content--loading').hide();
                });
            },

            onAddTabClick: (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.tabModel.add({
                    tabId: Constants.NEW_TAB_ID,
                    headerTemplate: welcomeTemplateHeader,
                    contentTemplate: welcomeTemplateContent,
                    data: {
                        user: this.currentUser.firstName,
                        promptMessage: 'Que voulez-vous faire ?',
                        collections: JSON.stringify(this.collections),
                        title: 'Nouvel Onglet',
                    },
                });
                this.selectDocument(this.tabModel.size() - 1);
            },

            onCloseTabClick: (e) => {
                e.preventDefault();
                e.stopPropagation();

                const item = this.$(e.target).closest('.k-item');
                this.closeDocument(item.index());
            },

            onCreateDocumentClick: (e, index) => {
                const newId = e.detail[0].initid;
                const collection = this.collections.find((c) => c.initid === newId);
                if (collection) {
                    this.setDocument({
                        initid: collection.initid,
                        viewid: '!defaultCreation',
                        title: `Création ${collection.html_label}`,
                        icon: collection.image_url,
                    }, index);
                    this.selectDocument(index);
                }
            },

            onTabstripSelect: (e) => {
                const itemSelectedPos = this.$(e.item).index();
                const selectedTab = this.tabModel.get(itemSelectedPos);
                if (selectedTab.tabId === Constants.NEW_TAB_ID ||
                    selectedTab.tabId === Constants.WELCOME_TAB_ID) {
                    const DOMElement = this.tabstrip.contentElement(itemSelectedPos);
                    const welcomeTab = this.$(DOMElement).find('a4-welcome-tab');
                    if (welcomeTab.prop('publicMethods')) {
                        welcomeTab.prop('publicMethods').refresh();
                    }
                }
            },

            onOpenedTabsListDataBound: (e) => {
                e.sender.list.find('.documentTabs__openedTab__listItem__close').off('click');
                e.sender.list.find('.documentTabs__openedTab__listItem__close').on('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeDocument({
                        tabId: e.target.parentElement.dataset.docid,
                    });
                });
            },

            onOpenedTabsListItemClick: (e) => {
                this.selectDocument(e.dataItem.data);
            },

            onDocumentReady: (readyEvent, tabPosition) => {
                const $document = this.$(readyEvent.target);
                const iframeDocument = this.$(readyEvent.detail[0].target);
                iframeDocument.find('.dcpDocument__header').hide();
                const menus = iframeDocument.find('nav.dcpDocument__menu');
                if (menus.length > 1) {
                    menus[0].classList.add('menu--top');
                    menus[1].classList.add('menu--bottom');
                }

                if (this.documentCss) {
                    $document.prop('publicMethods').injectCSS(this.documentCss);
                }

                if (tabPosition !== undefined) {
                    this.$(this.tabstrip.items()[tabPosition])
                        .find('a.tab__document__header__content').prop('href', readyEvent.detail[1].url);
                }

                const lazyIndex = this.privateScope.getLazyTabIndex();
                if (lazyIndex != -1) {
                    this.tabModel.remove(lazyIndex);
                }
                this.tabModel.add(this.newLazyTab);
            },

            onDocumentActionClick: (e, tabPosition) => {
                if (e.detail.length > 2 && e.detail[2].options) {
                    if (e.detail[2].eventId === 'document.load') {
                        e.detail[0].preventDefault();
                        const initid = e.detail[2].options[0];
                        const viewid = e.detail[2].options[1];
                        this.addDocument({ initid, viewid });
                    }
                }
            },

            onDocumentAfterSave: (e, tabPosition) => {
                const tab = this.tabModel.get(tabPosition);
                tab.set('tabId', e.detail[1].initid);
                tab.set('data.title', e.detail[1].title);
                tab.set('data.icon', e.detail[1].icon);
                this.$emit('document-modified', e.detail);
            },

            onDocumentAfterDelete: (e, tabPosition) => {
                this.$emit('document-deleted', e.detail);
            },
        };
    },

    mounted() {
        this.$kendo.ui.progress(this.$(this.$refs.tabsWrapper), true);
        const ready = () => {
            this.privateScope.createKendoComponents();
            this.$emit('document-tabs-ready', this.$el.parentElement);
            this.$kendo.ui.progress(this.$(this.$refs.tabsWrapper), false);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },

    methods: {
        addDocument(document) {
            const index = this.tabModel.findIndex(t => t.tabId == document.initid);
            if (index < 0) {
                const tabData = {
                    tabId: document.initid,
                    headerTemplate,
                    contentTemplate,
                    data: Object.assign({}, document),
                };
                if (this.privateScope.canUseLazyTab()) {
                    console.log('USE LAZY LOAD');
                    this.privateScope.loadLazyTabDocument(tabData);
                } else {
                    console.log("DON'T USE LAZY LOAD");
                    this.tabModel.add(tabData);
                }

                this.selectDocument(document);
                this.privateScope.setVisitedTagToDocument(document);
            } else {
                this.selectDocument(index);
            }
        },

        setDocument(document, position) {
            if (position === undefined) {
                this.addDocument(document);
            } else {
                const index = this.tabModel.findIndex(t => t.tabId == document.initid);
                if (index < 0) {
                    const tabData = {
                        tabId: document.initid,
                        headerTemplate,
                        contentTemplate,
                        data: Object.assign({}, document),
                    };
                    this.tabModel.replace(position, tabData);
                    /*if (this.privateScope.canUseLazyTab()) {
                        console.log('USE LAZY LOAD');
                        this.tabModel.replace(position, this.tabModel.remove(this.lazyTabIndex));
                        this.privateScope.loadLazyTabDocument(tabData);
                        this.selectDocument(document);
                    } else {
                        console.log("DON'T USE LAZY LOAD");
                        this.tabModel.replace(position, tabData);
                    }*/
                    this.selectDocument(document);
                } else {
                    this.selectDocument(index);
                }
            }
        },

        selectDocument(documentId) {
            let index = 0;
            if (typeof documentId === 'number') {
                if (documentId >= 0 && documentId < this.tabModel.size()) {
                    index = documentId;
                }
            } else if (typeof documentId === 'object' && documentId !== null
                && documentId.initid !== undefined) {
                index = this.tabModel.findIndex(t => t.tabId == documentId.initid);
                if (index < 0) {
                    index = 0;
                }
            }

            this.tabstrip.select(index);
        },

        closeDocument(documentId) {
            this.tabModel.remove(documentId);
        },

        closeAllDocuments() {
            this.tabModel.removeAll();
        },
    },
};
