<template>
  <div class="ss-treelist">
    <kendo-treelist
      ref="ssTreelist"
      class="ss-treelist-tree"
      :data-source="remoteDataSource"
      :height="'100%'"
      :filterable="filterable"
      :resizable="resizable"
      :data-bound="onDataBound"
      :expand="onExpand"
      :collapse="onCollapse"
      :column-hide="onColumnHide"
      :column-show="onColumnShow"
      :column-menu="{ columns: true, filterable: false }"
      :messages="{ noRows: messages }"
    >
      <kendo-treelist-column
        v-for="item in items"
        :key="item.name"
        :template="columnTemplate(`${item.name}`)"
        :field="`${item.name}`"
        :title="`${item.label}`"
        :header-template="headerTemplate(item)"
        :hidden="item.hidden"
        :width="item.width"
      />
    </kendo-treelist>
  </div>
</template>
<style scoped lang="scss">
@import "./SSTreeList.scss";
</style>
<script src="./SSTreeList.component.js"></script>
