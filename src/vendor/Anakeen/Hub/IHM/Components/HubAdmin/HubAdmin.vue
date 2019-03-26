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
            {{ hubTitle }}</span
          >
        </div>
        <div class="hub-admin-header__content">

        </div>
    </header>
    <section>
      <ank-hub-mockup :info="mockData" :selected-id="selectedComponent" @mock-select="changeSelectComponent"/>
      <ank-splitter
        ref="hubAdminSplitter"
        class="hub-admin-splitter"
        :panes="panes"
        localStorageKey="hub-admin-splitter"
      >
        <template slot="left">
          <div class="hub-admin-content">
            <div class="hub-admin-grid">
              <ank-se-grid
                ref="hubGrid"

                :urlContent="`/api/v2/hub/station/${hubId}/admin/`"
                :serverPaging="false"
                :sortable="''"
                :pageable="false"
                class="hub-admin"
                @action-click="actionClick"
                @after-content-response="displayMockUp"
                :contextTitles="false"
              >
                <ank-se-grid-column title="#" field="key" width="3rem"></ank-se-grid-column>
                <ank-se-grid-column title="Title" field="title"></ank-se-grid-column>
                <ank-se-grid-column title="Type" field="hub_type"></ank-se-grid-column>
                <ank-se-grid-column title="Position" field="hub_docker_position" :hidden="true"></ank-se-grid-column>
                <ank-se-grid-column title="Order" field="hub_order" :hidden="true"></ank-se-grid-column>

                <ank-se-grid-actions>
                  <ank-se-grid-action action="detail"  title="Details"></ank-se-grid-action>
                </ank-se-grid-actions>
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
