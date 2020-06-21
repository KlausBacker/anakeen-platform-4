<template>
  <div class="test-smart-criteria-examples">
    <div
      v-for="(example, index) in examples"
      class="example"
      :class="{ selected: index === selectedIndex, local: example.localIndex >= 0 }"
      :data-key="index"
      @click="onSelectExample"
    >
      {{ example.label }}
    </div>
  </div>
</template>
<style lang="scss">
@import "TestExamplesFulltextSmartCriteria";
</style>
<script src="./TestExamplesFulltextSmartCriteria.controller.ts" lang="ts"></script>
