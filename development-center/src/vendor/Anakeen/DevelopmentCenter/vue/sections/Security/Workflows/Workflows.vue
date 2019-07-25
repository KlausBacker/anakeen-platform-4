<template>
    <div class="security-workflows-section">
        <div class="security-workflows-section-content">
            <ss-list listUrl="/api/v2/devel/smart/workflows/" :filter="{ placeholder: 'Search a workflow'}"
                            vendorCategory="auto"
                            :selected="selectedWorkflow"
                            @item-clicked="onItemClicked"
                            @list-ready="onListReady"
            ></ss-list>
            <router-tabs :ref="listItem.name" @hook:mounted="onTabsMounted(listItem.name)" @tab-selected="onTabSelected" v-for="listItem in listContent" :key="listItem.name || listItem.id" v-show="listItem && listItem.name === selectedWorkflow" :tabs="tabs">
                <template v-slot="slotProps">
                    <component :is="slotProps.tab.component" :workflowId="listItem.name || listItem.id"></component>
                </template>
            </router-tabs>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./Workflows.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./Workflows.controller.js"></script>
