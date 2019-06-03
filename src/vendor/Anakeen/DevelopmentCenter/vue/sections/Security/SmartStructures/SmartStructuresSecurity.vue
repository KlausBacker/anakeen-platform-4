<template>
    <div class="security-ss-section">
        <ss-list
                position="left"
                :selected="selectedStructure"
                @item-clicked="onItemClicked"
                @list-ready="onListReady"
        >
        </ss-list>
        <div class="security-ss-content">
            <router-tabs :ref="listItem.name" @hook:mounted="onTabsMounted(listItem.name)" @tab-selected="onTabSelected" v-for="(listItem, index) in listContent" :key="index" v-show="listItem && listItem.name === selectedStructure" :tabs="tabs">
                <template v-slot="slotProps">
                    <component :ref="`${listItem.name}-${slotProps.tab.name}`" @navigate="onChildNavigate" @hook:mounted="onSubComponentMounted(listItem.name, slotProps.tab.name)" :is="slotProps.tab.component" :ssName="listItem.name" :ssSection="ssSection"></component>
                </template>
            </router-tabs>
            <div class="security-ss-empty" v-if="!selectedStructure">
                <span class="k-icon k-i-folder-open security-ss-empty-icon"></span>
                <span class="security-ss-empty-text">Select a structure</span>
            </div>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./SmartStructuresSecurity.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./SmartStructuresSecurity.controller.js"></script>
