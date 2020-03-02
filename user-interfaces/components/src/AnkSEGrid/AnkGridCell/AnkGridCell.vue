<template>
  <component
    :is="tag"
    :class="
      `smart-element-grid-cell smart-element-grid-cell--${columnConfig.smartType} smart-element-grid-cell--${field}`
    "
  >
    <!-- Inexistent case -->
    <div class="smart-element-grid-cell-content" :style="{ maxHeight: gridComponent.maxRowHeight }" v-if="isInexistent">
      <span class="smart-element-grid-cell-content--empty">
        <slot name="inexistentCell">{{ gridComponent.inexistentCellText }}</slot>
      </span>
    </div>

    <!-- Empty case -->
    <div class="smart-element-grid-cell-content" :style="{ maxHeight: gridComponent.maxRowHeight }" v-else-if="isEmpty">
      <span class="smart-element-grid-cell-content--empty">
        <slot name="emptyCell">{{ gridComponent.emptyCellText }}</slot>
      </span>
    </div>
    <!-- Property display -->
    <div
      v-else-if="columnConfig.property"
      class="smart-element-grid-cell-content"
      :style="{ maxHeight: gridComponent.maxRowHeight }"
    >
      <div v-if="field === 'state'" class="smart-element-grid-cell__state">
        <span>{{ cellValue.displayValue }}</span>
        <span class="smart-element-grid-cell__state--color" :style="{ backgroundColor: cellValue.color }"></span>
      </div>
      <span v-else>{{ cellValue }}</span>
    </div>
    <!-- Abstract display -->
    <div
      v-else-if="columnConfig.abstract"
      class="smart-element-grid-cell-content"
      :style="{ maxHeight: gridComponent.maxRowHeight }"
    >
      {{ cellValue.displayValue || cellValue.value }}
    </div>

    <!-- Smart Field display -->

    <!-- Multiple case -->
    <div
      v-else-if="isMultiple"
      class="smart-element-grid-cell-content"
      :style="{ maxHeight: gridComponent.maxRowHeight * 2 }"
    >
      <div v-for="(fValue, index) in cellValue" class="smart-element-grid-cell-content-multiple-row" :key="index">
        <div class="smart-element-grid-cell-content-multiple-row-content">
          <div
            v-for="(sublevel, subindex) in getSublevel(fValue)"
            :key="`sublevel-${subindex}`"
            class="smart-element-grid-cell-content-multiple-col"
          >
            <component
              :is="componentName"
              class="smart-element-grid-cell-content--value"
              v-bind="$props"
              :field-value="sublevel"
            />
            <span
              v-if="subindex < getSublevel(fValue).length - 1"
              class="smart-element-grid-cell-content-multiple-col-separator"
              >,&nbsp;</span
            >
          </div>
        </div>
        <hr v-if="index < cellValue.length - 1" class="smart-element-grid-cell-content-multiple-row-separator" />
      </div>
    </div>

    <!-- Simple case -->
    <div class="smart-element-grid-cell-content" :style="{ maxHeight: gridComponent.maxRowHeight }" v-else>
      <component
        :is="componentName"
        class="smart-element-grid-cell-content--value"
        v-bind="$props"
        :field-value="cellValue"
      />
    </div>
  </component>
</template>

<!-- CSS to this component only -->
<style lang="scss" scoped>
@import "./AnkGridCell.scss";
</style>

<script src="./AnkGridCell.component.ts" lang="ts"></script>
