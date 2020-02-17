<template>
  <td
    :class="
      `smart-element-grid-cell smart-element-grid-cell--${columnConfig.smartType} smart-element-grid-cell--${field}`
    "
  >
    <!-- Inexistent case -->
    <div class="smart-element-grid-cell-content" v-if="isInexistent">
      <span class="smart-element-grid-cell-content--empty">
        <slot name="inexistentCell">{{ gridComponent.inexistentCellText }}</slot>
      </span>
    </div>

    <!-- Empty case -->
    <div class="smart-element-grid-cell-content" v-else-if="isEmpty">
      <span class="smart-element-grid-cell-content--empty">
        <slot name="emptyCell">{{ gridComponent.emptyCellText }}</slot>
      </span>
    </div>
    <!-- Property display -->
    <div class="smart-element-grid-cell-content" v-else-if="columnConfig.property">
      {{ dataItem.properties[field] }}
    </div>

    <!-- Abstract display -->
    <div class="smart-element-grid-cell-content" v-else-if="columnConfig.abstract">
      {{ dataItem.abstract[field].displayValue }}
    </div>

    <!-- Smart Field display -->

    <!-- Multiple case -->
    <div
      v-else-if="columnConfig.multiple || Array.isArray(dataItem.attributes[field])"
      class="smart-element-grid-cell-content"
    >
      <div
        class="smart-element-grid-cell-content-multiple-row"
        v-for="(fieldValue, index) in dataItem.attributes[field]"
        :key="index"
      >
        <div class="smart-element-grid-cell-content-multiple-row-content">
          <div
            class="smart-element-grid-cell-content-multiple-col"
            v-for="(sublevel, subindex) in getSublevel(fieldValue)"
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
              v-if="subindex < getSublevel(fieldValue).length - 1"
              >,&nbsp;</span
            >
          </div>
        </div>
        <hr
          class="smart-element-grid-cell-content-multiple-row-separator"
          v-if="index < dataItem.attributes[field].length - 1"
        />
      </div>
    </div>

    <!-- Simple case -->
    <div class="smart-element-grid-cell-content" v-else>
      <component
        class="smart-element-grid-cell-content--value"
        :is="componentName"
        v-bind="$props"
        :fieldValue="dataItem.attributes[field]"
      ></component>
    </div>
  </td>
</template>

<!-- CSS to this component only -->
<style lang="scss" scoped>
@import "./AnkGridCell.scss";
</style>

<script src="./AnkGridCell.component.ts" lang="ts"></script>
