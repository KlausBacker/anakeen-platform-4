<template>
    <div class="smart-element-view">
        <div v-if="isReady" class="smart-element-view-toolbar">
            <div>
                Title : <b>{{element.title}}</b>
            </div>
            <div>
                Id : <b>{{element.initid}}</b>
            </div>
            <button class="k-button k-button-icon" @click="onDetachElement">
                <i class="k-icon k-i-hyperlink-open"></i>
            </button>

        </div>
        <ank-smart-element ref="smartElement" @ready="onReady" class="smart-element" :initid="initid" :viewId="viewId"></ank-smart-element>
    </div>
</template>

<script>
  import { AnkSmartElement } from "@anakeen/ank-components";
  export default {
    name: "ElementView",
    components: {
      AnkSmartElement
    },
    props: ["initid", "viewId"],
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
    data() {
      return {
        element: null
      }
    },
    mounted() {
      kendo.ui.progress(this.$(this.$el), true);
    },
    computed: {
      isReady() {
        return !!this.element;
      }
    },
    devCenterRefreshData() {
      if (this.$refs.smartElement) {
        this.$refs.smartElement.fetchSmartElement(this.$refs.smartElement.getInitialData);
      }
    },
    methods: {
      onDetachElement() {
        if (window.open) {
          if (this.viewId) {
            window.open(`/api/v2/documents/${this.initid}/views/${this.viewId}.html`);
          } else {
            window.open(`/api/v2/documents/${this.initid}.html`);
          }
        }
      },
      onReady(event, element) {
        this.element = element;
        this.element.name = this.$refs.smartElement.getProperty("name");
        this.$(event.target).find("nav.dcpDocument__menu").css("display", "none");
        kendo.ui.progress(this.$(this.$el), false);
      }
    }
  }
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
            background: #FAFAFA;
        }
    }
</style>