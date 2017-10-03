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


            <div class="documentsList__collections_buttons documentsList__collections_buttons--bottom">
                <div class="documentsList__collections_button documentsList__collections_button--down">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--down">
                        <i class="fa fa-caret-down"></i>
                    </div>
                </div>
                <div class="documentsList__collections_button documentsList__collections_button--notif">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--notif"></div>
                    <div class="documentsList__collections_button_title documentsList__collections_button_title--notif">
                        Notifications
                    </div>
                </div>
                <div class="documentsList__collections_button documentsList__collections_button--settings">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--settings"></div>
                    <div class="documentsList__collections_button_title documentsList__collections_button_title--settings">
                        Paramètres
                    </div>
                </div>
                <div class="documentsList__collections_button documentsList__collections_button--state">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--state"></div>
                    <div class="documentsList__collections_button_title documentsList__collections_button_title--state">
                        Synchronisé
                    </div>
                </div>
                <div class="documentsList__collections_button documentsList__collections_button--disconnect">
                    <div class="documentsList__collections_button_icon documentsList__collections_button_icon--disconnect"></div>
                    <div class="documentsList__collections_button_title documentsList__collections_button_title--disconnect">
                        Déconnexion
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script src="./collections.controller.js"></script>
