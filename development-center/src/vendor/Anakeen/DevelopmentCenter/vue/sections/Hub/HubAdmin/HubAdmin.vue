<template>
  <div class="hub-admin-main-section">
    <header class="hub-admin-header">
      <div class="hub-admin-header__content">
        <span
          >Configuration for -
          <img
            :src="`/api/v2/smart-elements/${hubId}/images/icon/-1/sizes/16x16c.png`"
            alt="favIcon"
            width="16"
            height="16"
          />
          {{ hubElement.properties.title }}</span
        >
      </div>

      <div class="hub-admin-header__content">
        <kendo-datasource ref="dataHubElements" :transport-read-url="'/hub/components/'" />
        <kendo-dropdownlist
          class="k-primary"
          :data-source-ref="'dataHubElements'"
          :data-text-field="'text'"
          :data-value-field="'value'"
          option-label="Add hub element"
          @select="selectCreateConfig"
          @open="addClassOnSelectorContainer"
        />

        <kendo-button class="k-primary k-outline" @click="exportConfiguration"
          >Export hub instance configuration
        </kendo-button>
        <kendo-button class="k-primary k-outline" @click="openElement">View current hub instance </kendo-button>
        <kendo-button class="k-primary k-outline" @click="openInterface">Display hub instance </kendo-button>
      </div>
    </header>
    <section>
      <ank-splitter ref="hubAdminSplitter" class="hub-admin-splitter" :panes="panes">
        <template slot="left">
          <div class="hub-admin-content">
            <ank-hub-mockup :info="mockData" :selected-id="selectedComponent" @mock-select="changeSelectComponent" />
            <div class="hub-admin-grid">
              <span>List of the hub elements of {{ hubElement.properties.title }}</span>
              <ank-se-grid
                ref="hubGrid"
                :collection="hubId.toString()"
                controller="HUB_STATION_ADMIN_GRID_CONTROLLER"
                :pageable="false"
                class="hub-admin"
                @AfterContent="displayMockUp"
                :contextTitles="false"
              >
              </ank-se-grid>
            </div>
          </div>
        </template>
        <template slot="right">
          <smart-element ref="smartConfig" class="hub-modal"></smart-element>
        </template>
      </ank-splitter>
    </section>
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
@import "HubAdmin";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./HubAdmin.controller.js"></script>
