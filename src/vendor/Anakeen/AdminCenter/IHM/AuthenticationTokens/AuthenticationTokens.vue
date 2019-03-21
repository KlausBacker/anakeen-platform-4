<template>
  <div class="authentication-tokens">
    <ank-splitter
      ref="splitter"
      class="ank-token-splitter"
      :panes="panes"
      localStorageKey="token-manager-splitter"
    >
      <template slot="left">
        <div class="token-main">
          <header>
            <kendo-button class="button-filtering k-outline k-primary" @click="flipFiltering">
              <i class="fa " :class="{ 'fa-check': showExpire }"/>
              <span> Show expired </span>
            </kendo-button>

            <kendo-button class="button-createform k-primary" @click="displayCreateForm">
              <i class="fa fa-plus"/>
              <span>Create token</span></kendo-button>
          </header>

          <div class="token-grid-box">
            <div ref="tokenGrid" class="token-grid"></div>
          </div>

        </div>
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
          <span class="material-icons">fingerprint</span>
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
