import BaseComponent from '../componentBase';

export default {
    mixins: [BaseComponent],
    props: {
        welcomeMessage: {
            type: String,
            default: '',
        },
        promptMessage: {
            type: String,
            default: '',
        },
        userName: {
            type: String,
            default: '',
        },
        collections: {
            type: String,
            required: true,
        },
    },
    created() {
        this.privateScope = {
            bindNewTabEvents: () => {
                this.$(this.$refs.buttonGroup).find('.documentsList__documentsTabs__welcome__collection__button')
                    .on('click', (e) => {
                        const newId = e.target.dataset.famid;
                        const collection = this.collectionsArray.find((c) => c.initid === newId);
                        if (collection) {
                            this.$emit('document-creation', collection);
                        }
                    });
            },

            createAutocompleteSearch: () => {
                const $input = this.$(this.$refs.documentsSearch);
                const kendoInput = $input.kendoAutoComplete({
                    clearButton: false,
                    autoBind: false,
                    select: (e) => this.$emit('document-selected', e.dataItem.properties),
                    dataTextField: 'properties.title',
                    dataValueField: 'properties.id',
                    template: `<div style="display: flex; align-items: center;">
                                    <img style="margin-right: 1rem" src="#= properties.icon#"/>
                                    <span>#= properties.title#</span>
                               </div>`,
                    serverFiltering: true,
                    noDataTemplate: 'Aucune correspondance',
                    footerTemplate: `<div style="display: flex; 
                                                justify-content: center; 
                                                padding-top: 1rem; 
                                                border-top: 1px solid lightgrey">
                                        <span><strong>#: instance.dataSource.total() #</strong> documents trouvés</span>
                                    </div>`,
                    autoWidth: true,
                    dataSource: {
                        transport: {
                            read: (options) => {
                                const params = {
                                    collections: this.collectionsArray.map(c => c.initid).join(','),
                                    fields: 'document.properties.icon,document.properties.title',
                                    slice: 'all',
                                };
                                if (kendoInput.value()) {
                                    params.filter = kendoInput.value();
                                }

                                this.$http.get('sba/documentsSearch', {
                                    params,
                                }).then(options.success).catch(options.error);
                            },
                        },
                        schema: {
                            data: (response) => response.data.data.documents,
                        },
                    },
                }).data('kendoAutoComplete');
            },

            configureWelcomeTab: () => {
                this.privateScope.bindNewTabEvents();
                this.privateScope
                    .createAutocompleteSearch();
            },

            prepareWelcomeTabData: () => new Promise((resolve, reject) => {
                this.privateScope.sendGetRequest('sba/documentsSearch', {
                    params: {
                        collections: this.collectionsArray.map(c => c.initid).join(','),
                        fields: 'document.properties.title,document.properties.icon,document.properties.state,document.properties.family',
                        slice: '5',
                        utag: 'open_document',
                        iconSize: '24x24c',
                    },
                }, this.$refs.recentConsultLoading).then((response) => {
                    const utags = response.data.data.utags;
                    const documents = response.data.data.documents;
                    const result = documents.map((d) => {
                        d.utag = utags[d.properties.id];
                        return d;
                    });
                    resolve(result);
                }).catch((error) => {
                    reject(error);
                });
            }),

            sendGetRequest: (url, config, DOMelement) => {
                const element = this.$(DOMelement);
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
        };
    },

    computed: {
        collectionsArray() {
            if (this.collections) {
                return JSON.parse(this.collections);
            } else return [];
        },

        translations() {
            return {
                searchPlaceholder: this.$pgettext('WelcomeTab', 'Search a Smart Element'),
                creationLabel: this.$pgettext('WelcomeTab', 'Creation'),
                consultLabel: this.$pgettext('WelcomeTab', 'Consultation'),
                recentConsultLabel: this.$pgettext('WelcomeTab', 'Last Consultations'),
                typeColumnLabel: this.$pgettext('WelcomeTab', 'Type'),
                titleColumnLabel: this.$pgettext('WelcomeTab', 'Title'),
                stepColumnLabel: this.$pgettext('WelcomeTab', 'Step'),
                consultDateColumnLabel: this.$pgettext('WelcomeTab', 'Consultation date'),
                noRecentConsult: this.$pgettext('WelcomeTab', 'No recent consultations'),
            };
        },
    },

    mounted() {
        this.refresh();
        this.privateScope.configureWelcomeTab();
    },

    data() {
        return {
            lastConsultations: [],
        };
    },

    methods: {
        refresh() {
            this.privateScope.prepareWelcomeTabData().then((result) => {
                this.lastConsultations = result;
            });
        },

        onRecentDocumentClick(document) {
            this.$emit('document-selected', document);
        },

        onRemoveSearch() {
            this.$(this.$refs.documentsSearch).data('kendoAutoComplete').value('');
        },

        getStateTag(color) {
            return {
                'background-color': color,
                width: '8px',
                height: '8px',
                'border-radius': '25%',
                margin: '0 5px 0 0',
                display: 'inline-block',
            };
        },

        getFormattedDate(isoDate) {
            const date = new Date(isoDate);
            return date.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        },
    },
};
