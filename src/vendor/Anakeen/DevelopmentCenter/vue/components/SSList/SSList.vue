<template>
    <div :class="`smart-structure-list smart-structure-list-position--${position}`">
        <kendo-datasource ref="remoteDataSource"
            :transport-read="readData"
            :schema-data="parseData"
            :schema-model="listModel"
        ></kendo-datasource>
        <div class="smart-structure-tabs">
            <div class="smart-structure-tabs-list">
                <div v-if="hasFilter" class="smart-structure-tabs-filter">
                    <input class="form-control k-textbox" type="search" :placeholder="translations.listFilterPlaceholder" v-model="listFilter"/>
                    <span class="filter-list-clear" @click="clearFilter"><i class="material-icons">close</i></span>
                </div>
                <div class="smart-structure-tabs-list-nav">
                    <router-link tag="div" :to="{name: routeName, params: { [routeParamField]: tab.name }}" v-for="(tab, index) in tabs" :key="`tab-${index}`" :class="{'smart-structure-list-item': true}" :title="tab.title">
                        <img class="smart-structure-list-item-icon" :src='tab.icon'/>
                        <div class="smart-structure-list-item-title">{{tab.name}}</div>
                    </router-link>
                </div>
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
