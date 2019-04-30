<!--suppress HtmlFormInputWithoutLabel -->
<template>
  <div
    class="authentication-token-info"
    v-bind:class="{ tokenExpired: isExpired }"
  >
    <p v-if="isInfo">
      Created by : <b>{{ info.author }}</b> at
      <b>{{ creationDateFormatted }}</b>
    </p>
    <p v-if="isExpired" class="token-expired">
      Token is expired
    </p>
    <div ref="form" class="authentication-token-form">
      <label v-show="isInfo">
        <span class="label">Token :</span>
        <input
          readonly="readonly"
          type="text"
          class="k-textbox"
          :value="tokenValues.token"
        />
      </label>
      <label>
        <span class="label">Description :</span>
        <input
          type="text"
          :readonly="isInfo"
          class="k-textbox"
          v-model="tokenValues.description"
        />
      </label>
      <label>
        <span class="label">User login :</span>
        <input
          type="text"
          :readonly="isInfo"
          class="k-textbox"
          v-model="tokenValues.user"
        />
      </label>
      <div class="token-expire">
        <span class="label">Expire :</span>
        <div class="token-dates">
          <kendo-button
            :data-active="neverExpire"
            v-show="neverExpire || !isInfo"
            :disabled="isInfo"
            class="button-infinity"
            @click="onInfinity"
            >&infin;</kendo-button
          >
          <input
            v-show="!neverExpire || !isInfo"
            ref="expireDate"
            :disabled="!isInfo && neverExpire"
            :readonly="isInfo"
            type="text"
            v-model="info.expirationDate"
          />
        </div>
      </div>

      <label>
        <span class="label">Expendable usage :</span>
      </label>

      <div class="token-expendable">
        <input
          :disabled="isInfo"
          id="token-unique"
          type="radio"
          class="k-radio"
          name="expandable"
          value="unique"
          v-model="expendable"
        />
        <label class="k-radio-label" for="token-unique"> Unique </label>

        <input
          :disabled="isInfo"
          id="token-multiple"
          type="radio"
          class="k-radio"
          name="expandable"
          value="multiple"
          v-model="expendable"
        />
        <label class="k-radio-label" for="token-multiple"> Multiple </label>
      </div>

      <div
        v-for="(route, index) in tokenValues.routes"
        class="token-route"
        :data-index="index"
      >
        <select :data-index="index" v-model="route.method" :disabled="isInfo">
          <option>GET</option>
          <option>POST</option>
          <option>PUT</option>
          <option>DELETE</option>
          <option>PATCH</option>
          <option>*</option>
        </select>
        <input
          type="text"
          class="k-textbox"
          v-model="route.pattern"
          :readonly="isInfo"
        />
      </div>

      <kendo-button
        v-show="!isInfo"
        class="button-add k-outline k-primary"
        @click="onAddRouteRow"
        >+</kendo-button
      >

      <div class="token-buttons">
        <kendo-button
          v-show="isInfo"
          class="button-delete k-danger"
          @click="onConfirmDelete"
          >Delete token</kendo-button
        >
        <kendo-button
          :disabled="!fullInfo"
          v-show="isNew"
          class="button-record k-primary"
          @click="onCreate"
          >Record token</kendo-button
        >
      </div>
    </div>
    <div ref="confirmDelete" class="token-confirm" style="display: none">
      <p>
        Sure delete token <b>{{ info.description }}</b> ?
      </p>
      <pre>{{ info.token }}</pre>
      <kendo-button
        v-show="isInfo"
        class="button-delete k-danger"
        @click="onDelete"
        >Delete token</kendo-button
      >

      <kendo-button v-show="isInfo" @click="onCloseConfirm"
        >Cancel</kendo-button
      >
    </div>

    <div ref="infoDelete" class="token-confirm" style="display: none">
      <p>
        Token <b>{{ info.description }}</b> is deleted
      </p>
      <pre>{{ info.token }}</pre>
    </div>
  </div>
</template>

<style lang="scss">
@import "./AuthenticationTokenInfo.scss";
</style>

<script src="./AuthenticationTokenInfo.controller.ts" lang="ts">
</script>
