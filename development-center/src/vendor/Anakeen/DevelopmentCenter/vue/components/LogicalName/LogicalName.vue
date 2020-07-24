<template>
  <div class="se-properties-view">
    <ank-smart-form
      ref="formLogicalName"
      class="logical-name-smart-form"
      :config="smartFormData"
      :options="{ force: true }"
      @smartFieldChange="onChangeLogicalName"
      @actionClick="menuClick"
    ></ank-smart-form>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./LogicalName.scss";
</style>

<script src="./LogicalName.js"></script>
