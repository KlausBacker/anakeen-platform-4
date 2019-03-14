<template>
  <kendo-splitter ref="ankSplitter" class="ank-splitter splitter-wrapper">
    <slot name="left"></slot>
    <div class="splitter-wrapper-right-content">
      <div class="splitter-empty" v-if="splitterEmpty">
        <div class="splitter-empty-content">
          <slot class="splitter-empty-icon" name="emptyIcon">
            <span
              class="k-icon k-i-information splitter-empty-icon splitter-default-empty-icon"
            ></span>
          </slot>
          <slot class="splitter-empty-text" name="emptyText">
            <span class="splitter-empty-text splitter-default-empty-text">
              Select an element to open
            </span>
          </slot>
        </div>
      </div>
      <slot v-else name="right"></slot>
    </div>
  </kendo-splitter>
</template>
<style scoped lang="scss">
@import "./Splitter.scss";
</style>
<script lang="ts" src="./Splitter.component.ts"></script>
