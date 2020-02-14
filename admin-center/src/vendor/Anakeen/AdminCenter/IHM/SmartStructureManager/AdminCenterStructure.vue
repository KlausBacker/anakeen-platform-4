<template>
  <div class="ssm-parent">
    <ss-list class="ssm-list" v-model="selectedSS"></ss-list>
    <div class="ssm-content">
      <div class="ssm-content__empty" v-if="isEmpty">
        <i class="material-icons hub-icon ssm-content__empty-icon">code</i>
        <span class="ssm-content__empty-text">Select a structure</span>
      </div>
      <div class="ssm-tabs-parent" v-else>
        <ank-tabs class="ssm-tabs" ref="ssmTabs" v-model="selectedTab">
          <ank-tab :closable="false" tab-id="informations">
            <template slot="label">
              <span class="ssm-informations-title">Informations</span>
            </template>
            <ssm-info class="ssm-info" :ssName="selectedSS" @parent-structure-selected="gotoParentStructure"></ssm-info>
          </ank-tab>
          <ank-tab :closable="false" tab-id="defaultValues">
            <template slot="label">
              <span class="ssm-informations-title">Default Values</span>
            </template>
            <ssm-default-values :ssName="selectedSS" class="ssm-default-values" ref="defaultComp"></ssm-default-values>
          </ank-tab>
          <ank-tab :closable="false" tab-id="parameters">
            <template slot="label">
              <span class="ssm-parameters-title">Parameters</span>
            </template>
            <ssm-parameters :ssName="selectedSS" class="ssm-parameters" ref="paramsComp"></ssm-parameters>
          </ank-tab>
        </ank-tabs>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
@import "./AdminCenterStructure.scss";
</style>
<script src="./AdminCenterStructure.controller.ts" lang="ts"></script>
