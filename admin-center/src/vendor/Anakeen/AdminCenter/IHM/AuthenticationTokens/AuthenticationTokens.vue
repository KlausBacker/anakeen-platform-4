<template>
  <div class="authentication-tokens">
    <ank-split-panes watch-slots vertical  ref="splitter" class="ank-token-splitter" localStorageKey="token-manager-splitter">
      <div class="token-main">
        <header>
          <kendo-button class="button-filtering k-outline k-primary" @click="flipFiltering">
            <i class="fa " :class="{ 'fa-check': showExpire }" />
            <span> Show expired </span>
          </kendo-button>

          <kendo-button class="button-createform k-primary" @click="displayCreateForm">
            <i class="fa fa-plus" />
            <span>Create token</span></kendo-button
          >
        </header>

        <div class="token-grid-box">
          <div ref="tokenGrid" class="token-grid"></div>
        </div>
      </div>
      <div v-if="!viewToken" class="token-logo">
        <span class="material-icons">fingerprint</span>
        <p>Select token to display details</p>
      </div>

      <ank-token-info v-if="viewToken" :info="tokenInfo" @token-deleted="refreshList"
        >Detail
      </ank-token-info >
    </ank-split-panes>
  </div>
</template>

<style lang="scss">
@import "./AuthenticationTokens.scss";
</style>

<script src="./AuthenticationTokens.controller.ts" lang="ts"></script>
