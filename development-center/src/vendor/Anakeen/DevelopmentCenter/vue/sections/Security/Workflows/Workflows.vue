<template>
  <div class="security-workflows-section">
    <div class="security-workflows-section-content">
      <ss-list
        list-url="/api/v2/devel/smart/workflows/"
        :filter="{ placeholder: 'Search a workflow' }"
        vendor-category="auto"
        :selected="selectedWorkflow"
        @item-clicked="onItemClicked"
        @list-ready="onListReady"
      ></ss-list>
      <router-tabs
        v-for="listItem in listContent"
        v-show="listItem && listItem.name === selectedWorkflow"
        :ref="listItem.name"
        :key="listItem.name || listItem.id"
        :tabs="tabs"
        @hook:mounted="onTabsMounted(listItem.name)"
        @tab-selected="onTabSelected"
      >
        <template v-slot="slotProps">
          <component :is="slotProps.tab.component" :workflow-id="listItem.name || listItem.id"></component>
        </template>
      </router-tabs>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./Workflows.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./Workflows.controller.js"></script>
