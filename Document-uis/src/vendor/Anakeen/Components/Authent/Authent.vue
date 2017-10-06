<template>
    <form class="authent-form">
        <label class="label" for="login">Identifiant :</label>
        <input id="login" class="authent-login form-control k-textbox" type="text"
               v-model="login"
               required  autocapitalize="off" autocorrect="off"
               placeholder="Entrer votre identifiant"
               data-validationmessage="Vous devez saisir votre identifiant">
        <label class="label" for="password">Mot de passe :</label>
        <span class="input-group-btn">
            <input id="password"
                   class="authent-pwd form-control"
                   type="password"
                   v-model="pwd"
                   required autocapitalize="off" autocorrect="off"
                   data-validationmessage="Vous devez saisir votre mot de passe">
            <button class="btn btn-reveal btn-secondary" type="button" v-if="!hidePassword"><i class="fa fa-eye"></i></button>
            <button class="btn btn-reveal btn-secondary" type="button" v-if="hidePassword"><i class="fa fa-eye-slash"></i></button>
        </span>
        <div id="msgerr" class="message msgerr" v-if="wrongPassword">Erreur d'authentification</div>
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
