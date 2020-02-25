<template>
  <div>
    {{ property ? dataItem.properties[field] : fieldValue.displayValue }}
  </div>
</template>

<script lang="ts">
import { Component, Mixins } from "vue-property-decorator";
import AnkGridCellMixin from "../AnkGridCellMixin";

@Component({
  name: "ank-grid-cell-text"
})
export default class AnkGridCellText extends Mixins(AnkGridCellMixin) {}
</script>
