<template>
  <div class="authentication-tokens">
    <header>
      <div class="token-title">Authentication Tokens </div>

      <kendo-button class="button-filtering k-outline k-primary" @click="flipFiltering">
        <i class="fa " :class="{ 'fa-check': showExpire }"/>
        <span> Show expired </span>
      </kendo-button>

      <kendo-button class="button-createform k-primary" @click="displayCreateForm">Create token</kendo-button>
    </header>
    <kendo-splitter ref="splitter" class="ank-token-splitter" orientation="horizontal" :panes="panes">
      <div ref="tokenGrid" class="token-grid" />
      <div>
      <ank-token-info  v-if="viewToken" :info="tokenInfo" @token-deleted="refreshList">Detail</ank-token-info>
        <div v-else class="token-logo">
        <span class="fa fa-user-secret"/>
        </div>
      </div>
    </kendo-splitter>
  </div>
</template>

<style lang="scss">
@import "./AuthenticationTokens.scss";
</style>

<script src="./AuthenticationTokens.controller.ts" lang="ts"></script>
