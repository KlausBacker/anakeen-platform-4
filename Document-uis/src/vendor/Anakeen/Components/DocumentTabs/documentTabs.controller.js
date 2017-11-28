// jscs:disable requirePaddingNewLinesBeforeLineComments
import contentTemplate from './documentTabsContent.template.kd';
import headerTemplate from './documentTabsHeader.template.kd';
import welcomeTemplate from './documentTabsWelcome.template.kd';
import openedTabListItemTemplate from './documentOpenedTabListItem.template.kd';
import recentConsultTemplate from './documentTabsRecentConsultItem.template.kd';
import abstractAnakeenComponent from '../componentBase';

const Constants = {
    WELCOME_TAB_ID: 'welcome_tab',
    NEW_TAB_ID: 'new_tab',
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

            initKendoOpenedTabsList: () => {
                this.tabsListSource = new this.$kendo.data.DataSource({
                    data: [],
                });
                this.tabsListElement = this.$(this.$refs.tabsList).kendoDropDownList({
                    animation: false,
                    dataSource: this.tabsListSource,
                    template: this.$kendo.template(openedTabListItemTemplate),
                    valueTemplate: '<i class="material-icons">list</span>',
                    autoWidth: true,
                    select: this.privateScope.onClickTabList,
                    noDataTemplate: 'Aucun document ouvert',
                    headerTemplate: '<button>Fermer tous les onglets</button>',
                });
                this.tabslist.list.addClass('documentsList__documentsTabs__tabsList__list');
            },

            initKendoTabStrip: () => {
                this.tabstripElement = this.$(this.$refs.tabstrip).kendoTabStrip({
                    animation: false,
                    select: (e) => {
                        const i = this.$(e.item).index();
                        const selectedTab = this.openedTabs[i];
                        if (selectedTab.tabId === Constants.NEW_TAB_ID ||
                            selectedTab.tabId === Constants.WELCOME_TAB_ID) {
                            const element = this.tabstrip.contentElement(i);
                            const welcomeTab = this.$(element).find('a4-welcome-tab');
                            if (welcomeTab) {
                                welcomeTab.prop('publicMethods').refresh();
                            }
                        }
                    },
                });
                this.openedTabs = new this.$kendo.data.ObservableArray([]);
                this.privateScope.bindDataChange(this.openedTabs);
            },
            // Init the model and view kendo element
            initKendoComponents: () => new Promise((resolve) => {
                this.privateScope.initKendoTabStrip();
                this.privateScope.initKendoOpenedTabsList();
                this.privateScope.sendGetRequest('sba/collections')
                    .then((response) => {
                        this.collections = response.data.data.collections;
                        this.currentUser = response.data.data.user;
                        this.privateScope.initTabsData();
                    });
                this.$(window).resize(() => {
                    this.privateScope.resizeComponents();
                });
                this.privateScope.resizeComponents();
                resolve();
            }),

            sendGetRequest: (url) => {
                const element = this.$(this.$refs.tabsWrapper);
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

            initTabsData: () => {
                this.openedTabs.push({
                    tabId: Constants.WELCOME_TAB_ID,
                    headerTemplate: `<span class="tab__document__header__content">                            
                                        <span class="tab__document__title">BIENVENUE</span>
                                        <span class="tab__new__button"><i class="material-icons">add</i></span>
                                     </span>`,
                    contentTemplate: welcomeTemplate,
                    data: {
                        user: this.currentUser.firstName,
                        welcomeMessage: 'bienvenue sur  Business App.',
                        promptMessage: 'Que voulez-vous faire ?',
                        collections: JSON.stringify(this.collections),
                    },
                });
                this.selectDocument(0);
            },

            onClickNewTabButton: (e) => {
                this.openedTabs.push({
                    tabId: Constants.NEW_TAB_ID,
                    headerTemplate: `<span class="tab__document__header__content">
                                        <span class="tab__document__title">NOUVEL ONGLET</span>
                                        <span class="tab__new__button"><i class="material-icons">add</i></span>
                                     </span>`,
                    contentTemplate: welcomeTemplate,
                    data: {
                        user: this.currentUser.firstName,
                        promptMessage: 'Que voulez-vous faire ?',
                        collections: JSON.stringify(this.collections),
                    },
                });
                this.selectDocument(this.openedTabs.length - 1);
            },

            onClickTabList: (e) => {
                this.selectDocument(e.sender.dataItem(e.item));
            },

            // Enable or disable close tab button
            configureCloseTab: (tab, forceClose) => {
                const $tab = this.$(tab);
                const closable = forceClose !== undefined ? forceClose : this.closable;
                if (closable) {
                    $tab.find('.tab__document__header__content')
                        .append('<span data-type="remove" class="k-link"><span class="k-icon k-i-x"></span></span>');
                    $tab.on('click', "[data-type='remove']", (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        const item = this.$(e.target).closest('.k-item');
                        this.closeDocument(item.index());
                    });
                    $tab.on('click', '.tab__new__button', (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        this.privateScope.onClickNewTabButton();
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
                let marginRight = paginatorWidth || 0;
                const prev = this.tabstripElement.find('.k-tabstrip-prev');
                const next = this.tabstripElement.find('.k-tabstrip-next');
                if (prev.length && next.length) {
                    const nextWidth = next.outerWidth(true);
                    const prevWidth = prev.outerWidth(true);
                    next.css('right', `${paginatorWidth}px`);
                    prev.css('right', `${paginatorWidth + nextWidth}px`);
                    marginRight = marginRight + prevWidth + nextWidth;
                }

                this.tabstrip.tabGroup.css('padding-right', `${marginRight}px`);
            },

            // Tag visited document
            setTagToDocument: (document) => {
                this.$http.put(`documents/${document.initid}/usertags/open_document`, {
                    counter: 1,
                }).then((response) => {
                        // console.log(response);
                    }).catch((error) => {
                    console.error(error);
                });
            },

            // Bind documents events to tabs system
            bindDocumentEvents: (tabContent, tabItem, index) => {
                const tab = this.openedTabs[index];
                const documentComponent = this.$(tabContent).find('a4-document');
                documentComponent.on('ready', (e) => {
                    e.detail[0].target.find('.dcpDocument__header').hide();
                    if (this.documentCss) {
                        documentComponent.prop('publicMethods').injectCSS(this.documentCss);
                    }

                    this.$(tabContent).find('.documentsList__documentsTabs__tab__content--document').show();
                    this.$(tabContent).find('.documentsList__documentsTabs__tab__content--loading').hide();
                    this.$(tabItem).find('a.tab__document__header__content').prop('href', e.detail[1].url);
                });
                documentComponent.on('actionClick', (e) => {
                    if (e.detail.length > 2 && e.detail[2].options) {
                        if (e.detail[2].eventId === 'document.load') {
                            e.detail[0].preventDefault();
                            const initid = e.detail[2].options[0];
                            const viewid = e.detail[2].options[1];
                            this.addDocument({ initid, viewid });
                        }
                    }
                });
                documentComponent.on('afterSave', (e) => {
                    tab.set('tabId', e.detail[1].initid);
                    tab.set('data.title', e.detail[1].title);
                    tab.set('data.icon', e.detail[1].icon);
                    this.$emit('document-modified', e.detail);
                });
                documentComponent.on('afterDelete', (e) => {
                    this.$emit('document-deleted', e.detail);
                });
            },

            bindNewTabEvents: (tabContent, index) => {
                const $newTab = this.$(tabContent).find('a4-welcome-tab');
                $newTab.on('document-creation', (e) => {
                        const newId = e.detail[0].initid;
                        console.log(newId);
                        const collection = this.collections.find((c) => c.initid === newId);
                        if (collection) {
                            this.openedTabs.splice(index, 1, {
                                tabId: newId,
                                headerTemplate: headerTemplate,
                                contentTemplate: contentTemplate,
                                data: Object.assign({}, {
                                    initid: collection.initid,
                                    viewid: '!defaultCreation',
                                    title: `Création ${collection.html_label}`,
                                    icon: collection.image_url,
                                }),
                            });
                            this.selectDocument(index);
                        }
                    });
                $newTab.on('document-selected', (e) => {
                    this.setDocument(e.detail[0], index);
                });
            },

            configureWelcomeTab: (tabContent, index) => {
                this.privateScope.bindNewTabEvents(tabContent, index);
            },

            // Listen model changes and update view
            bindDataChange: (data) => {
                if (data.bind) {
                    data.bind('change', (e) => {
                        switch (e.action) {
                            // Add new document
                            case 'add':
                                const item = e.items[0];
                                const header = this.$kendo.template(item.headerTemplate)(item.data);
                                const content = this.$kendo.template(item.contentTemplate)(item.data);
                                const tabAdded = {
                                    text: header,
                                    encoded: false,
                                    content: content,
                                };
                                if (e.index === this.openedTabs.length - 1) {
                                    this.tabstrip.append(tabAdded);
                                } else if (e.index === 0) {
                                    this.tabstrip.insertBefore(tabAdded, this.tabstrip.items()[0]);
                                } else {
                                    this.tabstrip.insertAfter(tabAdded, this.tabstrip.items()[e.index - 1]);
                                }

                                this.privateScope
                                    .configureCloseTab(this.tabstrip.items()[e.index]);
                                this.privateScope
                                    .bindDocumentEvents(this.tabstrip.contentElement(e.index),
                                        this.tabstrip.items()[e.index],
                                        e.index);
                                this.privateScope.computeTabstripMargin();
                                if (item.tabId === Constants.WELCOME_TAB_ID
                                    || item.tabId === Constants.NEW_TAB_ID) {
                                    this.privateScope
                                        .configureWelcomeTab(this.tabstrip.contentElement(e.index), e.index);
                                }

                                break;
                            // Remove document
                            case 'remove':
                                if (e.items.length === 1) {
                                    this.tabstrip.remove(e.index);
                                } else if (e.items.length > 1) {
                                    this.tabstrip.remove('li');
                                }

                                if (!this.openedTabs.length) {
                                    this.privateScope.initTabsData();
                                }

                                this.privateScope.computeTabstripMargin();
                                break;
                            // Modify a tab
                            case 'itemchange':
                                const index = this.tabsArray.findIndex((d) => d.tabId === e.items[0].tabId);
                                const props = e.field.split('.');
                                let newValue;
                                const $indexedItem = this.$(this.tabstrip.items()[index]);
                                switch (e.field) {
                                    case 'data.title':
                                        newValue = e.items[0][props[0]][props[1]];
                                        $indexedItem.find('span.tab__document__title').text(newValue);
                                        break;
                                    case 'data.icon':
                                        newValue = e.items[0][props[0]][props[1]];
                                        $indexedItem.find('img.tab__document__icon').prop('src', newValue);
                                        break;
                                }
                                break;
                        }
                        this.tabsListSource.data(this.tabsArray.filter(t => t.tabId !== Constants.NEW_TAB_ID && t.tabId !== Constants.WELCOME_TAB_ID));
                    });
                }
            },
        };
    },

    mounted() {
        const ready = () => {
            this.privateScope.initKendoComponents().then(() => {
                this.$emit('document-tabs-ready', this.$el);
            });
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
            recentConsultationsSource: null,
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
            const index = this.tabsArray.findIndex((d) => d.tabId === document.initid);
            if (index < 0) {
                this.openedTabs.push({
                    tabId: document.initid,
                    headerTemplate: headerTemplate,
                    contentTemplate: contentTemplate,
                    data: Object.assign({}, document),
                });
                this.selectDocument(this.openedTabs.length - 1);
                this.privateScope.setTagToDocument(document);
            } else {
                this.selectDocument(index);
            }
        },

        setDocument(document, position) {
            if (position === undefined) {
                this.addDocument(document);
            } else {
                const index = this.tabsArray.findIndex((d) => d.tabId === document.initid);
                if (index < 0) {
                    this.openedTabs.splice(position, 1, {
                        tabId: document.initid,
                        headerTemplate: headerTemplate,
                        contentTemplate: contentTemplate,
                        data: Object.assign({}, document),
                    });
                    this.selectDocument(position);
                } else {
                    this.selectDocument(index);
                }
            }
        },

        selectDocument(documentId) {
            let index = 0;
            if (typeof documentId === 'number') {
                index = documentId;
            } else if (typeof documentId === 'object') {
                index = this.tabsArray.findIndex((d) => d.tabId === documentId.initid);
                if (index < 0) {
                    index = 0;
                }
            }
            // this.tabslist.select(index);
            this.tabstrip.select(index);
        },

        closeDocument(documentId) {
            let index = -1;
            if (typeof documentId === 'number') {
                index = documentId;
            } else if (typeof documentId === 'object') {
                index = this.tabsArray.findIndex((d) => d.tabId === documentId.initid);
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
