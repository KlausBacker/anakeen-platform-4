<template>
    <form class="AuthentForm">
        <label class="label">Login:</label>
        <input class="AuthentLogin form-control" type="text" v-model="login"><br>
        <label class="label">Password:</label>
        <input class="AuthentPwd form-control" type="text" v-if="showPasswordIsChecked" v-model="pwd">
        <input class="AuthentPwd form-control" type="password" v-if="!showPasswordIsChecked" v-model="pwd">
        <label>
            <input class="methods" type="checkbox" v-model="showPasswordIsChecked"/>Show password
        </label>
        <button class="AuthentButton" type="primary">Se connecter</button>
    </form>
</template>
<script src="./Authent.component.js"></script>
