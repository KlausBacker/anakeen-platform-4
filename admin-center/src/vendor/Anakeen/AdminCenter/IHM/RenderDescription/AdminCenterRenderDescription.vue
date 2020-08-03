<template>
  <div class="rd-parent">
    <ank-split-panes ref="rdSplitter" watch-slots vertical class="rd-splitter">
      <div class="rd-panel rd-grid" :splitpanes-size="panelSizes.grid">
        <kendo-button class="k-primary" @click="createNewDescription">
          {{ $t("AdminRenderDescriptiont.Create new description") }}
        </kendo-button>
        <ank-se-vue-grid
          ref="grid"
          class="rd-grid"
          collection="RENDERDESCRIPTION"
          controller="DEFAULT_GRID_CONTROLLER"
          :pageable="pageable"
          refresh
          :context-titles="false"
          :actions="actions"
          :columns="columns"
          filterable
          default-expandable
          @rowActionClick="selectRenderDescription"
        />
      </div>

      <div class="rd-panel rd-form-wrapper" :splitpanes-size="panelSizes.description">
        <div v-show="!selectedRenderDescription" class="rd-empty">
          <span class="material-icons">info</span>
          <p>{{ $t("AdminCenterRenderDescription.Select render description") }}</p>
        </div>
        <ank-smart-element
          v-show="selectedRenderDescription"
          ref="rdSmartElement"
          class="rd-smart-element"
          @actionClick="updateExample"
          @afterSave="afterSaveRefreshGrid"
        />
      </div>

      <div class="rd-panel rd-example-wrapper" :splitpanes-size="panelSizes.example">
        <div v-show="!selectedExample" class="rd-empty">
          <span class="material-icons">info</span>
          <p>{{ $t("AdminCenterRenderDescription.Click to example link") }}</p>
        </div>
        <ank-smart-element v-show="selectedExample" ref="rdExample" class="rd-smart-element" />
      </div>
    </ank-split-panes>
  </div>
</template>
<style scoped lang="scss">
@import "./AdminCenterRenderDescription.scss";
</style>
<script src="./AdminCenterRenderDescription.controller.ts" lang="ts"></script>
