<template>
  <div class="ank-tabs-dropdown-list">
    <button @click="toggleShow" class="anchor">
      <slot><i class="material-icons">menu</i></slot>
    </button>
    <div v-if="showMenu" class="menu">
      <div
        class="menu-item"
        v-for="item in this.items"
        @click="itemClicked(item)"
      >
        <slot name="item" :item="item"></slot>
      </div>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./DropdownMenu.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./DropdownMenu.component.ts"></script>
