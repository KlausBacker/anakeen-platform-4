<template>
  <div class="seList__wrapper" ref="wrapper">
    <div class="seList">
      <div class="seList__header__slot">
        <slot name="header">
        </slot>
      </div>

      <div class="seList__header__wrapper">
        <slot name="title">
          <div class="seList__header__label">
            {{ collectionLabel }}
          </div>
        </slot>
      </div>

      <div class="seList__search__wrapper">
        <slot name="search">
          <div class="seList__search">
            <div class="input-group">
              <i
                class="input-group-addon material-icons seList__search__button"
                @click="filterList(filterInput)"
              >
                search
              </i>
              <input
                type="text"
                class="form-control seList__search__keyword"
                :placeholder="translations.searchPlaceholder"
                v-model="filterInput"
              />
              <i
                v-show="filterInput"
                class="input-group-addon material-icons seList__search__keyword__remove"
                @click="clearListFilter()"
              >
                close
              </i>
            </div>
          </div>
        </slot>
      </div>

      <div class="seList__list">
        <div
          v-for="(item, index) in dataSourceItems"
          :key="index"
          :class="{
            seList__listItem: true,
            'is-active': selectedItem == item.properties.initid
          }"
          :data-se-id="item.properties.initid"
          :title="item.properties.title"
          @click="onClickSE(item)"
        >
          <slot name="item" :properties="item.properties">
            <div class="seList__listItem__body">
              <div class="seList__listItem__heading">
                <img
                  class="seList__listItem__heading__content_icon"
                  :src="item.properties.icon"
                  alt="image"
                />
                <span>{{ item.properties.title }}</span>
              </div>
            </div>
            <div
              v-if="item.properties.state"
              class="seList__listItem__heading__state"
            >
              <span
                class="seList__listItem__heading__state--color"
                :style="`background-color: ${item.properties.state.color}`"
              ></span>
              <span class="seList__listItem__heading__state--label">
                {{ item.properties.state.displayValue }}
              </span>
            </div>
          </slot>
        </div>
      </div>

      <div class="seList__list__pager_wraper">
        <input class="seList__list__pagerCounter" ref="pagerCounter" />
        <div class="seList__list__pager" ref="pager"></div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
@import "./seList.scss";
</style>
<script src="./seList.component.ts" lang="ts"></script>
