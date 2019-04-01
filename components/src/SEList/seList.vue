<template>
  <div class="seList__wrapper" ref="wrapper">
    <div class="seList">
      <div class="seList__header__slot">
        <slot name="header">
          <img class="seList__logo__img" :src="logoUrl" />
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
              <input
                type="text"
                class="form-control seList__search__keyword"
                :placeholder="translations.searchPlaceholder"
                v-model="filterInput"
                @change="filterList(filterInput)"
                @input="_onFilterInput"
              />
              <i
                class="input-group-addon material-icons seList__search__keyword__remove"
                @click="clearListFilter"
              >
                close
              </i>
              <i
                class="input-group-addon material-icons seList__search__button"
                @click="filterList(filterInput)"
              >
                search
              </i>
            </div>
          </div>
        </slot>
      </div>

      <div class="seList__summary__wrapper">
        <div class="seList__summary" ref="summaryPager"></div>
      </div>

      <div class="seList__list" ref="listView"></div>

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
