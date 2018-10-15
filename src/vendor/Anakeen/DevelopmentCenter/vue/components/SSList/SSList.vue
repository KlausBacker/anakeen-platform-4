<template>
    <div :class="`smart-structure-list smart-structure-list-position--${position}`">
        <kendo-datasource ref="remoteDataSource"
            :transport-read="readData"
            :schema-data="parseData"
            :server-filtering="true"
            :schema-model="listModel"
        ></kendo-datasource>
        <div class="smart-structure-tabs">
            <div class="smart-structure-tabs-list">
                <div v-if="hasFilter" class="smart-structure-tabs-filter">
                    <input class="form-control k-textbox" type="search" v-model="listFilter"/>
                    <span class="filter-list-clear" @click="clearFilter"><i class="material-icons">close</i></span>
                </div>
                <kendo-listview class="smart-structure-tabs-list" ref="listview" :selectable="true" data-source-ref="remoteDataSource" :template="structureItemTemplate" :dataBound="onDataBound">
                </kendo-listview>
            </div>

            <router-multi-view class="smart-structure-tabs-content"></router-multi-view>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./SSList.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./SSList.component.js"></script>
