<template>
  <div class="accountManager_wrapper" ref="accountManager_wrapper">
    <ank-splitter
      ref="accountTreeSplitter"
      class="account-manager-splitter"
      :panes="panes"
    >
      <template slot="left">
        <div ref="treeViewPart" class="tree-view-part">
          <div class="accountManager_treeViewPart_wrapper">
            <div class="accountManager_treeViewPart_wrapper_topPart">
              <div class="accountManager_treeViewPart_wrapper_topPart_btnPart">
                <kendo-toolbar ref="treeToolbar" class="tree-toolbar">
                  <kendo-toolbar-item
                    type="button"
                    icon="refresh"
                    @click="() => updateTreeData(true)"
                  ></kendo-toolbar-item>
                  <kendo-toolbar-item
                    type="button"
                    icon="arrow-60-up"
                    @click="collapseAll"
                  ></kendo-toolbar-item>
                  <kendo-toolbar-item
                    type="button"
                    icon="arrow-60-down"
                    @click="expandAll"
                  ></kendo-toolbar-item>
                </kendo-toolbar>
              </div>
              <div class="accountManager_treeViewPart_wrapper_searchPart">
                <form @submit="filterGroup">
                  <input
                    type="text"
                    ref="filterTree"
                    class="form-control"
                    placeholder="Search..."
                  />
                </form>
              </div>
            </div>
            <div class="accountManager_treeViewPart_wrapper_content">
              <kendo-treeview
                ref="groupTreeView"
                :data-source="groupTree"
                :template="'#= item.title # (#= item.nbUser #)'"
                @change="onGroupSelect"
                @expand="registerTreeState"
                @collapse="registerTreeState"
              >
              </kendo-treeview>
            </div>
          </div>
        </div>
      </template>
      <template slot="right">
        <div class="admin-account-main-section">
          <header class="admin-account-header">
            <div class="admin-account-header__content">
              <kendo-button
                class="k-primary change-group-btn"
                :disabled="!this.groupId || this.groupId === '@users'"
                @click="openChangeGroup"
                >Move group
              </kendo-button>
              <kendo-datasource
                ref="dataUserElement"
                :transport-read-url="'/api/v2/admin/account/config/'"
                :schema-parse="parseCreateUser"
              />
              <kendo-dropdownlist
                      ref="userList"
                class="k-primary"
                :data-source-ref="'dataUserElement'"
                :data-text-field="'text'"
                :data-value-field="'value'"
                option-label="Create User"
                @select="selectCreateUserConfig"
                @open="addClassOnSelectorContainer"
              />
              <kendo-datasource
                ref="dataGroupElement"
                :transport-read-url="'/api/v2/admin/account/config/'"
                :schema-parse="parseCreateGroup"
              />
              <kendo-dropdownlist
                      ref="groupList"
                class="k-primary"
                :data-source-ref="'dataGroupElement'"
                :data-text-field="'text'"
                :data-value-field="'value'"
                option-label="Create sub group"
                @select="selectCreateGroupConfig"
                @open="addClassOnSelectorContainer"
              />
              <kendo-button
                      class="k-primary change-group-btn"
                      :disabled="!this.groupId || this.groupId === '@users'"
                      @click="openGroup"
              >Group info
              </kendo-button>
            </div>
          </header>
          <section>
            <ank-splitter
              ref="accountSplitter"
              class="account-manager-splitter"
              :panes="mainPanes"
            >
              <template slot="left">
                <div class="accountManager_contentPart_gridPart">
                  <kendo-grid
                    ref="grid"
                    class="account-user-grid"
                    :data-source="gridContent"
                    :pageable="{
                      alwaysVisible: true,
                      pageSizes: [10, 20, 100]
                    }"
                    :sortable="true"
                    :filterable="{
                      extra: false,
                      operators: { string: { contains: 'contains' } }
                    }"
                    :persistSelection="true"
                    :autoBind="false"
                  >
                    <kendo-grid-column
                      field="id"
                      :hidden="true"
                    ></kendo-grid-column>
                    <kendo-grid-column
                      field="login"
                      title="Login"
                      type="string"
                    ></kendo-grid-column>
                    <kendo-grid-column
                      field="firstname"
                      title="First name"
                      type="string"
                    ></kendo-grid-column>
                    <kendo-grid-column
                      field="lastname"
                      title="Last name"
                      type="string"
                    ></kendo-grid-column>
                    <kendo-grid-column
                      field="mail"
                      title="E-mail"
                      type="string"
                    ></kendo-grid-column>
                    <kendo-grid-column
                      :command="{ text: 'Display', click: openUser }"
                      :filterable="false"
                      width="8rem"
                    ></kendo-grid-column>
                  </kendo-grid>
                </div>
              </template>
              <template slot="right">
                <div class="ank-se-parent">
                  <ank-smart-element
                    ref="openDoc"
                    class="open-element-content"
                  ></ank-smart-element>
                </div>
              </template>
            </ank-splitter>
          </section>
        </div>
      </template>
    </ank-splitter>
  </div>
</template>
<style lang="scss">
@import "./AdminCenterAccount.scss";
</style>
<script src="./AdminCenterAccount.controller.ts" lang="ts">
</script>
