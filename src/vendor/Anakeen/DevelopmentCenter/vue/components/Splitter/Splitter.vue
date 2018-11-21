<template>
    <kendo-splitter ref="ankSplitter" orientation="horizontal" style="height:100%" :panes="panes">
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
      }
    }
  };

</script>