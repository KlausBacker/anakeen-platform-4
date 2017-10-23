<template>
    <div class="documentsList__openDocuments__wrapper" ref="wrapper">
        <div class="documentsList__documentsTabs__tabs__buttons">
            <ul ref="newActionButton"></ul>
            <div ref="tabsPaginator" class="documentsList__documentsTabs__tabs__paginator">
                <button class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__prev">
                    <span class="k-icon k-i-arrow-left"></span>
                </button>
                <button class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__next">
                    <span class="k-icon k-i-arrow-right"></span>
                </button>
                <button class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__list">
                    <span class="k-icon k-i-menu"></span>
                </button>
                <button class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__close">
                    <span class="k-icon k-i-x"></span>
                </button>
            </div>
        </div>
        <div class="documentsList__openDocuments__empty" v-if="emptyData">
            <img class="documentsList__openDocuments__empty__img" src="CORE/Images/anakeen-logo.svg"/>
        </div>
        <div v-show="!emptyData" class="documentsList__documentsTabs__tabs__wrapper">
            <div class="documentsList__documentsTabs__tabs" ref="tabstrip">
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    @import './openDocument.scss';
</style>
<script src="./openDocument.controller.js"></script>