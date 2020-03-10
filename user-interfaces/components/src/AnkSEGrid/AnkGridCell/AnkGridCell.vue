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
      <div v-if="field === 'state'" class="smart-element-grid-cell-state">
        <span class="smart-element-grid-cell-state--color" :style="{ backgroundColor: cellValue.color }"></span>
        <span>{{ cellValue.displayValue }}</span>
      </div>
      <div v-else-if="field === 'title'" class="smart-element-grid-cell--title">
        <div class="smart-element-grid-cell--title-content">
          <img
            class="smart-element-grid-cell--title-icon"
            v-if="dataItem.properties.icon"
            :src="dataItem.properties.icon"
          />
          <span class="smart-element-grid-cell--title-text">{{ cellValue }}</span>
        </div>
      </div>
      <span v-else>{{ cellValue }}</span>
    </div>
    <!-- Abstract display -->
    <div
      v-else-if="columnConfig.abstract"
      class="smart-element-grid-cell-content"
      :style="{ maxHeight: gridComponent.maxRowHeight }"
    >
      {{ cellValue.displayValue || cellValue.value || cellValue }}
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
