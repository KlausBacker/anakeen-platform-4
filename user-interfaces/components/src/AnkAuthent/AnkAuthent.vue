<template>
  <section class="authent-component" ref="authentComponent">
    <div class="authent-form-connect" v-if="!resetPassword">
      <form ref="authentForm" class="authent-form">
        <label class="label" for="login">{{ $t("authent.identifier :") }}</label>
        <input
          id="login"
          class="authent-login form-control k-textbox"
          type="text"
          v-model="login"
          required
          autocapitalize="off"
          autocorrect="off"
          spellcheck="false"
          autofocus="true"
          :placeholder="translations.loginPlaceHolder"
          :validationmessage="translations.validationMessageIdentifier"
        />
        <ank-password
          :label="translations.passwordLabel"
          :validationMessage="translations.validationMessagePassword"
          :placeholder="translations.passwordPlaceHolder"
          v-model="pwd"
        />
        <div class="message message--error" v-if="wrongPassword">
          {{ authentError }}
        </div>
        <div class="authent-buttons">
          <button ref="loginButton" class="authent-login-button k-primary" type="submit">
            <span>{{$t("authent.Sign in")}}</span>
          </button>
        </div>
      </form>
      <div class="authent-help" ref="authentHelpContent" style="display:none">
        <p class="label" for="password">
          {{$t("authent.Help Content")}}
        </p>
      </div>
      <form ref="authentForgetForm" class="authent-form authent-form--forget" style="display:none">
        <label class="label" for="forgetlogin">{{$t("authent.Enter identifier or email address :")}}</label>
        <input
          id="forgetlogin"
          class="authent-login form-control k-textbox"
          type="text"
          v-model="login"
          required
          autocapitalize="off"
          autocorrect="off"
          spellcheck="false"
          :placeholder="translations.forgetPlaceHolder"
        />
        <div class="message message--error" v-if="forgetStatusFailed">
          {{ forgetError }}
        </div>
        <div class="message message--success" v-if="!forgetStatusFailed">
          {{ forgetSuccess }}
        </div>
        <div class="authent-buttons">
          <button ref="authentForgetSubmit" class="authent-login-button k-primary" type="submit">
            <span>{{$t("authent.Send reset password ask")}}</span>
          </button>
        </div>
      </form>
    </div>
    <form ref="authentResetPasswordForm" class="authent-form authent-form--resetpassword" v-if="resetPassword">
      <ank-password
        :label="translations.resetPasswordLabel"
        v-if="resetStatusFailed"
        :validationMessage="translations.validationMessagePassword"
        :placeholder="translations.passwordPlaceHolder"
        v-model="resetPwd1"
      />
      <ank-password
        :label="translations.confirmPasswordLabel"
        v-if="resetStatusFailed"
        :validationMessage="translations.validationMessagePassword"
        :placeholder="translations.passwordPlaceHolder"
        v-model="resetPwd2"
      />
      <div class="message message--error" v-if="resetStatusFailed">
        {{ resetError }}
      </div>
      <div class="message message--success" v-if="!resetStatusFailed">
        {{ resetSuccess }}
      </div>
      <div class="authent-buttons">
        <button ref="authentResetSubmit" class="authent-login-button k-primary" type="submit" v-if="resetStatusFailed">
          <span>{{$t("authent.Send reset password ask")}}</span>
        </button>
        <button ref="authentGoHome" class="authent-login-home k-primary" v-if="!resetStatusFailed">
          <span>{{$t("authent.Go back to home page")}}</span>
        </button>
      </div>
    </form>
    <div class="authent-bottom">
      <select ref="authentLocale" class="authent-locale" name="language" v-model="$i18n.locale">
        <option v-for="language in availableLanguages" :value="language.key">{{ language.label }}</option>
      </select>
      <button ref="authentHelpButton" class="authent-help-button k-secondary" v-if="!resetPassword">
        <span>{{$t("authent.Help")}}</span>
      </button>
      <button ref="authentForgetButton" class="authent-forget-button k-secondary" v-if="!resetPassword">
        <span>{{$t("authent.Forget password ?")}}</span>
      </button>
    </div>
  </section>
</template>
<script src="./AnkAuthent.component.ts" lang="ts"></script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss" >
@import "./AnkAuthent.scss";
</style>
