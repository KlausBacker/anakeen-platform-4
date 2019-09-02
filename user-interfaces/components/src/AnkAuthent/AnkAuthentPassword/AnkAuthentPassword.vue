<template>
  <div class="authent-password" ref="authentPassword">
    <label class="label" :for="pwdId">{{ label }}</label>
    <div class="input-group">
      <input
        class="authent-pwd form-control"
        :type="passInputType"
        @input.stop="onPasswordInput"
        @invalid="onPasswordInvalid"
        v-model="value"
        :placeholder="placeholder"
        required
        autocapitalize="off"
        autocorrect="off"
      />
      <div class="input-group-append">
        <button
          class="btn btn-reveal btn-secondary input-group-append"
          @click="revealPassword"
          type="button"
        >
          <i :class="`fa ${visibilityIcon}`"></i>
        </button>
      </div>
    </div>
  </div>
</template>
<script src="./AnkAuthentPassword.component.ts" lang="ts"></script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
@import "./AnkAuthentPassword.scss";
</style>
