<template>
    <div class="ss-treelist">
        <div>
            <kendo-toolbar class="ss-treelist-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshTree"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
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
                        :columnMenu="true"
                        :messages="{ noRows: 'There are no data for this Smart Structure...' }">
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
