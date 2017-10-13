<template>
    <section class="authent-component">
        <form ref="authentForm" class="authent-form">
            <label class="label" for="login" translate-context="Authent" v-translate>Identifier :</label>
            <input id="login" class="authent-login form-control k-textbox" type="text"
                   v-model="login"
                   required autocapitalize="off" autocorrect="off" spellcheck="false"
                   :placeholder="translations.loginPlaceHolder"
                   :data-validationmessage="translations.validationMessageIdentifier"/>

            <label class="label" for="password" translate-context="Authent" v-translate>Password :</label>
            <span class="input-group-btn">
                <input id="password" ref="authentPassword"
                       class="authent-pwd form-control"
                       type="password"
                       v-model="pwd"
                       :placeholder="translations.passwordPlaceHolder"
                       required autocapitalize="off" autocorrect="off"
                       :data-validationmessage="translations.validationMessagePassword"/>
                <button class="btn btn-reveal btn-secondary" type="button" v-if="hidePassword"><i
                        class="fa fa-eye"></i></button>
                <button class="btn btn-reveal btn-secondary" type="button" v-if="!hidePassword"><i
                        class="fa fa-eye-slash"></i></button>
            </span>
            <div class="message msgerr" v-if="wrongPassword" >
                {{ authentError }}
            </div>
            <div class="authent-buttons">
                <button ref="loginButton" class="authent-login-button k-primary" type="submit">
                    <translate translate-context="Authent">Sign in</translate>
                </button>
            </div>
        </form>
        <div class="authent-bottom">

            <select ref="authentLocale" class="authent-locale" name="language" v-model="$language.current">
                <option v-for="language in availableLanguages" :value="language.key">{{ language.label }}</option>
            </select>
            <button ref="authentHelpButton" class="authent-help-button k-secondary">
                <translate translate-context="Authent">Help</translate>
            </button>
            <button ref="authentForgetButton" class="authent-forget-button k-secondary">
                <translate translate-context="Authent">Forget password ?</translate>
            </button>
        </div>
        <div class="authent-help" ref="authentHelpContent" style="display:none">
            <p class="label" for="password" translate-context="Authent" v-translate>Help Content</p>
        </div>
        <form ref="authentForgetForm" class="authent-form authent-form--forget" style="display:none">

            <label class="label" for="forgetlogin" translate-context="Authent" v-translate>Enter identifier or email address :</label>
            <input id="forgetlogin" class="authent-login form-control k-textbox" type="text"
                   v-model="login"
                   required autocapitalize="off" autocorrect="off" spellcheck="false"
                   :placeholder="translations.forgetPlaceHolder" />


            <div class="message msgerr" v-if="forgetStatusFailed" >
                {{ forgetError }}
            </div>
            <div class="authent-buttons">
                <button ref="authentForgetSubmit" class="authent-login-button k-primary" type="submit">
                    <translate translate-context="Authent">Send reset password ask</translate>
                </button>
            </div>
        </form>
    </section>
</template>
<script src="./Authent.component.js"></script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
    @import "./Authent.scss";
</style>
