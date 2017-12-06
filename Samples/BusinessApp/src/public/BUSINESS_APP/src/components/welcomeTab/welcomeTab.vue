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
                    <i class="material-icons">remove_red_eye</i> {{translations.consultLabel}}
                </div>
                <div class="documentsList__documentsTabs__welcome__content__inner--content">
                    <div class="input-group">
                        <input type="text"
                               class="form-control documentsList__documentsTabs__welcome__content__open__input"
                               :placeholder="translations.searchPlaceholder" ref="documentsSearch"/>
                        <i class="input-group-addon material-icons documentsList__documentsTabs__welcome__content__open--remove" @click="onRemoveSearch">
                            close
                        </i>
                    </div>
                </div>
            </div>
            <div class="documentsList__documentsTabs__welcome__content__inner documentsList__documentsTabs__welcome__content__creation">
                <div class="documentsList__documentsTabs__welcome__content__inner--label">
                    <i class="material-icons">edit</i> {{translations.creationLabel}}
                </div>
                <div class="documentsList__documentsTabs__welcome__content__inner--content">
                    <div class="btn-group" ref="buttonGroup">
                        <button v-for="c in collectionsArray" class="documentsList__documentsTabs__welcome__collection__button btn-default" :data-famid="c.initid">
                            <div class="button-icon">
                                <img class="button-icon-img" :src="c.image_url">
                            </div>
                            <span class="button-label">{{c.html_label}}</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="documentsList__documentsTabs__welcome__content__inner documentsList__documentsTabs__welcome__content__recommended">
                <div class="documentsList__documentsTabs__welcome__content__inner--label">
                    <i class="material-icons">view_carousel</i> {{translations.recentConsultLabel}}
                </div>
                <div class="documentsList__documentsTabs__welcome__content__inner--content" ref="recentConsultLoading">
                    <table v-if="lastConsultations.length" class="documentsList__documentsTabs__welcome__content__inner--table-content table-bordered table-hover table-responsive" ref="recentConsult">
                        <thead>
                        <tr>
                            <th>{{translations.typeColumnLabel}}</th>
                            <th>{{translations.titleColumnLabel}}</th>
                            <th>{{translations.stepColumnLabel}}</th>
                            <th>{{translations.consultDateColumnLabel}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in lastConsultations" @click.capture="onRecentDocumentClick(c.properties)">
                                <td>{{c.properties.family.title}}</td>
                                <td>{{c.properties.title}}</td>
                                <td v-if="c.properties.state"><div :style="getStateTag(c.properties.state.color)"></div>{{c.properties.state.displayValue}}</td>
                                <td v-else="true"></td>
                                <td>{{getFormattedDate(c.utag.date)}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else="true">{{translations.noRecentConsult}}</div>
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
