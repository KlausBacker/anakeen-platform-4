// jscs:disable disallowImplicitTypeConversion
// jscs:disable requirePaddingNewLinesBeforeLineComments
import contentTemplate from './templates/tab/seTabsContent.template.kd';
import headerTemplate from './templates/tab/seTabsHeader.template.kd';
import openedTabListItemTemplate from './templates/openedTabList/seOpenedTabListItem.template.kd';
import { AnkMixin } from '../AnkVueComponentMixin';
import TabModel from './model/tabModel';

const Constants = {
    WELCOME_TAB_ID: 'welcome_tab_id',
    NEW_TAB_ID: 'new_tab_id',
    LAZY_TAB_ID: 'lazy_tab_id',
    CUSTOM_TAB_ID: 'custom_tab_id',
};

const noop = () => {
};

const camelToKebab = (string) => string.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();

const smartElementEvents = ['beforeRender', 'ready', 'change', 'displayMessage', 'displayError', 'validate',
    'attributeBeforeRender', 'attributeReady',
    'attributeHelperSearch', 'attributeHelperResponse', 'attributeHelperSelect',
    'attributeArrayChange', 'actionClick',
    'attributeAnchorClick',
    'beforeClose', 'close',
    'beforeSave', 'afterSave', 'attributeDownloadFile', 'attributeUploadFile',
    'beforeDelete', 'afterDelete',
    'beforeRestore', 'afterRestore',
    'failTransition', 'successTransition',
    'beforeDisplayTransition', 'afterDisplayTransition',
    'beforeTransition', 'beforeTransitionClose',
    'destroy', 'attributeCreateDialogDocumentBeforeSetFormValues',
    'attributeCreateDialogDocumentBeforeSetTargetValue', 'attributeCreateDialogDocumentReady',
    'attributeCreateDialogDocumentBeforeClose', 'attributeCreateDialogDocumentBeforeDestroy',
];

