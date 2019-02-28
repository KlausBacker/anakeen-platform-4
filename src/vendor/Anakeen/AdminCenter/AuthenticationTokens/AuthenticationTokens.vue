<template>
  <div class="authentication-tokens">
    <ank-splitter
      ref="splitter"
      class="ank-token-splitter"
      :panes="panes"
      localStorageKey="vault-manager-splitter"
    >
      <template slot="left">
        <div ref="tokenGrid" class="token-grid"></div>
      </template>
      <template slot="right">
        <ank-token-info
          v-if="viewToken"
          :info="tokenInfo"
          @token-deleted="refreshList"
          >Detail</ank-token-info
        >
      </template>
      <template slot="emptyIcon">
        <div class="token-logo">
          <span class="fa fa-user-secret"></span>
        </div>
      </template>
      <template slot="emptyText"></template>
    </ank-splitter>
  </div>
</template>

<style lang="scss">
@import "./AuthenticationTokens.scss";
</style>

<script src="./AuthenticationTokens.controller.ts" lang="ts"></script>
