<template>
  <component
    :is="tag"
    :class="
      `smart-element-grid-cell smart-element-grid-cell--${columnConfig.smartType} smart-element-grid-cell--${field}`
    "
  >
    <!-- Inexistent case -->
    <div class="smart-element-grid-cell-content" :style="{maxHeight: gridComponent.maxRowHeight }" v-if="isInexistent">
      <span class="smart-element-grid-cell-content--empty">
        <slot name="inexistentCell">{{ gridComponent.inexistentCellText }}</slot>
      </span>
    </div>

    <!-- Empty case -->
    <div class="smart-element-grid-cell-content" :style="{maxHeight: gridComponent.maxRowHeight }" v-else-if="isEmpty">
      <span class="smart-element-grid-cell-content--empty">
        <slot name="emptyCell">{{ gridComponent.emptyCellText }}</slot>
      </span>
    </div>
    <!-- Property display -->
    <div class="smart-element-grid-cell-content" :style="{maxHeight: gridComponent.maxRowHeight }" v-else-if="columnConfig.property">
      {{ cellValue }}
    </div>

    <!-- Abstract display -->
    <div class="smart-element-grid-cell-content" :style="{maxHeight: gridComponent.maxRowHeight }" v-else-if="columnConfig.abstract">
      {{ cellValue.displayValue || cellValue.value }}
    </div>

    <!-- Smart Field display -->

    <!-- Multiple case -->
    <div v-else-if="isMultiple" class="smart-element-grid-cell-content" :style="{maxHeight: gridComponent.maxRowHeight }">
      <div class="smart-element-grid-cell-content-multiple-row" v-for="(fValue, index) in cellValue" :key="index">
        <div class="smart-element-grid-cell-content-multiple-row-content">
          <div
            class="smart-element-grid-cell-content-multiple-col"
            v-for="(sublevel, subindex) in getSublevel(fValue)"
            :key="`sublevel-${subindex}`"
          >
            <component
              class="smart-element-grid-cell-content--value"
              :is="componentName"
              v-bind="$props"
              :fieldValue="sublevel"
            ></component>
            <span
              class="smart-element-grid-cell-content-multiple-col-separator"
              v-if="subindex < getSublevel(fValue).length - 1"
              >,&nbsp;</span
            >
          </div>
        </div>
        <hr class="smart-element-grid-cell-content-multiple-row-separator" v-if="index < cellValue.length - 1" />
      </div>
    </div>

    <!-- Simple case -->
    <div class="smart-element-grid-cell-content" :style="{maxHeight: gridComponent.maxRowHeight }" v-else>
      <component
        class="smart-element-grid-cell-content--value"
        :is="componentName"
        v-bind="$props"
        :fieldValue="cellValue"
      ></component>
    </div>
  </component>
</template>

<!-- CSS to this component only -->
<style lang="scss" scoped>
@import "./AnkGridCell.scss";
</style>

<script src="./AnkGridCell.component.ts" lang="ts"></script>
