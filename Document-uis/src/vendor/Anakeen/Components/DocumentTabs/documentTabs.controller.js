// jscs:disable requirePaddingNewLinesBeforeLineComments
import contentTemplate from './documentTabsContent.template.kd';
import headerTemplate from './documentTabsHeader.template.kd';
import welcomeTemplate from './documentTabsWelcome.template.kd';
import abstractAnakeenComponent from '../componentBase';

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
            // Init the model and view kendo element
            initKendoComponents: () => {
                this.tabstripElement = this.$(this.$refs.tabstrip).kendoTabStrip({
                    animation: false,
                });
                this.openedTabs = new this.$kendo.data.ObservableArray([]);
                this.privateScope.bindDataChange(this.openedTabs);
                this.tabsListElement = this.$(this.$refs.tabsList).kendoDropDownList({
                    animation: false,
                    dataSource: this.tabsArray,
                    template: this.$kendo.template(headerTemplate),
                    valueTemplate: this.$kendo.template('<i class="material-icons">menu</span>'),
                    autoWidth: true,
                    select: this.privateScope.onClickTabList,
                });
                this.tabslist.list.addClass('documentsList__documentsTabs__tabsList__list');
                this.privateScope.sendGetRequest('sba/collections')
                    .then((response) => {
                        this.collections = response.data.data.sample.collections;
                        this.privateScope.initTabs();
                    });
                this.$(window).resize(() => {
                    this.privateScope.resizeComponents();
                });
                this.privateScope.resizeComponents();
            },

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

            initTabs: () => {
                this.openedTabs.push({
                    tabId: 'welcome_tab',
                    headerTemplate: `<span class="tab__document__header__content">
                                        <img src="api/v1/images/assets/original/BA.png" class="app_logo" style="width:auto; height:2rem; padding-right: 1rem;"/> 
                                        BIENVENUE
                                        <span class="tab__new__button"><i class="material-icons">add</i></span>
                                     </span>`,
                    contentTemplate: welcomeTemplate,
                    data: {
                        user: 'Anakeen',
                        welcomeMessage: 'bienvenue sur  Business App.<br/> Que voulez-vous faire ?',
                        collections: this.collections,
                    },
                });
                /*this.$('.documentsList__documentsTabs__welcome__collection__button').on('click', (e) => {
                    const id = e.target.dataset.famid;
                    const coll = this.collections.find((c) => c.initid === id);
                    this.openedTabs.push({
                        tabId: coll.initid,
                        headerTemplate,
                        contentTemplate,
                        data: {
                            initid: coll.initid,
                            viewid: '!defaultCreation',
                            title: coll.html_label,
                            icon: coll.image_url,
                        },
                    });
                    this.selectDocument(this.openedTabs.length - 1);
                });*/

                this.selectDocument(0);
            },

            onClickNewTabButton: (e) => {
                this.openedTabs.push({
                    tabId: 'new_tab',
                    headerTemplate: `<span class="tab__document__header__content">
                                        <i class="material-icons">crop_square</i>
                                        NOUVEL ONGLET
                                        <span class="tab__new__button"><i class="material-icons">add</i></span>
                                     </span>`,
                    contentTemplate: welcomeTemplate,
                    data: {
                        user: 'Anakeen',
                        welcomeMessage: '<br/>Que voulez-vous faire ?',
                        collections: this.collections,
                    },
                });
                this.selectDocument(this.openedTabs.length - 1);
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
                    $tab.find('.tab__document__header__content').append('<span data-type="remove" class="k-link"><span class="k-icon k-i-x"></span></span>');
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
                    next.css('right', `${paginatorWidth}px`);
                    prev.css('right', `${paginatorWidth + nextWidth}px`);
                    marginRight = marginRight + prevWidth + nextWidth;
                }

                this.tabstrip.tabGroup.css('margin-right', `calc(${marginRight}px + ${tabMargin})`);
            },

            // Bind documents events to tabs system
            bindDocumentEvents: (tabContent, index) => {
                const tab = this.openedTabs[index];
                const documentComponent = this.$(tabContent).find('a4-document');
                documentComponent.on('ready', (e) => {
                    e.detail[0].target.find('.dcpDocument__header').hide();
                    if (this.documentCss) {
                        documentComponent.prop('publicMethods').injectCSS(this.documentCss);
                    }

                    tab.set('title', '');
                    tab.set('icon', '');
                    tab.set('url', '');
                    tab.set('title', e.detail[1].title);
                    tab.set('icon', e.detail[1].icon);
                    tab.set('url', e.detail[1].url);
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
                                this.tabstrip.append({
                                    text: header,
                                    encoded: false,
                                    content: content,
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
                                // const index = this.tabsArray.findIndex((d) => d.initid === e.items[0].initid);
                                // const newValue = e.items[0][e.field];
                                // const $indexedItem = this.$(this.tabstrip.items()[index]);
                                // switch (e.field) {
                                //     case 'title':
                                //         $indexedItem.find('.tab__document__title').text(newValue);
                                //         break;
                                //     case 'icon':
                                //         $indexedItem.find('.tab__document__icon')
                                //             .replaceWith(`<img class="tab__document__icon" src="${newValue}"/>`);
                                //         break;
                                //     case 'url':
                                //         $indexedItem.find('.tab__document__header__content').prop('href', newValue);
                                //         break;
                                // }

                                break;
                        }
                    });
                }
            },
        };
    },

    mounted() {
        this.privateScope.initKendoComponents();
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
            const index = this.tabsArray.findIndex((d) => d.tabId === document.initid);
            if (index < 0) {
                this.openedTabs.push({
                    tabId: document.initid,
                    headerTemplate: headerTemplate,
                    contentTemplate: contentTemplate,
                    data: Object.assign({}, document, { onClickNewTab: this.privateScope.onClickNewTabButton }),
                });
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