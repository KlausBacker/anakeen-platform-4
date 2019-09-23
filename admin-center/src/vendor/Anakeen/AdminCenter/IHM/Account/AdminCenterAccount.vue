<template>
  <div class="accountManager_wrapper" ref="accountManager_wrapper">
    <ank-split-panes watch-slots vertical ref="accountTreeSplitter" class="account-manager-splitter splitter-tree">
      <div ref="treeViewPart" class="tree-view-part" splitpanes-size="20">
        <div class="accountManager_treeViewPart_wrapper">
          <div class="accountManager_treeViewPart_wrapper_topPart">
            <div class="accountManager_treeViewPart_wrapper_topPart_btnPart">
              <kendo-toolbar ref="treeToolbar" class="tree-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="() => updateTreeData()"></kendo-toolbar-item>
                <kendo-toolbar-item type="button" icon="arrow-60-up" @click="collapseAll"></kendo-toolbar-item>
                <kendo-toolbar-item type="button" icon="arrow-60-down" @click="expandAll"></kendo-toolbar-item>
              </kendo-toolbar>
            </div>
            <div class="accountManager_treeViewPart_wrapper_searchPart">
              <form @submit="filterGroup">
                <input type="text" ref="filterTree" class="form-control" placeholder="Search..." />
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

      <div class="admin-account-main-section" splitpanes-size="80">
        <header class="admin-account-header">
          <div class="admin-account-header__content">
            <kendo-button
              class="k-primary change-group-btn"
              :disabled="!this.groupId || this.groupId === '@users'"
              @click="openChangeGroup"
            >
              Move group
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
          <ank-split-panes watch-slots vertical ref="accountSplitter" class="account-manager-splitter splitter-grid">
            <div class="accountManager_contentPart_gridPart">
              <kendo-grid
                ref="grid"
                class="account-user-grid"
                :data-source="gridContent"
                :pageable="{
                  alwaysVisible: true,
                  pageSizes: [20, 50, 100]
                }"
                :sortable="true"
                :filterable="{
                  extra: false,
                  operators: { string: { contains: 'contains' } }
                }"
                :persistSelection="true"
                :autoBind="false"
              >
                <kendo-grid-column field="id" :hidden="true"></kendo-grid-column>
                <kendo-grid-column field="login" title="Login" type="string"></kendo-grid-column>
                <kendo-grid-column field="firstname" title="First name" type="string"></kendo-grid-column>
                <kendo-grid-column field="lastname" title="Last name" type="string"></kendo-grid-column>
                <kendo-grid-column field="mail" title="E-mail" type="string"></kendo-grid-column>
                <kendo-grid-column
                  :command="{ text: 'Display', click: openUser }"
                  :filterable="false"
                  width="8rem"
                ></kendo-grid-column>
              </kendo-grid>
            </div>

            <div class="ank-se-parent">
              <div class="user-empty" v-if="!selectedUser">
                <div class="token-logo"><span class="material-icons">account_circle</span></div>
                <div>Select an user</div>
              </div>
              <ank-smart-element ref="openDoc" class="open-element-content" />
            </div>
          </ank-split-panes>
        </section>
      </div>
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./AdminCenterAccount.scss";
</style>
<script src="./AdminCenterAccount.controller.ts" lang="ts"></script>
