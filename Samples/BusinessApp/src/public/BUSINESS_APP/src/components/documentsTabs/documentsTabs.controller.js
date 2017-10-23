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
                console.log(tabContent);
                const documentInstance = this.$(tabContent).find('a4-document');
                if (documentInstance && documentInstance.length) {
                    console.log(documentInstance[0]);
                    documentInstance[0].addEventListener('actionClick', () => {
                        console.log('coucou');
                    });
                }
            }
        };
    },

    mounted() {
        this.tabstrip = this.$(this.$refs.tabstrip).kendoTabStrip({
            animation: false,
            scrollable: true,
        }).data('kendoTabStrip');
        window.addEventListener('resize', (event) => {
            this.tabstrip.tabGroup.resize();
        });
    },

    watch: {
        value(newValue) {
            newValue.forEach((doc) => {

            });
            if (newValue.length) {
                const lastId = newValue.length - 1;
                const lastData = newValue[lastId];
                this.addTab(lastData);
            }
        },
    },

    data: {
        openedTabs: [],
    },

    props: {
        value: {
            type: Array,
            default: () => {
                return [];
            },
        },
        closable: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
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
            return `<a4-document ${option}></a4-document>`;
        },

        addTab(data) {
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
            const item = this.tabstrip.items()[index];
            this.tabstrip.remove(index);
            if (this.tabstrip.items().length > 0 && this.$(item).hasClass('k-state-active')) {
                this.tabstrip.select(0);
            }
        },
    },
};
