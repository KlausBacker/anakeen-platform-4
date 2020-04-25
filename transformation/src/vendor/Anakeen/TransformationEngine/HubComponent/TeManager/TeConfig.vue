<template>
  <div class="te-configuration">
    <label>
      <span>{{ $t("AdminCenterTransformationFileManager.Activate TE Engine") }} :</span>
      <span><kendo-switch v-model="info.TE_ACTIVATE" name="switch"/></span>
    </label>
    <label>
      <span>{{ $t("AdminCenterTransformationFileManager.Hostname of transformation engine server") }} :</span>
      <input v-model="info.TE_HOST" class="k-textbox" />
    </label>
    <label>
      <span>{{ $t("AdminCenterTransformationFileManager.Port number of transformation engine server") }} :</span>
      <input v-model="info.TE_PORT" class="k-textbox" type="number" />
    </label>
    <label>
      <span>{{ $t("AdminCenterTransformationFileManager.Callback url for response") }} :</span>
      <input v-model="info.TE_URLINDEX" class="k-textbox" placeholder="https://..." />
    </label>
    <label>
      <span>{{ $t("AdminCenterTransformationFileManager.Waiting delay") }} :</span>
      <input v-model="info.TE_TIMEOUT" class="k-textbox" type="number" />
    </label>

    <section class="te-check-section">
      <div class="te-check-button">
        <kendo-button :disabled="testRunning" class="k-primary" @click="checkConfig">{{
          $t("AdminCenterTransformationFileManager.Check connection")
        }}</kendo-button>
        <div v-show="teVersion" class="te-version">
          Version : <b>{{ teVersion }}</b>
        </div>
      </div>
      <div v-show="this.kProgress" class="te-check">
        <div class="te-checkbar">
          <p>{{ this.kProgress && this.kProgress.value() }} - {{ progressText }}</p>
          <div ref="progressBar" />
        </div>
        <div class="te-check-log">
          <ol>
            <li v-for="msg in progressMessages">{{ msg }}</li>
          </ol>
        </div>
      </div>
    </section>
    <div class="te-config-buttons">
      <kendo-button class="k-primary" @click="recordConfig">{{
        $t("AdminCenterTransformationFileManager.Save")
      }}</kendo-button>
    </div>
  </div>
</template>
<style lang="scss">
@import "./TeConfig.scss";
</style>
<script src="./TeConfig.controller.ts" lang="ts"></script>
