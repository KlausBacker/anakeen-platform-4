<template>
  <div class="authentication-tokens">
    <h1>Authentication Tokens</h1>
    <kendo-splitter class="ank-token-splitter" orientation="horizontal">
      <div ref="tokenGrid" class="token-grid" />
      <ank-token-info :info="tokenInfo">Detail</ank-token-info>
    </kendo-splitter>
  </div>
</template>

<style lang="scss">
@import "./AuthenticationTokens.scss";
</style>

<script src="./AuthenticationTokens.controller.ts" lang="ts"></script>
