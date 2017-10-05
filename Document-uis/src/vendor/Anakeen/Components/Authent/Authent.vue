<template>
    <form class="AuthentForm">
        <label class="label">Login:</label>
        <input class="AuthentLogin form-control k-textbox" type="text" v-model="login" required validationMessage="Enter your login"><br>
        <label class="label">Password:</label>
        <span class="input-group-btn">
            <input class="AuthentPwd form-control" type="password" v-model="pwd" required validationMessage="Enter your password">
            <button class="btn btn-reveal" type="button"><i class="fa fa-eye"></i></button>
        </span>
        <div id="msgerr" class="message msgerr" v-if="wrongPassword">Authentication error</div>
        <button class="AuthentButton k-primary">Se connecter</button>
    </form>
</template>
<script src="./Authent.component.js"></script>
