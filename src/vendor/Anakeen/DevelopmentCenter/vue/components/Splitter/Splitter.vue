<template>
    <kendo-splitter ref="ankSplitter" orientation="horizontal" style="height:100%; background-color: #DEDEDE" :panes="panes">
        <slot name="left"></slot>
        <slot name="right"></slot>
    </kendo-splitter>
</template>
<style>
</style>
<script>
  import Vue from "vue";
  import { Splitter, LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";

  Vue.use(LayoutInstaller);
  export default {
    name: "ank-splitter",
    components: {
      "ank-spliter": Splitter
    },
    props: {
      panes: {
        type: Array,
        default: () => []
      },
      localStorageKey: {
        type: String,
        default: ""
      }
    },
    mounted() {
      if (this.localStorageKey) {
        const savedSize = window.localStorage.getItem(this.localStorageKey);
        if (savedSize) {
          this.$refs.ankSplitter.kendoWidget().size(".k-pane:first", savedSize);
        }
        this.$refs.ankSplitter
          .kendoWidget()
          .bind(
            "resize",
            this.onSplitterResize
          );
      }
    },
    methods: {
      onSplitterResize() {
        window.localStorage.setItem(
          this.localStorageKey,
          this.$refs.ankSplitter.kendoWidget().size(".k-pane:first")
        );
      }
    }
  };

</script>