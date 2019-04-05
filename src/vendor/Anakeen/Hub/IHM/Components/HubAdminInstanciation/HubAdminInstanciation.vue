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
              <ank-se-list smart-collection="HUBINSTANCIATION" label="Hub Configuration Center" @se-selected="openConfig">
                <div slot="header">
                </div>
                <div slot="search">
                  <kendo-button class="k-primary k-outline" @click="createHubStation"  >Create new hub interface
                  </kendo-button>

                </div>
              </ank-se-list>
            </div>
          </div>
        </template>
        <template slot="right">
          <iframe v-if="this.displayConfig && this.selectedHub"  :src="`/hub/admin/${this.selectedHub}`" class="hub-modal"></iframe>
          <ank-smart-element v-show="!this.displayConfig" ref="instanceConfig" class="hub-modal"></ank-smart-element>

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
