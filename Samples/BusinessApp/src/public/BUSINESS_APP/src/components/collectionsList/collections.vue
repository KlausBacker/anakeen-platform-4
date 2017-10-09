<template>
    <div :class="`documentsList__collections__wrapper${showCollections ? ' documentsList__collections--expanded': ''}`">
        <div class="documentsList__collections">
            <div class="user-info">
                <div class="documentsList__collections_button_icon documentsList__collections_button_icon--user">
                    {{userInitial}}
                </div>
                <div class="documentsList__collections_button_title documentsList__collections_button_title--user">
                    {{userFullName}}
                </div>
            </div>

            <div class="documentsList__collections_buttons documentsList__collections_buttons--top">
                <div class="documentsList__collections_button documentsList__collections_button--collapse" @click="onToggleCollections">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--collapse">
                        <i :class="`fa fa-angle-double-${showCollections ? 'left': 'right'}`"></i>
                    </div>
                    <div class="documentsList__collections_button_title documentsList__collections_button_title--collapse">
                        Réduire
                    </div>
                </div>
                <div class="documentsList__collections_button documentsList__collections_button--up">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--up">
                        <i class="fa fa-caret-up"></i>
                    </div>
                </div>
            </div>

            <div class="documentsList__collections__list" ref="listView"></div>

            <div class="documentsList__collections_buttons documentsList__collections_buttons--bottom">
                <div class="documentsList__collections_button documentsList__collections_button--down">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--down">
                        <i class="fa fa-caret-down"></i>
                    </div>
                </div>
                <div v-for="b in buttons" :class="`documentsList__collections_button documentsList__collections_button--${b.id}`"
                @click="b.click">
                    <div :class="`documentsList__collections_button_icon documentsList__collections_button_icon--${b.id}`">
                        <i :class="b.icon"></i>
                    </div>
                    <div :class="`documentsList__collections_button_title documentsList__collections_button_title--${b.id}`">
                        {{b.title}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script src="./collections.controller.js"></script>
