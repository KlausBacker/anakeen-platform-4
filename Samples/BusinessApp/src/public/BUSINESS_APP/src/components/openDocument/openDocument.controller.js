export default {
  mounted() {
    document.addEventListener('DOMContentLoaded', (event) => {
      const store = document.getElementById('a4-store');
      store.addEventListener('store-change', (event) => {
        const storeData = event.detail && event.detail.length ? event.detail[0] : null;
        this.onStoreChange(storeData);
      });
    });
    this.sendGetRequest('sba/collections')
      .then((response) => {
        this.updateKendoData(response.data.data.sample.collections);
      });
    this.initKendo();
  },

  data() {
    return {
      urlDocument: null,
      openedDocuments: [],
      activeTab: null,
      newActionsSource: null,
    };
  },

  methods: {
    onStoreChange(storeData) {
      if (storeData) {
        switch (storeData.type) {
          case 'OPEN_DOCUMENT':
            this.openedDocuments.push(storeData.data);
            this.activeTab = storeData.data.initid;
            break;
        }
      }
    },

    initKendo() {
      this.newActionsSource = new this.$kendo.data.DataSource({
        data: [],
      });
      this.$(this.$refs.newActionButton).kendoDropDownList({
        dataSource: this.newActionsSource,
        dataTextField: 'html_label',
        dataValueField: 'ref',
        animation: false,
        change: this.onClickNewAction,
        valueTemplate: '<span class="documentsList__openDocuments__button__label">Nouveau</span>',
        template: this.$kendo.template(
          '<span class="documentsList__openDocuments__button__option">' +
          '<img class="documentsList__openDocuments__button__option__img" src="#: image_url#" alt="#: html_label# image"/>' +
          '<span class="documentsList__openDocuments__button__option__label">#= html_label#</span>' +
          '</span>'),
      });
      this.$(this.$refs.tabStrip).kendoTabStrip();
    },

    onClickNewAction() {

    },

    updateKendoData(data) {
      this.newActionsSource.data(data);
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
  },
};
