<template>
  <div>
    <div class="hub-instanciation-parent">
      <div class="hub-instanciation-header">
        <div class="hub-instanciation-header__content">
          <span>Hub Admin</span>
        </div>
        <div class="hub-instanciation-header__content">
          <identity
            :large="true"
            :email-alterable="true"
            :password-alterable="true"
          ></identity>
          <logout></logout>
        </div>
      </div>
    </div>
    <div>
      <ank-splitter
        ref="hubInstanciationSplitter"
        class="hub-instanciation-splitter"
        :panes="panes"
        localStorageKey="hub-instanciation-splitter"
      >
        <template slot="left">
          <div class="hub-instanciation-content">
            <div class="hub-instanciation-grid">
              <grid
                collection="HUBINSTANCIATION"
                :reorderable="true"
                class="hub-instanciation"
                ref="hubInstanciationGrid"
                :contextTitles="false"
                @toolbar-action-click="toolbarActionClick"
                @action-click="actionClick"
                @before-grid-cell-render="cellRender"
              >
                <grid-column
                  title="Icon"
                  field="icon"
                  :width="'10rem'"
                  :encoded="'false'"
                ></grid-column>
                <grid-column title="Title" field="title"></grid-column>
                <grid-actions>
                  <grid-action
                    action="consult"
                    iconClass="k-icon k-i-folder-open"
                  ></grid-action>
                  <grid-action action="edit"></grid-action>
                  <grid-action action="configure"></grid-action>
                </grid-actions>
                <grid-toolbar>
                  <grid-toolbar-action
                    action="create"
                    title="Create"
                    iconClass="k-icon k-i-plus"
                  ></grid-toolbar-action>
                </grid-toolbar>
              </grid>
            </div>
          </div>
        </template>
        <template slot="right">
          <smart-element ref="instanceConfig" class="hub-modal"></smart-element>
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
