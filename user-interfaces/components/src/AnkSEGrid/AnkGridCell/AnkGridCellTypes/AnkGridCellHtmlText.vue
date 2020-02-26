<template>
  <div v-html="fieldValue.displayValue"></div>
</template>

<script lang="ts">
import { Component, Mixins } from "vue-property-decorator";
import AnkGridCellMixin from "../AnkGridCellMixin";

@Component({
  name: "ank-grid-cell-htmltext"
})
export default class AnkGridCellHtmlText extends Mixins(AnkGridCellMixin) {}
</script>
