<template>
  <div class="test-smart-form-examples" >
    <div  v-for="(example, index) in examples"
          class="example"
          :class="{ selected: index === selectedIndex}"
          :data-key="index" @click="onSelectExample">
        {{ example.label }}
    </div>
  </div>
</template>
<style lang="scss">
@import "./TestExamplesSmartForm.scss";
</style>
<script src="./TestExamplesSmartForm.controller.ts" lang="ts"></script>
