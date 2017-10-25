<template>
    <div class="documentsList__documentsTabs__wrapper" ref="tabsWrapper">
        <div class="documentsList__documentsTabs__tabs__buttons">
            <slot name="leftContent">
                <ul ref="newActionButton" class="documentsList__documentsTabs__tabs__new"></ul>
            </slot>
            <slot name="rightContent">
                <div ref="tabsPaginator" class="documentsList__documentsTabs__tabs__paginator">
                    <ul class="documentsList__documentsTabs__tabs__paginator__dropdown__button documentsList__documentsTabs__tabs__paginator__list"
                        ref="tabsList"
                        data-role="menu"
                        data-open-on-click="true"
                        data-bind="events: {select: onSelectTabList}">
                        <li>
                            <span class="k-icon k-i-menu"></span>
                            <ul class="documentsList__documentsTabs__tabs__paginator__list__content"
                                data-bind="source: documents"
                                data-template="tabListTemplate">
                            </ul>
                        </li>
                    </ul>
                    <button class="documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__close">
                        <span class="k-icon k-i-x"></span>
                    </button>
                </div>
            </slot>
        </div>
        <div class="documentsList__documentsTabs__empty" v-if="emptyData">
            <img class="documentsList__documentsTabs__empty__img" src="CORE/Images/anakeen-logo.svg"/>
        </div>
        <div v-show="!emptyData" class="documentsList__documentsTabs__tabs__wrapper">
            <div class="documentsList__documentsTabs__tabs"
                 data-role="tabstrip"
                 data-animation="false"
                 ref="tabstrip"
                 data-bind="source: documents"
                 data-text-field="tabHeader"
                 data-content-field="tabContent"
            >
            </div>
        </div>
        <script id="tabListTemplate" type="text/x-kendo-template">
            <li class="documentsList__documentsTabs__tabs__paginator__list__item">
                #= tabHeader#
            </li>
        </script>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./documentsTabs.scss";
</style>
<script src="./documentsTabs.controller.js"></script>
