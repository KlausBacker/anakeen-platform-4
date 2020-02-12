<template>
  <div>
    <span
      class="smart-element-grid-cell-content--color"
      :style="{ backgroundColor: fieldValue.value }"
    ></span>
    <span class="smart-element-grid-cell-content--colorValue">{{ fieldValue.displayValue }}</span>
  </div>
</template>
<style lang="scss" scoped>
div {
  display: flex;
  align-items: center;

  .smart-element-grid-cell-content--color {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
  }

  .smart-element-grid-cell-content--colorValue {
    margin-left: 0.25rem;
  }
}
</style>

<script lang="ts">
import { Component, Mixins } from "vue-property-decorator";
import AnkGridCellMixin from "../AnkGridCellMixin";

@Component({
  name: "ank-grid-cell-color"
})
export default class AnkGridCellColor extends Mixins(AnkGridCellMixin) {}
</script>
