<template>
  <div class="security-fall-section">
    <ank-splitter ref="fallSplitter" class="fall-splitter" :panes="panes" local-storage-key="security-fall-splitter">
      <template slot="left">
        <ank-se-grid
          ref="fallGrid"
          :pageable="{ pageSizes: [100, 200, 500], pageSize: 100 }"
          :context-titles="false"
          controller="FIELD_ACCESS_GRID_CONTROLLER"
          filterable
          default-expandable
          @rowActionClick="actionClick"
          @dataBound="onGridDataBound"
        >
          <template v-slot:cellTemplate="opts">
            <td v-if="opts.columnConfig.field === 'dpdoc_famid'">
              <a
                data-role="develRouterLink"
                :href="'/devel/smartStructures/' + opts.props.dataItem.abstract.dpdoc_famid + '/infos'"
                >{{ opts.props.dataItem.abstract.dpdoc_famid }}</a
              >
            </td>
            <td v-else-if="opts.columnConfig.field === 'fall_famid'">
              <a
                data-role="develRouterLink"
                :href="'/devel/smartStructures/' + opts.props.dataItem.abstract.fall_famid + '/infos'"
                >{{ opts.props.dataItem.abstract.fall_famid }}</a
              >
            </td>
            <td v-else-if="opts.columnConfig.field === 'title'">
              <a
                data-role="develRouterLink"
                :href="
                  '/devel/smartElements/' +
                    opts.props.dataItem.properties.id +
                    '/view?initid=' +
                    opts.props.dataItem.properties.id
                "
                >{{ opts.props.dataItem.properties.title }}</a
              >
            </td>
          </template>
        </ank-se-grid>
      </template>
      <template slot="right">
        <!--                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1" class="splitter-right"></router-multi-view>-->
        <component
          :is="selectedFieldAccess.component"
          v-if="selectedFieldAccess"
          style="height: 100%"
          v-bind="selectedFieldAccess.props"
        ></component>
      </template>
    </ank-splitter>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./FieldAccess.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./FieldAccess.controller.js"></script>
