<template>
    <div :class="`documentsList__collections__wrapper${showCollections ? ' documentsList__collections--expanded': ''}`" ref="wrapper">
        <div class="documentsList__collections">
            <div class="user-info" @click.capture="onToggleCollections">
                <div class="documentsList__collections__button__icon documentsList__collections__button__icon--user" @click="openAccount">
                    <transition name="fade" mode="out-in">
                        <div key="collapsed" class="documentsList__collections__button__icon--user__icon--collapsed" v-if="!showCollections">
                            <i  class="documentsList__collections__button__icon--user__icon material-icons">account_circle</i>
                            <span class="documentsList__collections__button__icon--user__initial">
                                {{userInitial}}
                            </span>
                        </div>
                        <div class="documentsList__collections__button__icon--user__icon--expanded" v-else="true" key="expanded">
                            <i class="documentsList__collections__button__icon--user__icon material-icons">settings</i>
                        </div>
                    </transition>
                </div>
                <div class="documentsList__collections__button__title documentsList__collections__button__title--user">
                    {{userFullName}}
                </div>
            </div>

            <div class="documentsList__collections__buttons documentsList__collections__buttons--top">
                <div class="documentsList__collections__button documentsList__collections__button--collapse" @click="onToggleCollections">
                    <div class="documentsList__collections__button__icon documentsList__collections__button__icon--collapse">
                        <i class="material-icons">keyboard_arrow_{{showCollections ? 'left': 'right'}}</i>
                    </div>
                </div>
            </div>

            <div class="documentsList__collections__list" ref="listView"></div>

            <div class="documentsList__collections_buttons documentsList__collections__buttons--bottom">
                <slot name="bottomButton"></slot>
                <div v-for="b in buttons" class="documentsList__collections__button"
                @click="b.click">
                    <div class="documentsList__collections__button__icon">
                        <i :class="b.icon"></i>
                    </div>
                    <div class="documentsList__collections__button__title">
                        {{b.title}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    @import './collections.scss';
</style>
<script src="./collections.controller.js"></script>
