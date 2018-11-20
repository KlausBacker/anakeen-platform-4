<template>
    <div class="ss-treelist">
        <kendo-treelist ref="ssTreelist"
                        class="ss-treelist-tree"
                        :data-source="remoteDataSource"
                        :height="'100%'"
                        :filterable="filterable"
                        :resizable="true"
                        :sortable="sortable"
                        :dataBound="onDataBound"
                        :expand="onExpand"
                        :collapse="onCollapse"
                        :columnHide="onColumnHide"
                        :columnShow="onColumnShow"
                        :columnMenu="true"
                        :messages="{ noRows: messages }">
            <kendo-treelist-column v-for="item in items" :key="item.name" :template="columnTemplate(`${item.name}`)"
                                   :field="`${item.name}`" :title="`${item.label}`" :headerTemplate="headerTemplate(item)" :hidden="item.hidden" :width="item.width">
            </kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style scoped lang="scss">
    @import "./SSTreeList.scss";
</style>
<script src="./SSTreeList.component.js"></script>