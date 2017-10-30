<template>
    <div class="documentsList__documentsTabs__wrapper">
        <div class="documentsList__documentsTabs__tabs__buttons">
                <div ref="tabsPaginator" class="documentsList__documentsTabs__tabs__paginator">
                    <ul class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__dropdown__button" data-role="menu">
                        <li>
                            <span class="k-icon k-i-menu"></span>
                        </li>
                    </ul>
                    <button @click="closeAllDocuments" class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__close">
                        <span class="k-icon k-i-x"></span>
                    </button>
                </div>
        </div>
        <div class="documentsList__documentsTabs__empty" v-if="emptyState">
            <img class="documentsList__documentsTabs__empty__img" src="CORE/Images/anakeen-logo.svg"/>
        </div>
        <div v-show="!emptyState" class="documentsList__documentsTabs__tabs__wrapper">
            <div  ref="tabstrip" class="documentsList__documentsTabs__tabs"></div>
        </div>
        <button @click="debugAddTab" style="position: absolute; bottom: 0; z-index: 200;">Add tab</button>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./tabsComponent.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./tabsComponent.controller.js"></script>
