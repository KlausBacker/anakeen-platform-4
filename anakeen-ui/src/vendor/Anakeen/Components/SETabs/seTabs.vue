<template>
    <div class="seTabs__wrapper" ref="tabsWrapper">
        <div class="seTabs__tabs__buttons">
                <div class="seTabs__tabs__custom__slot" ref="slotContent">
                    <slot name="left-content"></slot>
                </div>
                <div ref="tabsTools" class="seTabs__tabs__paginator">
                    <input class="seTabs__tabs__paginator__button seTabs__tabs__paginator__dropdown__button" ref="tabsList"/>
                </div>
        </div>
        <div class="seTabs__empty" v-if="!hasContent">
            <slot name="empty-content">
                <img class="seTabs__empty__img" :src="defaultEmptyImgUrl"/>
            </slot>
        </div>
        <div class="seTabs__tabs__wrapper">
            <div  ref="tabstrip" class="seTabs__tabs"></div>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./seTabs.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./seTabs.component.js"></script>
