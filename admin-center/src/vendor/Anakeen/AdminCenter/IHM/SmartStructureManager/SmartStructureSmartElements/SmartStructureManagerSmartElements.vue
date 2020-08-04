<template>
  <div class="smart-elements-section">
    <ank-split-panes
      watch-slots
      vertical
      collapse-buttons
      ref="splitter"
      class="ssm-smart-element-splitter"
      localStorageKey="admin-center-ssm-smart-element-splitter"
    >
      <ank-se-grid
        ref="grid"
        class="se-grid"
        :pageable="pageableConfig"
        :collection="ssName"
        :actions="gridActions"
        :columns="gridColumns"
        :resizable="false"
        filterable
        refresh
        defaultExpandable
        @rowActionClick="actionClick"
      >
        <template v-slot:cellTemplate="options">
          <td v-if="options.columnConfig.field === 'name' && options.props.dataItem.properties.name === null"></td>
        </template>
      </ank-se-grid>
      <div v-if="!selectedElement" class="element-empty full-pane-size">
        <span class="material-icons">info</span>
        <p>{{ $t("AdminCenterSmartStructure.Select a SmartElement") }}</p>
      </div>
      <div v-else-if="selectedElement.component === 'element-view'" class="full-pane-size">
        <element-view :initid="selectedElement.props.initid" :viewId="selectedElement.props.viewId" @se-after-save="refreshGrid"></element-view>
      </div>
      <div v-else-if="selectedElement.component === 'element-properties'" class="full-pane-size">
        <element-properties :elementId="selectedElement.name"></element-properties>
      </div>
    </ank-split-panes>
  </div>
</template>
<!-- Global CSS -->
<style scoped lang="scss">
@import "./SmartStructureManagerSmartElements.scss";
</style>
<script src="./SmartStructureManagerSmartElements.controller.js"></script>
