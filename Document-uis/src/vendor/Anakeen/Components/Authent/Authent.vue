<template>
    <form class="AuthentForm">
        <label class="label">Login:</label>
        <input class="AuthentLogin form-control k-textbox" type="text" v-model="login" required validationMessage="Please enter your login"><br>
        <label class="label">Password:</label>
        <span class="input-group-btn">
            <input class="AuthentPwd form-control" type="password" v-model="pwd" required validationMessage="Please enter your password">
            <button class="btn btn-reveal" type="button" v-if="!hidePassword"><i class="fa fa-eye"></i></button>
            <button class="btn btn-reveal" type="button" v-if="hidePassword"><i class="fa fa-eye-slash"></i></button>
        </span>
        <div id="msgerr" class="message msgerr" v-if="wrongPassword">Authentication error</div>
        <button class="AuthentButton k-primary" type="submit">Se connecter</button>
    </form>
</template>
<script src="./Authent.component.js"></script>
