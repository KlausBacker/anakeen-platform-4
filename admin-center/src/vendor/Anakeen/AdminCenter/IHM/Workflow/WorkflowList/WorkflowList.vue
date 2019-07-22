<template>
  <div class="workflow-list">
    <kendo-datasource
      ref="wflDataSource"
      :transport-read="readData"
      :schema-data="parseData"
      :schema-model="listModel"
      :server-filtering="true"
    ></kendo-datasource>
    <div class="workflow-tabs">
      <div class="workflow-tabs-list" ref="ssWflList">
        <div v-if="hasFilter" class="workflow-tabs-filter">
          <input
            class="form-control k-textbox"
            type="search"
            :placeholder="filterPlaceholder"
            v-model="listFilter"
          />
          <span class="filter-list-clear" @click="clearFilter"
            ><i class="material-icons">close</i></span
          >
        </div>
        <div class="workflow-tabs-list-nav">
          <slot v-if="isEmpty" name="empty">
            <div class="empty-content">
              <span>No data found</span>
            </div>
          </slot>
          <div
            v-for="(tab, index) in tabs"
            :key="`tab-${tab.name}-${index}`"
            :class="{
              'workflow-list-item': true,
              'item-active': tab.name === selected || tab.id === selected
            }"
            :title="tab.title"
            @click="onListItemClicked(tab)"
          >
            <img class="workflow-list-item-icon" :src="tab.icon" />
            <div class="workflow-list-item-title">
              {{ tab.name || tab.title }} ({{ tab.id }})
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script src="./WorkflowList.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./WorkflowList.scss";
</style>
