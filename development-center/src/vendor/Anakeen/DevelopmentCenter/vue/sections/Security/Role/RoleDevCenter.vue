<template>
  <div class="security-roles-section">
    <ank-splitter class="roles-splitter" ref="rolesSplitter" :panes="panes" localStorageKey="security-roles-splitter">
      <template slot="left">
        <ank-se-grid
          ref="roleContent"
          class="role-content"
          collection="ROLE"
          :pageable="{ pageSizes: [100, 200, 500], pageSize: 100 }"
          @RowActionClick="selectRole"
          filterable
          :actions="actions"
          :columns="columns"
          @DataBound="onGridDataBound"
          @GridError="onGridError"
        >
        </ank-se-grid>
      </template>
      <template slot="right">
        <!--                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1; height: 100%;"></router-multi-view>-->
        <element-view style="height:100%" :initid="selectedRole" viewId="!defaultConsultation"></element-view>
      </template>
    </ank-splitter>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./RoleDevCenter.scss";
</style>
<script src="./RoleDevCenter.controller.js"></script>
