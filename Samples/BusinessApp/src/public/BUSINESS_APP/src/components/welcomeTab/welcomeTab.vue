<template>
    <div class="documentsList__documentsTabs__welcome" ref="welcomeTabWrapper">
        <div class="documentsList__documentsTabs__welcome__header">
            <h1 class="documentsList__documentsTabs__welcome__header__title">
                <span class="documentsList__documentsTabs__welcome__user">
                    {{userName}},
                </span>
                <span v-if="welcomeMessage" class="documentsList__documentsTabs__welcome__message">
                       {{welcomeMessage}}
                </span>
            </h1>
        </div>
        <div class="documentsList__documentsTabs__welcome__content">
            <h1 v-if="promptMessage" class="documentsList__documentsTabs__welcome__prompt__message">
                {{promptMessage}}
            </h1>
            <div class="documentsList__documentsTabs__welcome__content__inner documentsList__documentsTabs__welcome__content__open">
                <div class="documentsList__documentsTabs__welcome__content__inner--label">
                    <i class="material-icons">remove_red_eye</i> Consultation
                </div>
                <div class="documentsList__documentsTabs__welcome__content__inner--content">
                    <div class="input-group">
                        <input type="text"
                               class="form-control documentsList__documentsTabs__welcome__content__open__input"
                               placeholder="Rechercher" ref="documentsSearch"/>
                        <i class="input-group-addon material-icons documentsList__documentsTabs__welcome__content__open--remove" @click="onRemoveSearch">
                            close
                        </i>
                        <i class="input-group-addon material-icons documentsList__documentsTabs__welcome__content__open--search">
                            search
                        </i>
                    </div>
                </div>
            </div>
            <div class="documentsList__documentsTabs__welcome__content__inner documentsList__documentsTabs__welcome__content__creation">
                <div class="documentsList__documentsTabs__welcome__content__inner--label">
                    <i class="material-icons">create_new_folder</i> Création
                </div>
                <div class="documentsList__documentsTabs__welcome__content__inner--content">
                    <i class="material-icons">keyboard_arrow_left</i>
                    <div class="btn-group" ref="buttonGroup">
                        <button v-for="c in collectionsArray" class="documentsList__documentsTabs__welcome__collection__button btn-default" :data-famid="c.initid">
                            <img :src="c.image_url">
                            <span>{{c.html_label}}</span>
                        </button>
                    </div>
                    <i class="material-icons">keyboard_arrow_right</i>
                </div>
            </div>
            <div class="documentsList__documentsTabs__welcome__content__inner documentsList__documentsTabs__welcome__content__recommended">
                <div class="documentsList__documentsTabs__welcome__content__inner--label">
                    <i class="material-icons">view_carousel</i> Dernières consultations
                </div>
                <div class="documentsList__documentsTabs__welcome__content__inner--content" ref="recentConsultLoading">
                    <table v-if="lastConsultations.length" class="documentsList__documentsTabs__welcome__content__inner--table-content table-bordered table-hover table-responsive" ref="recentConsult">
                        <thead>
                        <tr>
                            <th>Collection</th>
                            <th>Titre</th>
                            <th>Etat</th>
                            <th>Dernière Modification</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in lastConsultations" @click.capture="onRecentDocumentClick(c.properties)">
                                <td><img :src="c.utag.icon"/> {{c.properties.family.title}}</td>
                                <td>{{c.properties.title}}</td>
                                <td v-if="c.properties.state"><div :style="getStateTag(c.properties.state.color)"></div>{{c.properties.state.displayValue}}</td>
                                <td v-else="true"></td>
                                <td>{{c.utag.date}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else="true">Aucune consultations récentes</div>
                </div>

            </div>
        </div>
        <div class="documentsList__documentsTabs__welcome__footer">
            <img class="documentsList__documentsTabs__welcome__footer__icon" src="CORE/Images/anakeenplatform-logo-fondblanc.svg"/>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./welcomeTab.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./welcomeTab.controller.js"></script>
