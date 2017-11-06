<template>
    <div class="documentsList__documentsTabs__wrapper" ref="tabsWrapper">
        <div class="documentsList__documentsTabs__tabs__buttons">
                <div class="documentsList__documentsTabs__tabs__custom__slot" ref="slotContent">
                    <slot name="left-content"></slot>
                </div>
                <div ref="tabsPaginator" class="documentsList__documentsTabs__tabs__paginator">
                    <input class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__dropdown__button" ref="tabsList"/>
                    <button @click="closeAllDocuments" class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__close">
                        <span class="k-icon k-i-x"></span>
                    </button>
                </div>
        </div>
        <div class="documentsList__documentsTabs__empty" v-if="emptyState">
            <img class="documentsList__documentsTabs__empty__img" :src="emptyImg"/>
        </div>
        <div v-show="!emptyState" class="documentsList__documentsTabs__tabs__wrapper">
            <div  ref="tabstrip" class="documentsList__documentsTabs__tabs"></div>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./documentTabs.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./documentTabs.controller.js"></script>
