<template>
    <div class="identity-component" ref="identity-component" v-if="">
        <div class="identity">
            <button class="identity-badge" @click="toggleSettingsPopup">
                <span class="identity-badge-initials" >{{ initials }}</span>
                <i class="identity-badge-icon fa fa-angle-down" v-if="emailAlterable || passwordAlterable"></i>
            </button>
            <div class="identity-badge-extension" v-if="large">
                <div class="identity-badge-extension-name">
                    {{ displayName }}
                </div>
                <div class="identity-badge-extension-email">
                    {{ email }}
                </div>
            </div>
        </div>
        <div ref="modificationPopup" class="identity-modification-popup" v-if="emailAlterable || passwordAlterable" v-show="false" >
            <ul>
                <li class="action" v-if="emailAlterable" @click="openEmailModifierWindow">{{ translations.emailChangeAction }}</li>
                <li class="action" v-if="passwordAlterable" @click="openPasswordModifierWindow">{{ translations.passwordChangeAction }}</li>
            </ul>
        </div>

        <div ref="emailModifier" class="identity-email-modifier" v-if="emailAlterable" v-show="false" >
            <form>
                <div class="form-group">
                    <div class="label">{{ translations.currentEmailLabel + " :" }}</div>
                    <div>{{ email || translations.noEmail }}</div>
                </div>
                <div class="form-group">
                    <div class="label">{{ translations.newEmailLabel + " :" }}</div>
                    <input type="text" ref="emailInput" class="identity-email-input form-control" :placeholder="translations.newEmailPlaceholder" v-model="newEmail" @keyup="removeEmailWarningMessage"/>
                </div>
                <div class="form-group">
                    <ank-authent-password ref="oldPasswordInputEmail" class="identity-old-password-input-email" :label="translations.oldPasswordLabel" :placeholder="translations.oldPasswordPlaceholder" @input="updateOldPassword" @keyup="removeEmailWarningMessage"></ank-authent-password>
                </div>
                <div class="form-group" v-if="emailWarningMessage">
                    <div class="alert alert-warning">{{ emailWarningMessage }}</div>
                </div>
                <button class="btn btn-primary identity-emailModifier--validate" @click.prevent="modifyUserEmail" :disabled="emailChangeButtonDisabled">{{ translations.validateEmailButtonLabel }}</button>
                <button class="btn identity-emailModifier--cancel" @click.prevent="closeEmailModifierWindow">{{ translations.cancelEmailButtonLabel }}</button>
            </form>
            <div ref="emailModifiedWindow" class="identity-email-modified-window" v-show="false">
                <div class="label">{{ translations.emailChangeSuccess }}</div>
                <button class="btn btn-primary identity-email--modified--accept" @click.prevent="closeEmailModifiedWindow" autofocus>{{ translations.closeButtonLabel }}</button>
            </div>
        </div>

        <div ref="passwordModifier" class="identity-password-modifier" v-if="passwordAlterable" v-show="false" >
            <form>
                <div class="form-group">
                    <ank-authent-password ref="oldPasswordInput" class="identity-old-password-input" :label="translations.oldPasswordLabel" :placeholder="translations.oldPasswordPlaceholder" @input="updateOldPassword" @keyup="updatePasswordChangeForm"></ank-authent-password>
                </div>
                <div class="form-group">
                    <ank-authent-password ref="passwordInput" class="identity-password-input" :label="translations.newPasswordLabel" :placeholder="translations.newPasswordPlaceholder" @input="updateNewPassword" @keyup="updatePasswordChangeForm"></ank-authent-password>
                </div>
                <div class="form-group">
                    <ank-authent-password ref="passwordConfirmationInput" class="identity-password-confirmation-input" :label="translations.newPasswordConfirmationLabel" :placeholder="translations.newPasswordConfirmationPlaceholder" @input="updateNewPasswordConfirmation" @keyup="updatePasswordChangeForm"></ank-authent-password>
                </div>
                <div class="form-group" v-if="passwordWarningMessage">
                    <div class="alert alert-warning">{{ passwordWarningMessage }}</div>
                </div>
                <button class="btn btn-primary identity-passwordModifier--validate" @click.prevent="modifyUserPassword" :disabled="passwordChangeButtonDisabled">{{ translations.validatePasswordButtonLabel }}</button>
                <button class="btn identity-passwordModifier--cancel" @click.prevent="closePasswordModifierWindow">{{ translations.cancelPasswordButtonLabel }}</button>
            </form>
            <div ref="passwordModifiedWindow" class="identity-password-modified-window" v-show="false">
                <div class="label">{{ translations.passwordChangeSuccess }}</div>
                <button class="btn btn-primary identity-password--modified--accept" @click.prevent="closePasswordModifiedWindow" autofocus>{{ translations.closeButtonLabel }}</button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './Identity.scss';
</style>

<script src="./Identity.component.js"></script>