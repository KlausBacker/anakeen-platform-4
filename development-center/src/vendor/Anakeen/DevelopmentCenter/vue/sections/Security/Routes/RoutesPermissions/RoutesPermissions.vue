<template>
  <div class="routes-permissions-parent">
    <kendo-datasource
      ref="routesPermissions"
      :transport-read="getPermissions"
      :server-paging="true"
      :pageable="true"
      :page-size="100"
      :sort="[
        { field: 'accessNs', dir: 'asc' },
        { field: 'accessName', dir: 'asc' }
      ]"
      :schema-data="parsePermissionsData"
      :schema-total="parsePermissionsTotal"
    >
    </kendo-datasource>
    <kendo-grid
      ref="routesPermissionsContent"
      class="routes-permissions-content"
      :data-source-ref="'routesPermissions'"
      :pageable="{ pageSizes: [100, 200, 500] }"
      :filterable-mode="'row'"
      :filterable-extra="false"
      :sortable="true"
      @databound="onGridDataBound"
    >
      <kendo-grid-column
        :field="'accessNs'"
        :title="'<b>Namespace</b>'"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
      ></kendo-grid-column>
      <kendo-grid-column
        :field="'accessName'"
        :title="'<b>Name</b>'"
        :template="displayLink('accessName')"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
      ></kendo-grid-column>
      <kendo-grid-column
        :field="'account.reference'"
        :title="'<b>Account</b>'"
        :template="displayLink('account')"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
      ></kendo-grid-column>
    </kendo-grid>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "RoutesPermissions.scss";
</style>
<script src="./RoutesPermissions.controller.js"></script>
