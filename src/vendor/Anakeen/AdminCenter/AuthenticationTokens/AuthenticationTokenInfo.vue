<template>
  <div class="authentication-token-info">
    <h1>Authentication Token Info</h1>
    <h2>Tok tok : {{ info.token }}</h2>
    <h2>User : {{ info.user }}</h2>
    <h2>Expire : {{ info.expirationDate }}</h2>
  </div>
</template>

<style lang="scss">
@import "./AuthenticationTokenInfo.scss";
</style>

<script src="./AuthenticationTokenInfo.controller.ts" lang="ts"></script>
