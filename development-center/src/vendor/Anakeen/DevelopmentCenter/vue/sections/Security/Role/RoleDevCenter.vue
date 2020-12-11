<template>
  <div class="security-roles-section">
    <ank-splitter ref="rolesSplitter" class="roles-splitter" :panes="panes" local-storage-key="security-roles-splitter">
      <template slot="left">
        <ank-se-grid
          ref="roleContent"
          class="role-content"
          collection="ROLE"
          :pageable="{ pageSizes: [100, 200, 500], pageSize: 100 }"
          filterable
          :actions="actions"
          :columns="columns"
          @rowActionClick="selectRole"
          @dataBound="onGridDataBound"
          @gridError="onGridError"
        >
        </ank-se-grid>
      </template>
      <template slot="right">
        <!--                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1; height: 100%;"></router-multi-view>-->
        <element-view style="height:100%" :initid="selectedRole" view-id="!defaultConsultation"></element-view>
      </template>
    </ank-splitter>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./RoleDevCenter.scss";
</style>
<script src="./RoleDevCenter.controller.js"></script>
