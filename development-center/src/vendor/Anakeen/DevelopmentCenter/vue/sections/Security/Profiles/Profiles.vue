<template>
  <div class="security-se-section">
    <ank-splitter
      ref="profileSplitter"
      class="profile-splitter"
      :panes="panes"
      local-storage-key="security-profiles-splitter"
    >
      <template slot="left">
        <ank-se-grid
          ref="profilesGrid"
          :pageable="{ pageSizes: [100, 200, 500], pageSize: 100 }"
          :context-titles="false"
          controller="PROFILES_GRID_CONTROLLER"
          filterable
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
        <profile-view style="height: 100%" :se-identifier="selectedProfile"></profile-view>
      </template>
    </ank-splitter>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./Profiles.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./Profiles.controller.js"></script>
