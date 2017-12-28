import mixin from '../componentBase';

export default {
    mixins: [mixin],
    mounted() {
        this.$kendo.ui.progress(this.$(this.$refs.wrapper), true);
        const ready = () => {
            this.initKendo();
            this.sendGetRequest('/sba/collections')
                .then((response) => {
                    this.collections = response.data.data.collections;
                    this.currentUser = response.data.data.user;
                    this.updateKendoData();
                    const listView = this.$(this.$refs.listView).data('kendoListView');
                    listView.select(listView.element.children().first());
                });
            this.$kendo.ui.progress(this.$(this.$refs.wrapper), false);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },

    data() {
        return {
            showCollections: true,
            selectedCollection: null,
            collections: [],
            currentUser: null,
            dataSources: null,
            buttons: [
                /*{
                  id: 'notif',
                  icon: 'fa fa-bell',
                  title: 'Notifications',
                },
                /*{
                  id: 'settings',
                  icon: 'fa fa-cog',
                  title: 'Paramètres',
                },
                {
                  id: 'state',
                  icon: 'fa fa-refresh',
                  title: 'Synchronisé'
                },*/
                {
                    id: 'disconnect',
                    icon: 'fa fa-power-off',
                    title: this.$pgettext('CollectionsList', 'Logout'),
                    click: () => {
                        window.location.href = '?app=CORE&action=LOGOUT';
                    },
                },
            ],
        };
    },

    computed: {
        userInitial() {
            if (this.currentUser) {
                const fullName = `${this.currentUser.firstName} ${this.currentUser.lastName}`;
                const words = fullName.split(' ');
                let initials = '';
                if (words.length >= 2) {
                    for (let i = 0; i < 2; i++) {
                        initials += words[i].trim().charAt(0).toUpperCase();
                    }

                    return initials;
                } else if (words.length) {
                    return words[0].trim().substring(0, 2);
                } else {
                    return '';
                }
            }
        },

        userFullName() {
            if (this.currentUser) {
                return `${this.currentUser.firstName} ${this.currentUser.lastName}`;
            } else {
                return '';
            }
        },

        seeReporting() {
            if (!this.currentUser) {
                return false;
            }

            if (this.currentUser.roles && this.currentUser.roles.length) {
                const role = this.currentUser.roles.findIndex((r) =>r.lastname.startsWith('Directeur') ||
                   r.lastname.startsWith('Comptable'));
                return (role > -1);
            }

            return false;
        },

        isDirecteur() {
            if (!this.currentUser) {
                return false;
            }

            if (this.currentUser.roles && this.currentUser.roles.length) {
                const role = this.currentUser.roles.findIndex((r) =>r.lastname.startsWith('Directeur'));
                return (role > -1);
            }

            return false;
        },

        isComptable() {
            if (!this.currentUser) {
                return false;
            }

            if (this.currentUser.roles && this.currentUser.roles.length) {
                const role = this.currentUser.roles.findIndex((r) => r.lastname.startsWith('Comptable'));
                return (role > -1);
            }

            return false;
        },
    },

    methods: {
        onToggleCollections() {
            if (this.showCollections) {
                this.closeCollections();
            } else {
                this.openCollections();
            }
        },

        openCollections() {
            this.showCollections = true;
            this.$emit('open');
        },

        closeCollections() {
            this.showCollections = false;
            this.$emit('close');
        },

        selectCollection(c) {
            this.$emit('collection-selected', c);
            if (this.showCollections) {
                this.closeCollections();
            }
        },

        initKendo() {
            this.dataSource = new this.$kendo.data.DataSource({
                data: [],
                pageSize: 10,
            });

            this.$(this.$refs.listView).kendoListView({
                dataSource: this.dataSource,
                template: this.$kendo.template('<div class="documentsList__collectionCard">' +
                    '<div class="documentsList__collectionCard__body">' +
                    '<div class="documentsList__collectionCard__heading">' +
                    '<div class="documentsList__collectionCard__heading__content__icon">' +
                    '<img class="documentsList__collectionCard__heading__content__icon__img" src="#: image_url#"  alt="#: html_label# image"/></div>' +
                    '<span class="documentsList__collectionCard__heading__content__label">#:html_label#</span>' +
                    '<span class=".documentsList__documentCard__heading__state"></span>' +
                    '</div></div></div>'),
                selectable: 'single',
                change: this.onSelectItemList,
            });

            this.updateKendoData();
        },

        updateKendoData() {
            this.dataSource.data(this.collections);
        },

        onSelectItemList() {
            const data = this.dataSource.view();
            const listView = this.$(this.$refs.listView).data('kendoListView');
            const selected = this.$.map(listView.select(), item => data[this.$(item).index()]);
            this.selectCollection(selected[0]);
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

        openAccount() {
            // Open user account
        },

        onClickToValidate() {
            this.selectCollection({
                        html_label: 'Notes de frais à valider',
                        ref: 'BA_FEES_TO_VALIDATE',
                        initid: 'BA_FEES_TO_VALIDATE',
                        image_url: 'api/v1/images/assets/sizes/24x24c/BA_Fees_to_action.png',
                    });
        },
    },
};
