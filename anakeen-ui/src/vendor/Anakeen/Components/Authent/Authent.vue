<template>
    <section class="authent-component" ref="authentComponent">


        <div class="euthent-form-connect" v-if="!resetPassword">
            <form ref="authentForm" class="authent-form">
                <label class="label" for="login" translate-context="Authent" v-translate>Identifier :</label>
                <input id="login" class="authent-login form-control k-textbox" type="text"
                       v-model="login"
                       required autocapitalize="off" autocorrect="off" spellcheck="false"
                       :placeholder="translations.loginPlaceHolder"
                       :validationmessage="translations.validationMessageIdentifier"/>

                  <ank-password :label="translations.passwordLabel"
                               :validationMessage="translations.validationMessagePassword"
                               :placeholder="translations.passwordPlaceHolder"
                               v-model="pwd" />



                <div class="message message--error" v-if="wrongPassword">
                    {{ authentError }}
                </div>
                <div class="authent-buttons">
                    <button ref="loginButton" class="authent-login-button k-primary" type="submit">
                        <translate translate-context="Authent">Sign in</translate>
                    </button>
                </div>
            </form>

            <div class="authent-help" ref="authentHelpContent" style="display:none">
                <p class="label" for="password" translate-context="Authent" v-translate>Help Content</p>
            </div>
            <form ref="authentForgetForm" class="authent-form authent-form--forget" style="display:none" >

                <label class="label" for="forgetlogin" translate-context="Authent" v-translate>Enter identifier or email address :</label>
                <input id="forgetlogin" class="authent-login form-control k-textbox" type="text"
                       v-model="login"
                       required autocapitalize="off" autocorrect="off" spellcheck="false"
                       :placeholder="translations.forgetPlaceHolder"/>

                <div class="message message--error" v-if="forgetStatusFailed">
                    {{ forgetError }}
                </div>
                <div class="message message--success" v-if="!forgetStatusFailed">
                    {{ forgetSuccess }}
                </div>
                <div class="authent-buttons">
                    <button ref="authentForgetSubmit" class="authent-login-button k-primary" type="submit">
                        <translate translate-context="Authent">Send reset password ask</translate>
                    </button>
                </div>
            </form>
        </div>


        <form ref="authentResetPasswordForm" class="authent-form authent-form--resetpassword" v-if="resetPassword">


            <ank-password :label="translations.resetPasswordLabel" v-if="resetStatusFailed"
                         :validationMessage="translations.validationMessagePassword"
                         :placeholder="translations.passwordPlaceHolder"
                         v-model="resetPwd1" />
            <ank-password :label="translations.confirmPasswordLabel" v-if="resetStatusFailed"
                         :validationMessage="translations.validationMessagePassword"
                         :placeholder="translations.passwordPlaceHolder"
                         v-model="resetPwd2" />


            <div class="message message--error" v-if="resetStatusFailed">
                {{ resetError }}
            </div>
            <div class="message message--success" v-if="!resetStatusFailed">
                {{ resetSuccess }}
            </div>
            <div class="authent-buttons">
                <button ref="authentResetSubmit" class="authent-login-button k-primary" type="submit" v-if="resetStatusFailed">
                    <translate translate-context="Authent">Send reset password ask</translate>
                </button>
                <button ref="authentGoHome" class="authent-login-home k-primary" v-if="!resetStatusFailed">
                    <translate translate-context="Authent">Go back to home page</translate>
                </button>
            </div>
        </form>

        <div class="authent-bottom">

            <select ref="authentLocale" class="authent-locale" name="language" v-model="$language.current">
                <option v-for="language in availableLanguages" :value="language.key">{{ language.label }}</option>
            </select>
            <button ref="authentHelpButton" class="authent-help-button k-secondary"  v-if="!resetPassword">
                <translate translate-context="Authent">Help</translate>
            </button>
            <button ref="authentForgetButton" class="authent-forget-button k-secondary"  v-if="!resetPassword">
                <translate translate-context="Authent">Forget password ?</translate>
            </button>
        </div>
    </section>
</template>
<script src="./Authent.component.js"></script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
    @import "./Authent.scss";
</style>
