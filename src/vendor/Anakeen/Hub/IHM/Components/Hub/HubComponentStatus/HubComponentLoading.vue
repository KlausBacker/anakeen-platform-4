<template>
  <div class="ank-hub-component-loading">
      <div ref="loadingWidget" class="ank-hub-component-loading-widget"></div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    .ank-hub-component-loading {
        position: relative;
        width: 100%;
        height: 100%;
    }
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script>
  export default {
    mounted() {
      kendo.ui.progress($(this.$refs.loadingWidget), true);
    },
    beforeDestroy() {
      kendo.ui.progress($(this.$refs.loadingWidget), false);
    }
  };

</script>
