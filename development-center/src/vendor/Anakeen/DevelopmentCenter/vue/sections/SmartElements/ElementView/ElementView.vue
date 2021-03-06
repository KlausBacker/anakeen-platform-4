<template>
  <div class="smart-element-view">
    <div v-if="isReady && !errorMessage" class="smart-element-view-toolbar">
      <div>
        Title : <b>{{ element.title }}</b>
      </div>
      <div>
        Id : <b>{{ element.initid }}</b>
      </div>
      <button class="k-button k-button-icon" @click="onDetachElement">
        <i class="k-icon k-i-hyperlink-open"></i>
      </button>
    </div>
    <ank-smart-element
      v-show="!errorMessage"
      ref="smartElement"
      :auto-unload="false"
      class="smart-element"
      :initid="initid"
      :view-id="viewId"
      @ready="onReady"
      @displayError="onShowError"
      @internalComponentError="onShowError"
      @actionClick="onActionClick"
    ></ank-smart-element>
    <div v-show="errorMessage" class="smart-element-error-view">
      <div class="smart-element-error-content">
        <i class="material-icons smart-element-error-icon">error_outline</i>
        <span class="smart-element-error-text">{{ errorMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
export default {
  name: "ElementView",
  components: { "ank-smart-element": () => AnkSmartElement },
  props: ["initid", "viewId"],
  data() {
    return {
      element: null,
      errorMessage: ""
    };
  },
  computed: {
    isReady() {
      return !!this.element;
    }
  },
  watch: {
    initid(newValue) {
      if (this.$refs.smartElement) {
        this.$refs.smartElement.fetchSmartElement({ initid: newValue, viewId: this.viewId });
      }
    },
    viewId() {
      if (this.$refs.smartElement) {
        this.$refs.smartElement.fetchSmartElement({ initid: newValue, viewId: this.viewId });
      }
    }
  },
  mounted() {
    kendo.ui.progress(this.$(this.$el), true);
  },
  beforeRouteEnter(to, from, next) {
    next(vueInstance => {
      vueInstance.errorMessage = "";
    });
  },
  beforeRouteUpdate(to, from, next) {
    this.errorMessage = "";
    next();
  },
  devCenterRefreshData() {
    if (this.$refs.smartElement) {
      this.$refs.smartElement.fetchSmartElement(this.$refs.smartElement.initialData);
    }
  },
  methods: {
    onDetachElement() {
      if (window.open) {
        if (this.viewId) {
          window.open(`/api/v2/smart-elements/${this.initid}/views/${this.viewId}.html`);
        } else {
          window.open(`/api/v2/smart-elements/${this.initid}.html`);
        }
      }
    },
    onReady(event, element) {
      this.errorMessage = "";
      this.element = element;
      this.element.name = this.$refs.smartElement.getProperty("name");
      this.$(event.target)
        .find("nav.dcpDocument__menu")
        .css("display", "none");
      kendo.ui.progress(this.$(this.$el), false);
    },
    onActionClick(event, element, action) {
      event.preventDefault();
      const id = action.options[0];
      if (id) {
        const url = `/devel/smartElements/${id}/view?filters=${this.$.param({ id })}`;
        this.$ankHubRouter.internal.navigate(url, true);
        this.$ankHubRouter.external.navigate(url, true);
      }
    },
    onShowError(event, element, error) {
      this.errorMessage = error.message;
      kendo.ui.progress(this.$(this.$el), false);
    }
  }
};
</script>

<style scoped lang="scss">
.smart-element-view {
  display: flex;
  flex-direction: column;
  .smart-element {
    flex: 1;
  }
  .smart-element-view-toolbar {
    padding: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fafafa;
  }

  .smart-element-error-view {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;

    .smart-element-error-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      color: #555;
      max-width: 75%;

      .smart-element-error-icon {
        font-size: 13rem;
      }

      .smart-element-error-text {
        font-size: 2rem;
      }
    }
  }
}
</style>
