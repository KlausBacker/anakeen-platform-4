<template>
    <div :class="`documentsList__collections__wrapper${showCollections ? ' documentsList__collections--expanded': ''}`">
        <div class="documentsList__collections">
            <div class="user-info">
                <div class="documentsList__collections_button_icon documentsList__collections_button_icon--user">
                    Dp
                </div>
                <div class="documentsList__collections_button_title documentsList__collections_button_title--user">
                    Dynacase platform Master
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

            <div class="list-group documentsList__collections__list">
                <div v-for="c in collections">
                    <div class="list-group-item documentsList__collectionCard " data-collection-ref="CSTB_CLIENT">
                        <div class="list-group-item-heading documentsList__collectionCard__heading">
                            <div class="documentsList__collectionCard__heading__content">
                                <div class="documentsList__collectionCard__heading__content_icon">
                                    <img :src="c.image_url" class="documentsList__collectionCard__heading__content_icon_img">
                                </div>
                                <div class="documentsList__collectionCard__heading__content_label">
                                    {{c.html_label}}
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item-text documentsList__collectionCard__text">
                        </div>
                    </div>
                </div>
            </div>

            <div class="documentsList__collections_buttons documentsList__collections_buttons--bottom">
                <div class="documentsList__collections_button documentsList__collections_button--down">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--down">
                        <i class="fa fa-caret-down"></i>
                    </div>
                </div>
                <div v-for="b in buttons" :class="`documentsList__collections_button documentsList__collections_button--${b.id}`" @click="fetchCollections">
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
