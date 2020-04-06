<template>
  <div ref="accountManager_wrapper" class="accountManager_wrapper">
    <ank-split-panes ref="accountTreeSplitter" watch-slots vertical class="account-manager-splitter splitter-tree">
      <div ref="treeViewPart" class="grid-group-section" splitpanes-size="20">
        <header class="admin-account-group-header">
          <kendo-button class="k-primary" @click="viewAllUsers">
            View all users
          </kendo-button>

          <kendo-dropdownlist
            v-model="selectedDepth"
            class="k-primary"
            :data-source="dataDepth"
            option-label="Depth"
            value-template="Depth : <strong>#: id #</strong>"
            data-text-field="id"
            data-value-field="id"
            @select="selectDepth"
            @open="addClassOnSelectorContainer"
          />
        </header>
        <kendo-grid
          ref="groupGrid"
          class="account-user-grid"
          :data-source="gridGroupContent"
          :row-template="groupRowTemplate"
          :selectable="true"
          :pageable="{
            alwaysVisible: true,
            pageSizes: [50, 100, 200],
            numeric: false,
            refresh: true
          }"
          :sortable="true"
          :filterable="{
            extra: false,
            operators: { string: { contains: 'contains' } }
          }"
          :change="onGroupSelect"
          :persist-selection="true"
          :auto-bind="false"
          @filter="onGroupFilter"
        >
          <kendo-grid-column field="id" :hidden="true" />
          <kendo-grid-column :sortable="false" field="lastname" title="Groups" type="string" />
        </kendo-grid>
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
            >
              Group info
            </kendo-button>
          </div>
        </header>
        <section>
          <ank-split-panes ref="accountSplitter" watch-slots vertical class="account-manager-splitter splitter-grid">
            <div class="accountManager_contentPart_gridPart">
              <kendo-grid
                ref="grid"
                class="account-user-grid"
                :data-source="gridUserContent"
                :pageable="{
                  alwaysVisible: true,
                  pageSizes: [50, 100, 200],
                  numeric: false,
                  refresh: true
                }"
                :sortable="true"
                :filterable="{
                  extra: false,
                  operators: { string: { contains: 'contains' } }
                }"
                :persist-selection="true"
                :auto-bind="false"
              >
                <kendo-grid-column field="id" :hidden="true" />
                <kendo-grid-column field="login" title="Login" type="string" />
                <kendo-grid-column field="firstname" title="First name" type="string" />
                <kendo-grid-column field="lastname" title="Last name" type="string" />
                <kendo-grid-column field="mail" title="E-mail" type="string" />
                <kendo-grid-column :command="{ text: 'Display', click: openUser }" :filterable="false" width="8rem" />
              </kendo-grid>
            </div>

            <div class="ank-se-parent">
              <div v-if="!selectedUser" class="user-empty">
                <div class="token-logo">
                  <span class="material-icons">account_circle</span>
                </div>
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
