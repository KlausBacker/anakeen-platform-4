<template>
  <div class="ank-tree">
    <div v-if="treeData.length === 0 && !error && !message" class="ank-tree-loading">
      <p>{{ translations.loading }}</p>
      <p v-if="filter">
        {{ translations.searching }} : "<i>{{ filter }}</i
        >"
      </p>
      <progress />
    </div>
    <div v-if="error" class="ank-tree-error">
      <p>{{ error }}</p>
      <a class="item-reload k-button k-primary" @click="reloadTree">{{ translations.reload }}</a>
    </div>
    <div v-if="message" class="ank-tree-message">
      <p>{{ message }}</p>
    </div>
    <div v-if="treeData.length > 0" class="ank-tree-header">
      <div class="ank-tree-item ank-tree-header-content" :style="{ 'padding-right': scrollBarWidth }">
        <span class="ank-tree-foldername"
          >{{ translations.headerLabel }}
          <span v-if="requestFilter && displayFilter" class="ank-tree-filter">
            <span class="material-icons">filter_alt</span> : "<em> {{ requestFilter }}</em
            >"</span
          >
        </span>
        <span v-if="multipleSelection" class="ank-tree-selected"
          >{{ translations.selectItem }} ({{ Object.keys(selectedNodes).length }})</span
        >
        <span v-if="displayChildrenCount" class="ank-tree-subfoldercount">{{ translations.childrenCount }}</span>
        <span v-if="displayItemCount" class="ank-tree-usercount">{{ translations.itemCount }}</span>
      </div>
    </div>
    <div
      v-if="treeData.length > 0"
      ref="tree"
      class="ank-tree-body"
      @scroll.passive="debounced(scrollDebounce, onScroll)($event)"
    >
      <div class="ank-tree-first" :style="{ height: firstItemHeight + 'rem' }" />

      <div
        v-for="item in visibleTreeData"
        :key="item.index"
        :title="item.name"
        class="ank-tree-item"
        :class="{
          'item-opened': item.isOpened,
          'item-selected': item.isSelected,
          'item-match': item.match,
          'item-loading': item.loading,
          'item-child-selected': item.isChildSelected
        }"
        :style="{ height: itemHeight + 'rem' }"
        @click="selectNode($event, item)"
      >
        <span :style="{ 'padding-left': item.level * levelIndentationWidth + 'rem' }"></span>
        <i v-if="item.loadedChildrenCount > 0" class="material-icons item-arrow" @click="openNode($event, item, false)"
          >play_arrow
        </i>
        <i v-else class="material-icons item-leaf">group</i>

        <i v-if="item.loadedChildrenCount > 0" class="material-icons item-highlight item-node">folder</i>

        <span class="ank-tree-foldername">
          <span class="ank-tree-item-label">{{ item.name }}</span>

          <a
            v-if="item.directChildsCount !== item.loadedChildrenCount"
            class="ank-tree-explorer"
            @click="reopenNode($event, item)"
            >{{ translations.reopenNode }}</a
          >
        </span>
        <span v-if="multipleSelection === true" class="ank-tree-selected">
          <input class="k-checkbox" type="checkbox" :checked="item.isSelected" />
        </span>
        <span v-if="displayChildrenCount" class="ank-tree-subfoldercount">{{ item.childrenCount }}</span>
        <span v-if="displayItemCount" class="ank-tree-usercount">{{ item.itemCount }}</span>
      </div>
      <div class="ank-tree-last" :style="{ height: lastItemHeight + 'rem' }" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
@import "./AnkTree.scss";
</style>
<script lang="ts" src="./AnkTree.component.ts"></script>
