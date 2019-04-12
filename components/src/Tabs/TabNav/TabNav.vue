<template>
  <nav
    :class="{
      'ank-tabs-nav-wrap': true,
      scrollable: scrollable,
      [`is-${rootTabs.tabPosition}`]: true
    }"
  >
    <span
      v-if="scrollable"
      :class="{ 'ank-tabs-nav-prev': true, 'is-disabled': scrollable.prev }"
      @click="scrollPrev"
      :style="navButtonStyle"
      ><i class="fa fa-caret-left"></i
    ></span>
    <span
      v-if="scrollable"
      :class="{ 'ank-tabs-nav-next': true, 'is-disabled': scrollable.next }"
      @click="scrollNext"
      :style="navButtonStyle"
      ><i class="fa fa-caret-right"></i
    ></span>
    <dropdown-menu
      v-if="scrollable && tabsList"
      :class="{ 'ank-tabs-nav-list': true }"
      :items="panes"
      :style="navButtonStyle"
      @dropdownMenuSelected="onTabListSelected"
    >
      <template slot="item" slot-scope="slotProps">
        <vnodes
          v-if="slotProps.item.$slots.label"
          :vnodes="slotProps.item.$slots.label"
          :class="labelClass(slotProps.item)"
        ></vnodes>
        <span
          v-else-if="slotProps.item.tabNavItemList"
          :class="labelClass(slotProps.item)"
          v-html="slotProps.item.tabNavItemList"
        >
        </span>
        <span
          v-else
          :class="labelClass(slotProps.item)"
          :title="slotProps.item.label"
          >{{ slotProps.item.label }}</span
        >
        <span
          v-if="slotProps.item.isClosable || rootTabs.editable"
          class="ank-tab-item-close"
          @click="onClickRemove(slotProps.item, $event)"
        >
          <i class="k-icon k-i-x"></i>
        </span>
      </template>
    </dropdown-menu>
    <div class="ank-tabs-nav-scroll" ref="navScroll">
      <div
        :class="{
          'ank-tabs-nav': true,
          [`is-${rootTabs.tabPosition}`]: true
        }"
        ref="nav"
        :style="navStyle"
        role="tablist"
        @keydown="onChangeTab"
      >
        <!--{!type ? <tab-bar tabs={panes}></tab-bar> : null}-->
        <div
          v-for="(pane, index) in panes"
          :class="{
            'ank-tab-item': true,
            [`is-${rootTabs.tabPosition}`]: true,
            'is-active': pane.active,
            'is-disabled': pane.disabled,
            'is-closable': pane.isClosable || rootTabs.editable,
            'is-focus': isFocus,
            'is-dirty': !!pane.isDirty
          }"
          :key="`tab-${index}`"
          :id="`tab-${index}`"
          :aria-controls="`tab-${index}`"
          role="tab"
          :aria-selected="pane.active"
          ref="tabs"
          :tabindex="pane.active ? 0 : -1"
          @focus="setFocus"
          @blur="removeFocus"
          @click="onClickNavItem(pane, pane.paneName || index, $event)"
          @keydown="onKeydownNavItem(pane, $event)"
          :title="pane.tabTitle || pane.label"
        >
          <vnodes
            v-if="pane.$slots.label"
            :vnodes="pane.$slots.label"
            :class="labelClass(pane)"
          ></vnodes>
          <span
            v-else-if="pane.tabNavItemList"
            :class="labelClass(pane)"
            v-html="pane.tabNavItemList"
          >
          </span>
          <span v-else :class="labelClass(pane)" :title="pane.label">{{
            pane.label
          }}</span>
          <span
            v-if="pane.isClosable || rootTabs.editable"
            class="ank-tab-item-close"
            @click="onClickRemove(pane, $event)"
          >
            <i class="k-icon k-i-x"></i>
          </span>
        </div>
      </div>
    </div>
  </nav>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./TabNav.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./TabNav.component.ts"></script>