export default {
    mixins: [AnkMixin],
    props: {
        'header-tab-template': {
            type: String,
            default: '',
        },

        'welcome-tab-template': {
            type: String,
            default: '',
        },

        'custom-tab-template': {
            type: String,
            default: '',
        },

        'se-list': {
            type: String,
            default: '[]',
            validator: (value) => {
                try {
                    JSON.parse(value);
                    return true;
                } catch (err) {
                    console.error('"se-list" prop validation failed :', err.toString());
                    return false;
                }
            },
        },

        'se-css': {
            type: String,
            default: '',
        },

        closable: {
            type: Boolean,
            default: true,
        },

        addable: {
            type: Boolean,
            default: false,
        },

        sortable: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            tabModel: null,
            tabstripEl: null,
            tabslistEl: null,
            tabslistSource: null,
            newTabConfig: null,
            welcomeTabConfig: null,
            defaultEmptyImgUrl: '/CORE/Images/anakeenplatform-logo-fondblanc.svg',
        };
    },

    computed: {
        hasWelcomeTab() {
            return !!(this.welcomeTabTemplate);
        },

        hasCustomTab() {
            return !!(this.customTabTemplate);
        },

        hasContent() {
            if (this.tabModel) {
                return !this.tabModel.isEmpty();
            } else {
                return false;
            }
        },

        seListProp() {
            return JSON.parse(this.seList);
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
                return this.$(this.tabstrip.contentElement(index)).find('ank-smart-element');
            }

            return null;
        },

        translations() {
            return {
                noSEOpened: this.$pgettext('SETabs', 'No Smart Element opened'),
                closeAllSE: this.$pgettext('SETabs', 'Close all '),
            };
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

        addable(newValue) {
            this.privateScope.setAddTabButton(newValue);
        },

        sortable(newValue) {
            this.privateScope.configureSortable(newValue);
        },
    },

    created() {
        if (this.hasWelcomeTab) {
            this.$options.components.welcomeTab = {
                template: this.welcomeTabTemplate,
            };
        }

        if (this.hasCustomTab) {
            this.$options.components.customTab = {
                template: this.customTabTemplate,
            };
        }

        this.privateScope = {
            createKendoComponents: () => {
                this.privateScope.createKendoTabStrip();
                this.privateScope.createKendoOpenedTabsList();
                this.privateScope.initTabModel();
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
                    noDataTemplate: this.translations.noSEOpened,
                    headerTemplate: `<button class="seTabs__tabsList__list__close__all">
                                        ${this.translations.closeAllSE}
                                     </button>`,
                });
                this.tabslist.list.addClass('seTabs__tabsList__list');
                this.tabslist.list
                    .find('.seTabs__tabsList__list__close__all')
                    .on('click', this.closeAllSE);
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
                const paginatorWidth = this.$(this.$refs.tabsTools).outerWidth(true);
                let marginRight = paginatorWidth || 0;
                // let marginLeft = 0;
                // const prev = this.tabstripEl.find('.k-tabstrip-prev');
                const next = this.tabstripEl.find('.k-tabstrip-next');
                if (/*prev.length && */next.length) {
                    const nextWidth = next.outerWidth(true);
                    // const prevWidth = prev.outerWidth(true);
                    next.css('right', `${paginatorWidth}px`);
                    // prev.css('right', `${paginatorWidth + nextWidth}px`);
                    marginRight += nextWidth;
                    this.tabstrip.tabGroup.find('#seTabs__new__tab__button')
                        .addClass('new__tab__button--sticky');
                } else {
                    this.tabstrip.tabGroup.find('#seTabs__new__tab__button')
                        .removeClass('new__tab__button--sticky');
                }

                this.tabstrip.tabGroup.css('margin-right', `${marginRight}px`);
            },

            initTabModel: () => {
                if (this.welcomeTabConfig) {
                    const welcomeTab = Object.assign({}, { tabId: Constants.WELCOME_TAB_ID }, this.welcomeTabConfig);
                    if (this.privateScope.getLazyTabIndex() > -1) {
                        this.tabModel.add(welcomeTab);
                    } else {
                        this.tabModel.add(welcomeTab, this.newLazyTab);
                    }

                    this.selectIndex(0);
                }

                this.privateScope
                    .configureSortable(this.sortable);
                this.privateScope
                    .setAddTabButton(this.addable);
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

            configureSortable: (sortable = true) => {
                if (sortable) {
                    this.tabstrip.tabGroup.kendoSortable({
                        filter: 'li.k-item',
                        axis: 'x',
                        container: 'ul.k-tabstrip-items',
                        hint: (element) => $("<div id='hint' class='k-widget k-header k-tabstrip'>" +
                            "<ul class='k-tabstrip-items k-reset'>" +
                            "<li class='k-item k-state-active k-tab-on-top'>" +
                            element.html() +
                            '</li></ul></div>'),

                        start: (e) => {
                            this.tabstrip.activateTab(e.item);
                        },

                        change: (e) => {
                            const reference = this.tabstrip.tabGroup.children().eq(e.newIndex);

                            if (e.oldIndex < e.newIndex) {
                                this.tabstrip.insertAfter(e.item, reference);
                            } else {
                                this.tabstrip.insertBefore(e.item, reference);
                            }
                        },
                    });
                } else {
                    this.tabstrip.tabGroup.kendoSortable({
                        disabled: 'li.k-item',
                    });
                }
            },

            setAddTabButton: (addable = true) => {
                let newTabButton = this.$('#seTabs__new__tab__button');
                if (addable) {
                    if (!newTabButton.length) {
                        newTabButton = this.$('<button id="seTabs__new__tab__button" class="tab__new__button">' +
                            '<i class="material-icons">add</i>' +
                            '</button>');
                        newTabButton.on('click', this.privateScope.onAddTabClick);
                    }

                    this.tabstrip.tabGroup.append(newTabButton);
                } else {
                    if (newTabButton.length) {
                        newTabButton.remove();
                    }
                }
            },

            setCloseTabButton: (tab, forceClose) => {
                const $tab = this.$(tab);
                const closable = forceClose !== undefined ? forceClose : this.closable;
                if (closable) {
                    $tab.find('.seTab__header__content')
                        .append('<span data-type="remove" class="k-link"><span class="k-icon k-i-x"></span></span>');
                    $tab.on('click', "[data-type='remove']", this.privateScope.onCloseTabClick);
                } else {
                    $tab.off('click', "[data-type='remove']");
                    $tab.find("span[data-type='remove']").remove();
                }
            },

            loadLazyTabDocument: (data) => {
                const tab = this.$(this.tabstrip.items()[this.privateScope.getLazyTabIndex()]);
                tab.find('.seTab__title').text(data.data.title);
                tab.find('.seTab__icon')
                    .replaceWith(`<img class="seTab__icon" src="${data.data.icon}" />`);
                this.privateScope.onAddDocumentTab(this.privateScope.getLazyTabIndex());
                this.$(this.tabstrip.items()[this.privateScope.getLazyTabIndex()]).show();
                this.$(this.lazyTabDocument).prop('seValue', JSON.stringify(data.data));
                this.tabModel.get(this.privateScope.getLazyTabIndex()).tabId = data.tabId;
                this.tabsListSource.add(data);
            },

            bindWelcomeTabEvents: ($newTab, index) => {
                $newTab.on('document-creation', e => this.privateScope.onCreateDocumentClick(e, index));
                $newTab.on('document-selected', (e) => {
                    this.setSE(e.detail[0], index);
                });
            },

            bindLazyTabEvents: () => {

            },

            getSEEventHandler: (eventName) => {
                switch (eventName) {
                    case 'ready':
                        return this.privateScope.onDocumentReady;
                    case 'actionClick':
                        return this.privateScope.onDocumentActionClick;
                    case 'afterSave':
                        return this.privateScope.onDocumentAfterSave;
                    default:
                        return noop;
                }
            },

            bindDocumentTabEvents: ($doc, tabIndex) => {
                const documentComponent = $doc;
                smartElementEvents.forEach(eventName => {
                    documentComponent.on(eventName, e => {
                        const cb = this.privateScope.getSEEventHandler(eventName);
                        cb.call(this, e, tabIndex);
                        const notCancelled = this.$emitAnkEvent(`se-${camelToKebab(eventName)}`, e, tabIndex);
                        if (!notCancelled) {
                            if (e.detail && e.detail.length) {
                                if (e.detail[0].cancelable) {
                                    e.detail[0].preventDefault();
                                }
                            }
                        }
                    });
                });
            },

            onModelAddItem: (event) => {
                const addedItems = event.items;
                addedItems.forEach((item, pos) => {
                    const header = this.$kendo.template(item.headerTemplate)(item.data || {});
                    const content = this.$kendo.template(item.contentTemplate)(item.data || {});
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
                        this.selectIndex(0);
                    }

                    this.tabstrip.remove(event.index);
                } else if (event.items.length > 1) {
                    this.tabstrip.remove('li');
                }

                if (this.tabModel.isEmpty() || this.tabModel.findIndex(t => t.tabId !== Constants.LAZY_TAB_ID) === -1) {
                    this.privateScope.initTabModel();
                }

                this.privateScope.setTabstripPagination();
                event.items.forEach(i => {
                    const deletedEl = this.tabsListSource.data().find((e) => e.tabId == i.tabId);
                    if (deletedEl) {
                        this.tabsListSource.remove(deletedEl);
                    }
                });
            },

            onModelItemChange: (event, model) => {
                const index = this.tabModel.findIndex((d) => d.tabId === event.items[0].tabId);
                const props = event.field.split('.');
                let newValue;
                const $indexedItem = this.$(this.tabstrip.items()[index]);
                switch (event.field) {
                    case 'data.title':
                        newValue = event.items[0][props[0]][props[1]];
                        $indexedItem.find('span.seTab__title').text(newValue);
                        break;
                    case 'data.icon':
                        newValue = event.items[0][props[0]][props[1]];
                        $indexedItem.find('img.seTab__icon').prop('src', newValue);
                        break;
                }
            },

            onAddGenericTab: (index) => {
                this.privateScope
                    .setAddTabButton(this.addable);
                this.privateScope
                    .setCloseTabButton(this.tabstrip.items()[index]);
                this.privateScope.setTabstripPagination();
            },

            onAddWelcomeTab: (index) => {
                const tabContent = this.tabstrip.contentElement(index);
                const $newTab = this.$(tabContent).find('ank-welcome-tab');
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
                const $doc = this.$(tabContent).find('ank-smart-element');
                this.privateScope
                    .bindDocumentTabEvents($doc, index);
                $doc.one('ready', () => {
                    this.$(tabContent)
                        .find('.seTabs__tab__content--se').show();
                    this.$(tabContent)
                        .find('.seTabs__tab__content--loading').hide();
                });
            },

            onAddTabClick: (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (this.newTabConfig) {
                    this.tabModel.add(Object.assign({}, { tabId: Constants.NEW_TAB_ID }, this.newTabConfig));
                    this.selectIndex(this.tabModel.size() - 1);
                }

                this.$emitAnkEvent('tabs-new-tab');
            },

            onCloseTabClick: (e) => {
                e.preventDefault();
                e.stopPropagation();

                const item = this.$(e.target).closest('.k-item');
                this.closeSE(item.index());
            },

            onCreateDocumentClick: (e, index) => {
                const newId = e.detail[0].initid;
                this.setSE({
                    initid: newId,
                    viewid: '!defaultCreation',
                }, index);
                this.selectIndex(index);
            },

            onTabstripSelect: (e) => {
                const itemSelectedPos = this.$(e.item).index();
                const selectedTab = this.tabModel.get(itemSelectedPos);
                if (selectedTab.tabId === Constants.NEW_TAB_ID ||
                    selectedTab.tabId === Constants.WELCOME_TAB_ID) {
                    const DOMElement = this.tabstrip.contentElement(itemSelectedPos);
                    const welcomeTab = this.$(DOMElement).find('ank-welcome-tab');
                    if (welcomeTab.prop('publicMethods')) {
                        welcomeTab.prop('publicMethods').refresh();
                    }
                }
            },

            onOpenedTabsListDataBound: (e) => {
                e.sender.list.find('.seTabs__openedTab__listItem__close').off('click');
                e.sender.list.find('.seTabs__openedTab__listItem__close').on('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeSE({
                        tabId: $(e.target).closest('.seTabs__openedTab__listItem').data('docid'),
                    });
                });
            },

            onOpenedTabsListItemClick: (e) => {
                this.selectSE(e.dataItem.data);
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
                    $document.prop('publicMethods').injectCSS(this.seCss);
                }

                if (tabPosition !== undefined) {
                    this.$(this.tabstrip.items()[tabPosition])
                        .find('a.seTab__header__content')
                        .prop('href', readyEvent.detail[1].url);
                    this.$(this.tabstrip.items()[tabPosition])
                        .find('a.seTab__header__content .seTab__title')
                        .text(readyEvent.detail[1].title);
                    this.$(this.tabstrip.items()[tabPosition])
                        .find('a.seTab__header__content .seTab__icon')
                        .replaceWith(`<img class="seTab__icon" src="${readyEvent.detail[1].icon}" />`);
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
                        this.addSE({ initid, viewid });
                    }
                }
            },

            onDocumentAfterSave: (e, tabPosition) => {
                const tab = this.tabModel.get(tabPosition);
                tab.set('tabId', e.detail[1].initid);
                tab.set('data.title', e.detail[1].title);
                tab.set('data.icon', e.detail[1].icon);
                // this.$emit('document-modified', e.detail);
            },

            formatSE: (seConfig) => {
                let initid = null;
                let otherProps = {};
                if (typeof seConfig === 'object') {
                    initid = seConfig.initid;
                    otherProps = Object.assign({}, seConfig);
                } else if (typeof seConfig === 'number' || typeof seConfig === 'string') {
                    initid = seConfig;
                }

                if (typeof initid !== 'string' && typeof initid !== 'number') {
                    throw "Error in the Smart Element format : '" +
                    JSON.stringify(seConfig) +
                    "' must be String|Number or Object with an 'initid' property";
                }

                return Object.assign({}, otherProps, { initid: initid.toString() });
            },
        };
    },

    mounted() {
        this.$kendo.ui.progress(this.$(this.$refs.tabsWrapper), true);
        const ready = () => {
            this.privateScope.createKendoComponents();
            this.$emitAnkEvent('se-tabs-ready');
            this.$kendo.ui.progress(this.$(this.$refs.tabsWrapper), false);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },

    methods: {
        addSE(seConfig) {
            const seFormat = this.privateScope.formatSE(seConfig);
            const index = this.tabModel.findIndex(t => t.tabId === seFormat.initid);
            if (index < 0) {
                const tabData = {
                    tabId: seFormat.initid,
                    headerTemplate,
                    contentTemplate,
                    data: Object.assign({}, seFormat),
                };
                if (this.privateScope.canUseLazyTab()) {
                    // Use preloaded smart element
                    this.privateScope.loadLazyTabDocument(tabData);
                } else {
                    this.tabModel.add(tabData);
                }

                this.selectSE(seFormat);
            } else {
                this.selectIndex(index);
            }
        },

        setSE(se, position) {
            if (position === undefined) {
                this.addSE(se);
            } else {
                const seFormat = this.privateScope.formatSE(se);
                const index = this.tabModel.findIndex(t => t.tabId === seFormat.initid);
                if (index < 0) {
                    const tabData = {
                        tabId: seFormat.initid,
                        headerTemplate,
                        contentTemplate,
                        data: Object.assign({}, seFormat),
                    };
                    this.tabModel.replace(position, tabData);
                    this.selectSE(seFormat);
                } else {
                    this.selectIndex(index);
                }
            }
        },

        selectIndex(seIndex = 0) {
            let index = seIndex;
            if (index < 0) {
                index = 0;
            }

            this.$emitAnkEvent('se-tab-selected', this.tabModel.get(index), index);

            this.tabstrip.select(index);
        },

        selectSE(seConfig) {
            const seFormat = this.privateScope.formatSE(seConfig);
            let index = this.tabModel.findIndex(t => t.tabId === seFormat.initid);
            if (index < 0) {
                index = 0;
            }

            this.$emitAnkEvent('se-tab-selected', this.tabModel.get(index), index);

            this.tabstrip.select(index);
        },

        closeSE(documentId) {
            this.tabModel.remove(documentId);
        },

        closeAllSE() {
            this.tabModel.removeAll();
        },

        setNewTabConfig(newTabConfiguration) {
            this.newTabConfig = newTabConfiguration;
        },

        initWithWelcomeTab(tabConfig = null) {
            this.welcomeTabConfig = tabConfig || this.newTabConfig;
            this.privateScope.initTabModel();
        },

        addCustomTab(tabConfiguration) {
            if (tabConfiguration.headerTemplate && tabConfiguration.contentTemplate) {
                this.tabModel.add(Object.assign({}, tabConfiguration, { tabId: Constants.CUSTOM_TAB_ID }));
                this.selectSE({ initid: Constants.CUSTOM_TAB_ID });
            }
        },
    },
};
