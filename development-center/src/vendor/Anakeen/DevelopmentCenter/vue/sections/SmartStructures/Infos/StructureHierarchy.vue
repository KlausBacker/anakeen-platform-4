<template>
  <ul class="smart-structure-hierarchy">
    <li
      v-for="(item, index) in hierarchy"
      :key="`${currentStructure}-${item.name}-${index}`"
      class="smart-structure-hierarchy-item"
    >
      <a
        data-role="develRouterLink"
        :href="`/devel/smartStructures/${item.name}/infos`"
        :class="{
          'smart-structure-hierarchy-label': true,
          'current-item': item.name === currentStructure
        }"
        >{{ item.name }}</a
      >
      <structure-hierarchy
        v-if="item.children && item.children.length"
        :data="item.children"
        :currentStructure="currentStructure"
      ></structure-hierarchy>
    </li>
  </ul>
</template>

<script>
export default {
  name: "structure-hierarchy",
  props: ["data", "currentStructure"],
  data() {
    return {
      hierarchy: JSON.parse(JSON.stringify(this.data))
    };
  }
};
</script>

<style lang="scss" scoped>
/*Now the CSS*/
* {
  margin: 0;
  padding: 0;
}

.smart-structure-hierarchy {
  padding-top: 20px;
  position: relative;
  flex: 1;
  display: flex;

  .smart-structure-hierarchy-item {
    float: left;
    text-align: center;
    list-style-type: none;
    position: relative;
    padding: 20px 5px 0 5px;

    /*We will use ::before and ::after to draw the connectors*/
    &::before,
    &::after {
      content: "";
      position: absolute;
      top: 0;
      right: 50%;
      border-top: 1px solid #ccc;
      width: 50%;
      height: 20px;
    }

    &::after {
      right: auto;
      left: 50%;
      border-left: 1px solid #ccc;
    }

    /*We need to remove left-right connectors from elements without
    any siblings*/
    &:only-child::after,
    &:only-child::before {
      display: none;
    }

    /*Remove space from the top of single children*/
    &:only-child {
      padding-top: 0;
    }

    /*Remove left connector from first child and
    right connector from last child*/
    &:first-child::before,
    &:last-child::after {
      border: 0 none;
    }

    /*Adding back the vertical connector to the last nodes*/
    &:last-child::before {
      border-right: 1px solid #ccc;
      border-radius: 0 5px 0 0;
    }

    &:first-child::after {
      border-radius: 5px 0 0 0;
    }

    & .smart-structure-hierarchy-label {
      background: #e3edf6;
      border: 2px solid #5f9ccc;
      text-align: center;
      padding: 0.33em 0.66em;
      text-decoration: none;
      color: #333;
      display: inline-block;
      border-radius: 5px;

      &.current-item {
        background: #fdc69f;
      }
    }
  }

  /*Time to add downward connectors from parents*/
  & .smart-structure-hierarchy::before {
    content: "";
    position: absolute;
    top: 0;
    left: 50%;
    border-left: 1px solid #ccc;
    width: 0;
    height: 20px;
  }
}
</style>
