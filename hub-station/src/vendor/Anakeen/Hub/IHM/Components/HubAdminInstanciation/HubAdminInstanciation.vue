<template>
  <div class="ank-hub-main">
    <div class="hub-instanciation-parent">
      <div class="hub-instanciation-header">

      </div>
    </div>
    <div class="hub-instanciation-body">
      <ank-splitter
        ref="hubInstanciationSplitter"
        class="hub-instanciation-splitter"
        :panes="panes"
      >
        <template slot="left">
          <div class="hub-instanciation-content">
            <div class="hub-instanciation-grid">
              <ank-se-list ref="hubInstanciationList" smart-collection="HUBINSTANCIATION" label="Hub Instance" @se-selected="openConfig">
                <div slot="header">
                </div>
                <div slot="search">
                  <kendo-button class="k-primary k-outline" @click="createHubStation">Create new hub instance
                  </kendo-button>
                </div>
              </ank-se-list>
            </div>
          </div>
        </template>
        <template slot="right">
          <ank-hub-admin ref="ankHubAdmin" :hubId="selectedHub" class="hub-modal" v-if="displayConfig && selectedHub"></ank-hub-admin>
          <ank-smart-element v-show="!displayConfig" ref="instanceConfig" class="hub-modal" :initid="selectedHub"></ank-smart-element>
        </template>
      </ank-splitter>
    </div>
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
@import "./HubAdminInstanciation.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./HubAdminInstanciation.controller.js"></script>
