<template>
  <div class="te-manager">
    <div v-if="true" class="te-manager-box">
      <kendo-tabstrip @activate="onTabActivate">
        <ul>
          <li class="k-state-active">
            {{ $t("AdminCenterTransformationFileManager.Configuration") }}
          </li>
          <li data-id="supervision">
            {{ $t("AdminCenterTransformationFileManager.Supervision") }}
          </li>
          <li data-id="load">
            {{ $t("AdminCenterTransformationFileManager.Server load") }}
          </li>
          <li data-id="unitTransformation">
            {{ $t("AdminCenterTransformationFileManager.Unit transformation") }}
          </li>
        </ul>
        <div class="te-configuration">
          <te-config />
        </div>
        <div class="te-supervision">
          <te-supervision v-if="supervisionActived" ref="supervisionComponent" />
        </div>

        <div class="te-server-load">
          <te-server-load v-if="loadActived" ref="serverLoadComponent" />
        </div>

        <div class="te-unit-transformation">
          <te-unit-transformation v-if="unitTransformationActived" ref="unitTransformationComponent" />
        </div>
      </kendo-tabstrip>
    </div>
  </div>
</template>
<style lang="scss">
@import "./TeManager.scss";
</style>

<script src="./TeManager.controller.ts" lang="ts"></script>
