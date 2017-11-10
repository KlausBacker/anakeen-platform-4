import mixin from '../componentBase';
export default {
    mixins: [mixin],
    name: 'a4-create-button',
    props: {
        familiesListUrl: {
            type: String,
            default: '',
        },
    },

    data() {
        return {
            createButtonElement: null,
        };
    },

    computed: {
        createButton() {
            return this.createButtonElement.data('kendoMenu');
        },
    },

    created() {
        this.privateScope = {
            initKendoComponents: () => {
                this.createButtonElement = this.$(this.$refs.createButton).kendoMenu({
                    dataSource: {
                        text: 'Nouveau',
                        cssClass: 'documentsList__createButton__button',
                        items: [],
                    },
                    openOnClick: true,
                });
            },

            sendGetRequest: (url) => {
                return new Promise((resolve, reject) => {
                    this.$http.get(url)
                        .then((response) => {
                            resolve(response);
                        }).catch((error) => {
                            reject(error);
                        });
                });
            },

            onSelectNewItem: (e) => {
                const initid = this.$(e.target).closest('.documentsList__createButton__button__item').attr('family-id');
                console.log(initid);
                this.$emit('create-document', { initid, viewid: '!defaultCreation' });
            },

            updateMenuItems: (data) => {
                const items = data.map((c) => {
                    return {
                        text: c.html_label,
                        cssClass: 'documentsList__createButton__button__item',
                        imageUrl: c.image_url,
                        select: this.privateScope.onSelectNewItem,
                        attr: {
                            'family-id': c.initid,
                        },
                    };
                });
                this.createButton.append(items, '.documentsList__createButton__button');
            },
        };
    },

    mounted() {
        this.privateScope.initKendoComponents();
        if (this.familiesListUrl) {
            this.privateScope.sendGetRequest(this.familiesListUrl)
                .then((result) => {
                    this.privateScope.updateMenuItems(result.data.data.sample.collections);
                }).catch((e) => {
                    console.error(e);
                });
        }
    },

    methods: {
        sayHello() {
            console.log('Hello');
        },
    },
};
