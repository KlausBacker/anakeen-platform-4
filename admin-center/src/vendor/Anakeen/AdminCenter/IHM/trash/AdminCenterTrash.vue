<template>
  <div class="trash-parent">
        <!-- <button @click="onClickRefresh" class="dev-center-refresh-button k-button k-button-icontext" title="Refresh Data">
        <slot>
            <i class="dev-center-refresh-button-icon k-icon k-i-refresh"></i>
            <span class="dev-center-refresh-button-icon k-label">Refresh Data</span>
        </slot>
    </button> -->
    <ank-split-panes
      watch-slots
      vertical
      ref="trashSplitter"
      class="trash-splitter"
      localStorageKey="admin-center-trash-splitter"
    >
      <ank-se-grid
        class="trash-grid"
        sortable="multiple"
        :refresh="true"
        :pageSizes="[50, 100, 200]"
        ref="grid"
        urlConfig="/api/v2/admin/trash/config/"
        :contextTitles="false"
        @action-click="selectTrash"
      >

      </ank-se-grid>

      <div v-if="!selectedTrash" class="trash-empty">
        <span class="material-icons">delete</span>
        <p>Select Smart Element</p>
      </div>
      <ank-smart-element v-else class="trash-smart-element" ref="trashSmartElement"></ank-smart-element>
    </ank-split-panes>
  </div>
</template>

// <script>
//     import { REFRESH_EVENT_KEY } from "/development-center/src/vendor/Anakeen/DevelopmentCenter/vue/mixins/GlobalMixins/globalMixins";
//   export default {
//     name: "RefreshButton",
//     props: {

//     },
//     methods: {
//       onClickRefresh() {
//         if (this.$_busEvent) {
//           this.$_busEvent.$emit(REFRESH_EVENT_KEY)
//         }
//       }
//     }
//   }

// // development-center/src/vendor/Anakeen/DevelopmentCenter/vue/mixins/GlobalMixins/globalMixins.js
// // admin-center/src/vendor/Anakeen/AdminCenter/IHM/trash/AdminCenterTrash.vue

// </script>

<style scoped lang="scss">
@import "./AdminCenterTrash.scss";
</style>
<script src="./AdminCenterTrash.controller.ts" lang="ts"></script>
