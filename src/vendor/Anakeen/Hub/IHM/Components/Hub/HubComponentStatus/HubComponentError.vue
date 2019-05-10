<template>
  <div
    :class="`ank-hub-component-error ank-hub-component-error--${displayType}`"
  >
    <i
      class="ank-hub-component-error-icon fa fa-warning"
      :title="titleMessage"
    ></i>
    <span
      class="ank-hub-component-error-message"
      v-if="displayType === 'CONTENT'"
      >{{ errorMessage }}</span
    >
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
.ank-hub-component-error {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  .ank-hub-component-error-icon {
    font-size: 2rem;
    color: red;
  }

  &.ank-hub-component-error--CONTENT {
    .ank-hub-component-error-icon {
      font-size: 5rem;
    }
    .ank-hub-component-error-message {
      font-size: 2rem;
    }
  }
}
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script>
export default {
  props: {
    title: {
      default: "Le composant n'a pas pu être chargé"
    },
    displayType: {
      type: String
    },
    entryOptions: {
      type: Object,
      default: () => null
    }
  },
  data() {
    return {
      titleMessage: this.title,
      errorMessage: this.title
    };
  },
  mounted() {
    if (this.entryOptions && this.entryOptions.name) {
      this.errorMessage = `${this.entryOptions.name} n'a pas pu être chargé`;
    }
  },
  errorCaptured(err, vm, info) {
    console.log(err, vm, info);
  }
};
</script>
