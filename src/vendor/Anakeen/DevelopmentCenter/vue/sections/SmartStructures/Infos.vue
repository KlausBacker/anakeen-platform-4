<template>
    <div>
        <h1>Infos for {{ssName}}</h1>
        <smart-element ref="smartElement" :initid="ssName"></smart-element>
    </div>
</template>

<script>
    import Vue from "vue";
    import { AnkSmartElement } from "@anakeen/ank-components";
    Vue.use(AnkSmartElement);
  export default {
    name: "Infos",
    props: ["ssName"],
    components: {
      "smart-element": AnkSmartElement
    },
    mounted() {
      console.log(this.ssName);
    },
    watch: {
      ssName(newValue, oldValue) {
        if (newValue !== oldValue) {
          if (this.$refs.smartElement.isLoaded()) {
            this.$refs.smartElement.fetchSmartElement({initid: newValue});
          }
        }
      }
    }
  }
</script>

<style scoped>

</style>