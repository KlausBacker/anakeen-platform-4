<template>
    <div class="smart-structure-section">
        <h3>Smart Structures</h3>
        <ss-list routeName="SmartStructures::infos"
                 routeParamField="ssName"
                 smartStructureCategory="vendor"
                 position="left"
        >
        </ss-list>
    </div>
</template>

<script>
  import SSList from "../../components/SSList/SSList.vue";
  export default {
    name: "SmartStructures",
    components: {
      "ss-list": SSList
    }
  }
</script>

<style>
    .smart-structure-section {
        padding: 2rem;
        flex: 1;
    }
</style>