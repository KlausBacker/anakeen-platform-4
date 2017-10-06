<template>
    <form class="authent-form">
        <label class="label" for="login">Identifiant :</label>
        <input id="login" class="AuthentLogin form-control k-textbox" type="text" v-model="login" required validationMessage="Please enter your login">
        <label class="label" for="password">Mot de passe :</label>
        <span class="input-group-btn">
            <input id="password" class="authent-pwd form-control" type="password" v-model="pwd" required validationMessage="Please enter your password">
            <button class="btn btn-reveal" type="button" v-if="!hidePassword"><i class="fa fa-eye"></i></button>
            <button class="btn btn-reveal" type="button" v-if="hidePassword"><i class="fa fa-eye-slash"></i></button>
        </span>
        <div id="msgerr" class="message msgerr" v-if="wrongPassword">Authentication error</div>
        <div class="authent-buttons">
          <button class="authent-login-button k-primary" type="submit">Se connecter</button>
        </div>
    </form>
</template>
<script src="./Authent.component.js"></script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
    @import "./Authent.scss";
</style>
