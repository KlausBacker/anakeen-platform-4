<template>
    <div class="ss-treelist">
        <kendo-treelist ref="ssTreelist"
                        class="ss-treelist-tree"
                        :data-source="remoteDataSource"
                        :height="'100%'"
                        :filterable="{ extra: false, operators: { string: { contains: 'Contains'}}}"
                        :resizable="true"
                        :sortable="true"
                        :dataBound="onDataBound"
                        :expand="onExpand"
                        :collapse="onCollapse"
                        :columnHide="onColumnHide"
                        :columnShow="onColumnShow"
                        :columnMenu="true"
                        :messages="{ noRows: messages }">
            <kendo-treelist-column v-for="item in items" :key="item.name" :template="columnTemplate(`${item.name}`)"
                                   :field="`${item.name}`" :title="`${item.label}`" :hidden="item.hidden">
            </kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "./SSTreeList.scss";
</style>
<script src="./SSTreeList.component.js"></script>