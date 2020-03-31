<template>
  <div
    :class="{
      'smart-element-list': true,
      'smart-element-list--fit': autoFit,
      'smart-element-list--compact': small,
      'smart-element-list--tiny': xSmall
    }"
    ref="wrapper"
  >
    <ank-grid
      ref="internalWidget"
      :auto-fit="autoFit"
      :collection="collectionId"
      :selectable="selectable"
      :columns="columns"
      :content-url="contentUrl"
      :controller="controller"
      :filter="currentFilter"
      :page="page"
      @selectionChange="onSelectionChange"
      @rowClick="onItemClick"
      @dataBound="onDataBound"
      @pageChange="onPageChange"
    >
      <template v-slot:headerTemplate="props">
        <div class="smart-element-list--slots-wrapper">
          <div class="smart-element-list--header">
            <slot name="header"></slot>
          </div>

          <div class="smart-element-list--label">
            <slot name="label">
              <div class="smart-element-list--label-text">
                {{ listLabel }}
              </div>
            </slot>
          </div>

          <div class="smart-element-list--search">
            <slot name="search">
              <div class="smart-element-list--search-default">
                <div class="input-group">
                  <i
                    class="input-group-addon material-icons smart-element-list--search-button"
                    @click="filterList(filterInput)"
                  >
                    search
                  </i>
                  <input
                    type="text"
                    class="form-control smart-element-list--search-input"
                    :placeholder="translations.searchPlaceholder"
                    v-model="filterInput"
                    @change="filterList(filterInput)"
                  />
                  <i
                    v-show="filterInput"
                    class="input-group-addon material-icons smart-element-list--search-button-clear"
                    @click="clearListFilter()"
                  >
                    close
                  </i>
                </div>
              </div>
            </slot>
          </div>
        </div>
      </template>
      <template v-slot:cellTemplate="{ props, listeners }">
        <td class="smart-element-list-item--body" :title="props.dataItem.properties.title" @click="listeners.ItemClick">
          <slot name="item" v-bind:item="props.dataItem">
            <div class="smart-element-list-item--heading">
              <img class="smart-element-list-item--heading-icon" :src="props.dataItem.properties.icon" alt="image" />
              <div>{{ props.dataItem.properties.title }}</div>
            </div>
            <div v-if="props.dataItem.properties.state" class="smart-element-list-item--heading-state">
              <span class="smart-element-list-item--heading-state-label">
                {{ props.dataItem.properties.state.displayValue }} </span
              ><span
                class="smart-element-list-item--heading-state-color"
                :style="`background-color: ${props.dataItem.properties.state.color}`"
              ></span>
            </div>
          </slot>
        </td>
      </template>
    </ank-grid>
  </div>
</template>
<style lang="scss">
@import "./AnkSEList.scss";
</style>
<script src="./AnkSEList.component.ts" lang="ts"></script>
