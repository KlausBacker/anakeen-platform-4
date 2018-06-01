<template>
    <div>
        <div id="identity">
            <button id="identity-badge" class="identity-badge" @click="toggleSettingsPopup">
                <span id="identity-badge-initials" >{{ initials }}</span>
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
                    <input id="emailInput" type="text" class="form-control" v-model="newEmail" @keyup="removeEmailWarningMessage"/>
                </div>
                <div class="form-group" v-if="emailWarningMessage">
                    <div class="alert alert-warning">{{ emailWarningMessage }}</div>
                </div>
                <button class="btn btn-primary" @click.prevent="modifyUserEmail" :disabled="emailChangeButtonDisabled">{{ translations.validateEmailButtonLabel }}</button>
                <button class="btn" @click.prevent="closeEmailModifierWindow">{{ translations.cancelEmailButtonLabel }}</button>
            </form>
            <div id="emailModifiedWindow" style="display: none;">
                <div class="label">{{ translations.emailChangeSuccess }}</div>
                <button class="btn btn-primary" @click.prevent="closeEmailModifiedWindow" autofocus>{{ translations.closeButtonLabel }}</button>
            </div>
        </div>

        <div id="passwordModifier" v-if="passwordAlterable" style="display: none;">
            <form>
                <ank-authent-password id="oldPasswordInput" :label="translations.oldPasswordLabel" :placeholder="translations.oldPasswordPlaceholder" @input.stop="updateOldPassword" @keyup="removePasswordWarningMessage"></ank-authent-password>
                <ank-authent-password id="passwordInput" :label="translations.newPasswordLabel" :placeholder="translations.newPasswordPlaceholder" @input.stop="updateNewPassword" @keyup="removePasswordWarningMessage"></ank-authent-password>
                <ank-authent-password id="passwordConfirmationInput" :label="translations.newPasswordConfirmationLabel" :placeholder="translations.newPasswordConfirmationPlaceholder" @input.stop="updateNewPasswordConfirmation" @keyup="removePasswordWarningMessage"></ank-authent-password>
                <div class="form-group" v-if="passwordWarningMessage">
                    <div class="alert alert-warning">{{ passwordWarningMessage }}</div>
                </div>
                <button class="btn btn-primary" @click.prevent="modifyUserPassword" :disabled="passwordChangeButtonDisabled">{{ translations.validatePasswordButtonLabel }}</button>
                <button class="btn" @click.prevent="closePasswordModifierWindow">{{ translations.cancelPasswordButtonLabel }}</button>
            </form>
            <div id="passwordModifiedWindow" style="display: none;">
                <div class="label">{{ translations.passwordChangeSuccess }}</div>
                <button class="btn btn-primary" @click.prevent="closePasswordModifiedWindow" autofocus>{{ translations.closeButtonLabel }}</button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './Identity.scss';
</style>

<script src="./Identity.component.js"></script>