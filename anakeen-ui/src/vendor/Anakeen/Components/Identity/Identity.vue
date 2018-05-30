<template>
    <div>
        <div id="identity">
            <button id="identity-badge" class="identity-badge" @click="toggleSettingsPopup">
                <div id="identity-badge-initials" >{{ initials }}</div>
                <i id="identity-badge-icon" class="fa fa-angle-down" v-if="emailAlterable || passwordAlterable"></i>
            </button>
            <div id="identity-badge-extension" v-if="large">
                <div id="identity-badge-extension-name">
                    {{ displayName }}
                </div>
                <div id="identity-badge-extension-email">
                    {{ email }}
                </div>
            </div>
        </div>
        <div id="popup" v-if="emailAlterable || passwordAlterable" style="display: none;">
            <ul>
                <li class="action" v-if="emailAlterable" @click="openEmailModifierWindow">{{ translations.emailChangeAction }}</li>
                <li class="action" v-if="passwordAlterable" @click="openPasswordModifierWindow">{{ translations.passwordChangeAction }}</li>
            </ul>
        </div>

        <div id="emailModifier" v-if="emailAlterable" style="display: none;">
            <form>
                <div class="form-group">
                    <div class="label">{{ translations.currentEmailLabel + " :" }}</div>
                    <div>{{ email || translations.noEmail }}</div>
                </div>
                <div class="form-group">
                    <label class="label" for="emailInput">{{ translations.newEmailLabel + " :" }}</label>
                    <input id="emailInput" type="text" class="form-control" v-model="newEmail" @keyup="removeEmailWarningMessage($event)"/>
                </div>
                <div class="form-group" v-if="emailWarningMessage">
                    <div class="alert alert-warning">{{ emailWarningMessage }}</div>
                </div>
                <button class="btn btn-primary" @click.prevent="modifyUserEmail">{{ translations.validateEmailButtonLabel }}</button>
                <button class="btn" @click.prevent="closeEmailModifierWindow">{{ translations.cancelEmailButtonLabel }}</button>
            </form>
        </div>

        <div id="passwordModifier" v-if="passwordAlterable" style="display: none;">
            <form>
                <div class="form-group">
                    <label class="label" for="passwordInput">{{ translations.newPasswordLabel + " :" }}</label>
                    <input id="passwordInput" type="password" class="form-control" v-model="newPassword" @keyup="removePasswordWarningMessage($event)"/>
                </div>
                <div class="form-group">
                    <label class="label" for="passwordConfirmationInput">{{ translations.newPasswordConfirmationLabel + " :" }}</label>
                    <input id="passwordConfirmationInput" type="password" class="form-control" v-model="newPasswordConfirmation" @keyup="removePasswordWarningMessage($event)"/>
                </div>
                <div class="form-group" v-if="passwordWarningMessage">
                    <div class="alert alert-warning">{{ passwordWarningMessage }}</div>
                </div>
                <button class="btn btn-primary" @click.prevent="modifyUserPassword">{{ translations.validatePasswordButtonLabel }}</button>
                <button class="btn" @click.prevent="closePasswordModifierWindow">{{ translations.cancelPasswordButtonLabel }}</button>
            </form>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './Identity.scss';
</style>

<script src="./Identity.component.js"></script>