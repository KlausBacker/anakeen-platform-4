<template>
  <div class="security-ss-section">
    <ss-list position="left" :selected="selectedStructure" @item-clicked="onItemClicked" @list-ready="onListReady">
    </ss-list>
    <div class="security-ss-content">
      <router-tabs
        v-for="(listItem, index) in listContent"
        v-show="listItem && listItem.name === selectedStructure"
        :ref="listItem.name"
        :key="index"
        :tabs="tabs"
        @hook:mounted="onTabsMounted(listItem.name)"
        @tab-selected="onTabSelected"
      >
        <template v-slot="slotProps">
          <component
            :is="slotProps.tab.component"
            :ref="`${listItem.name}-${slotProps.tab.name}`"
            :ss-name="listItem.name"
            :ss-section="ssSection"
            @navigate="onChildNavigate"
            @hook:mounted="onSubComponentMounted(listItem.name, slotProps.tab.name)"
          ></component>
        </template>
      </router-tabs>
      <div v-if="!selectedStructure" class="security-ss-empty">
        <span class="k-icon k-i-folder-open security-ss-empty-icon"></span>
        <span class="security-ss-empty-text">Select a structure</span>
      </div>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./SmartStructuresSecurity.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./SmartStructuresSecurity.controller.js"></script>
