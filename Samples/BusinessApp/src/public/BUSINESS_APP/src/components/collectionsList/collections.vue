<template>
    <div :class="`documentsList__collections__wrapper${showCollections ? ' documentsList__collections--expanded': ''}`" ref="wrapper">
        <div class="documentsList__collections">
            <div class="user-info" @click.capture="onToggleCollections">
                <div class="documentsList__collections__button__icon documentsList__collections__button__icon--user" @click="openAccount">
                    <transition name="fade" mode="out-in">
                        <div key="collapsed" class="documentsList__collections__button__icon--user__icon--collapsed" v-if="!showCollections">
                            <img  class="documentsList__collections__button__icon--user__icon" src="api/v1/images/assets/sizes/50x50c/showcase_user.png"/>
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
                <div v-show="seeReporting" class="documentsList__collections__slot__buttons">
                    <div v-if="isDirecteur" @click="onClickToExceed">
                        <div class="button__icon">
                            <i class="material-icons">vertical_align_top</i>
                            <div class="button__notification__counter">{{notificationCounter.exceeds}}</div>
                        </div>
                        <div class="button__title">
                            Notes de frais en dépassement
                        </div>

                    </div>
                    <div v-if="isDirecteur" @click="onClickToValidate">
                        <div class="button__icon">
                            <i class="material-icons">present_to_all</i>
                            <div class="button__notification__counter">{{notificationCounter.toValidate}}</div>
                        </div>
                        <div class="button__title">
                            Notes de frais à valider
                        </div>
                    </div>
                    <div v-if="seeReporting" @click="onClickReporting">
                        <div class="button__icon">
                            <i class="material-icons">insert_chart</i>
                        </div>
                        <div class="button__title">
                            Reporting
                        </div>
                    </div>
                </div>
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
        <span ref="showcaseNotification" style="display: none"></span>
    </div>
</template>
<style lang="scss">
    @import './collections.scss';
</style>
<script src="./collections.controller.js"></script>
