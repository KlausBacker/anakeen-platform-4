<template>
  <div class="smart-elements-section">
    <ank-splitter ref="splitter" class="se-splitter" :panes="panes" local-storage-key="smart-elements-splitter">
      <template slot="left">
        <ank-se-grid
          ref="grid"
          class="se-grid"
          :pageable="{ pageSizes: [100, 200, 500], pageSize: 100 }"
          controller="ELEMENTS_GRID_CONTROLLER"
          filterable
          defaultExpandable
          @grid-data-bound="onGridDataBound"
          @rowActionClick="actionClick"
        >
          <template v-slot:cellTemplate="opts">
            <td v-if="opts.columnConfig.field === 'fromid'">
              <a
                data-role="develRouterLink"
                :href="'/devel/smartStructures/' + opts.props.dataItem.properties.fromid + '/infos'"
                >{{ opts.props.dataItem.properties.fromid }}</a
              >
            </td>
          </template>
        </ank-se-grid>
      </template>
      <template slot="right">
        <!--                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1" class="splitter-right"></router-multi-view>-->
        <component
          :is="selectedElement.component"
          v-if="selectedElement"
          ref="component"
          style="height:100%"
          v-bind="selectedElement.props"
        />
      </template>
    </ank-splitter>
  </div>
</template>
<!-- Global CSS -->
<style scoped lang="scss">
@import "./SmartElements.scss";
</style>
<script src="./SmartElements.controller.js"></script>
