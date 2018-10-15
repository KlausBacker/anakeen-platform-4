<template>
    <div :class="{'smart-structure-list-item': true}" :data-smartstructname="templateArgs.structure.name" :title="templateArgs.structure.title" @click="onItemClick">
        <img class="smart-structure-list-item-icon" :src='templateArgs.structure.icon'/>
        <div class="smart-structure-list-item-title">{{templateArgs.structure.name}}</div>
    </div>
</template>

<script>
  export default {
    name: "structureItemTemplate",
    data() {
      return {
        templateArgs: { structure: {}, parentComponent: {}, to: {}}
      }
    },
    methods: {
      onItemClick() {
        this.templateArgs.parentVueComponent.$router.push(this.templateArgs.to);
      }
    }
  }
</script>

<style scoped>

</style>