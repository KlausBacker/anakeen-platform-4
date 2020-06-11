<template>
  <div class="ssm-parent">
    <ss-list class="ssm-list" v-model="selectedSS"></ss-list>
    <div class="ssm-content">
      <div class="ssm-content__empty" v-if="isEmpty">
        <i class="material-icons hub-icon ssm-content__empty-icon">code</i>
        <span class="ssm-content__empty-text">{{ $t("AdminCenterSmartStructure.Select a structure") }}</span>
      </div>
      <div class="ssm-tabs-parent" v-else>
        <ank-tabs class="ssm-tabs" ref="ssmTabs" @tabClick="emitTabId">
          <ank-tab :closable="false" tab-id="informations">
            <template slot="label">
              <span class="ssm-informations-title">{{ $t("AdminCenterSmartStructure.Informations") }}</span>
            </template>
            <ssm-info
              class="ssm-info"
              :ssName="selectedSS"
              @parentStructureSelected="gotoParentStructure"
              @structure-infos="recordStructureInfos"
            ></ssm-info>
          </ank-tab>
          <ank-tab :closable="false" tab-id="defaultValues">
            <template slot="label">
              <span class="ssm-informations-title">{{ $t("AdminCenterSmartStructure.Default Values") }}</span>
            </template>
            <ssm-default-values ref="defaultComp" :ssName="selectedSS" class="ssm-default-values"></ssm-default-values>
          </ank-tab>
          <ank-tab :closable="false" tab-id="parameters">
            <template slot="label">
              <span class="ssm-parameters-title">{{ $t("AdminCenterSmartStructure.Parameters") }}</span>
            </template>
            <ssm-parameters ref="paramsComp" :ssName="selectedSS" class="ssm-parameters"></ssm-parameters>
          </ank-tab>
          <ank-tab :closable="false" tab-id="smartElements">
            <template slot="label">
              <span class="ssm-smart-elements-title">{{ $t("AdminCenterSmartStructure.SmartElements") }}</span>
            </template>
            <ssm-smart-elements
              ref="smartElementsComp"
              :ssName="selectedSS"
              :ssInfos="structureInfos"
              class="ssm-smart-elements"
            ></ssm-smart-elements>
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
