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
      :pageable="pageable"
      :columns="columns"
      :content-url="contentUrl"
      :controller="controller"
      :filter="currentFilter"
      :sort="[sort]"
      :page="page"
      @selectionChange="onSelectionChange"
      @rowClick="onItemClick"
      @dataBound="onDataBound"
      @pageChange="onPageChange"
      @beforeContent="onBeforeContent"
    >
      <template v-slot:headerTemplate="props">
        <div class="smart-element-list--slots-wrapper">
          <slot name="header"></slot>
          <slot name="label" v-bind="{ label: listLabel }">
            <div class="smart-element-list--label">
              <div class="smart-element-list--label-text">
                {{ listLabel }}
              </div>
            </div>
          </slot>
          <slot name="search" v-bind="{ filter: filterList }">
            <div class="smart-element-list--search">
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
            </div>
          </slot>
        </div>
      </template>
      <template v-slot:cellTemplate="{ props, listeners }">
        <td class="smart-element-list-item--body" :title="props.dataItem.properties.title" @click.stop="listeners.ItemClick">
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
      <template v-slot:gridFooter="opts">
        <div class="smart-element-list--footer">
          <slot name="footer"></slot>
          <slot name="pager">
            <ank-grid-pager
              v-if="opts.gridComponent.pager"
              v-bind="opts.gridComponent.pager"
              class="smart-element-list--pager"
              :gridComponent="opts.gridComponent"
            ></ank-grid-pager>
          </slot>
        </div>
      </template>
    </ank-grid>
  </div>
</template>
<style lang="scss">
@import "./AnkSEList.scss";
</style>
<script src="./AnkSEList.component.ts" lang="ts"></script>
