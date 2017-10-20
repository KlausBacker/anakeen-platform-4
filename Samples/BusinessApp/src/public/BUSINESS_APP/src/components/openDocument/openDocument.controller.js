import A4Tabs from '@/documentsTabs/documentsTabs.vue';
import A4Document from '@~/Document/Document.vue';
// import A4OtherTabs from '@/otherDocumentsTabs/documentsTabs.vue';
export default {
  components: {
    "a4-documents-tabs": A4Tabs,
    "a4-vue-document": A4Document,
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
      openedDocuments : [],
    };
  },

  computed: {
    emptyData() {
      return !this.openedDocuments.length;
    },
    tabstrip() {
      return this.tabstripEl ? this.tabstripEl.data('kendoTabStrip') : null;
    }
  },

  methods: {
    onStoreChange(storeData) {
      if (storeData) {
        switch (storeData.type) {
          case 'OPEN_DOCUMENT':
            this.openedDocuments.push(storeData.data);
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
        resolve();
      });
    },

    onClickNewAction(e) {
        if (e.item.getAttribute('initid') && e.item.getAttribute('html_label')) {
            this.openedDocuments.push({
                title: `Création ${e.item.getAttribute('html_label')}`,
                initid: e.item.getAttribute('initid'),
                viewId: '!defaultCreation',
            });
        }
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

    onDocumentActionClick(event, document, options) {
      console.log(event, document, options);
    },

    onAttributeAnchorClick(event, document ,options) {
      console.log(event, document, options);
    }
  },
};
