<template>
  <div :class="`smart-structure-list smart-structure-list-position--${position}`">
    <kendo-datasource
      ref="remoteDataSource"
      :transport-read="readData"
      :schema-data="parseData"
      :schema-model="listModel"
    ></kendo-datasource>
    <div class="smart-structure-tabs">
      <div ref="ssTabsList" class="smart-structure-tabs-list">
        <div v-if="hasFilter" class="smart-structure-tabs-filter">
          <input v-model="listFilter" class="form-control k-textbox" type="search" :placeholder="filterPlaceholder" />
          <span class="filter-list-clear" @click="clearFilter"><i class="material-icons">close</i></span>
        </div>
        <div class="smart-structure-tabs-list-nav">
          <slot v-if="isEmpty" name="empty">
            <div class="empty-content">
              <span>No data found</span>
            </div>
          </slot>
          <div
            v-for="(tab, index) in tabs"
            :key="`tab-${tab.name}-${index}`"
            :class="{
              'smart-structure-list-item': true,
              'item-active': tab.name === selected || tab.id === selected
            }"
            :title="tab.title"
            @click="onListItemClicked(tab)"
          >
            <img class="smart-structure-list-item-icon" :src="tab.icon" />
            <div class="smart-structure-list-item-title">
              {{ tab.title }} ({{ tab.id }}) <br />
              ({{ tab.name }})
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./SSList.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./SSList.component.js"></script>
