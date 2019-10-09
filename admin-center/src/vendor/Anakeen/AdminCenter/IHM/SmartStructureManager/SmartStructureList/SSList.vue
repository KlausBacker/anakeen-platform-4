<template>
  <div class="ss-list">
    <kendo-datasource
            ref="wflDataSource"
            :transport-read="readData"
            :schema-data="parseData"
            :schema-model="listModel"
            :server-filtering="true"
    ></kendo-datasource>
    <div class="ss-tabs">
      <div class="ss-tabs-list" ref="ssWflList">
        <div v-if="hasFilter" class="ss-tabs-filter">
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
        <div class="ss-tabs-list-nav">
          <slot v-if="isEmpty" name="empty">
            <div class="empty-content">
              <span>No data found</span>
            </div>
          </slot>
          <div
                  v-for="(tab, index) in tabs"
                  :key="`tab-${tab.name}-${index}`"
                  :class="{
              'ss-list-item': true,
              'item-active': tab.name === selected || tab.id === selected
            }"
                  :title="tab.title"
                  @click="onListItemClicked(tab)"
          >
            <img class="ss-list-item-icon" :src="tab.icon" />
            <div class="ss-list-item-title">
              {{ tab.name || tab.title }} ({{ tab.id }})
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script src="./SSList.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
  @import "./SSList.scss";
</style>
