<template>
  <div ref="accountManager_wrapper" class="accountManager_wrapper">
    <ank-split-panes ref="accountTreeSplitter" watch-slots vertical class="account-manager-splitter splitter-tree">
      <div ref="treeViewPart" class="grid-group-section" splitpanes-size="20">
        <header class="admin-account-group-header">
          <kendo-button class="k-primary" @click="viewAllUsers">
            {{ $t("AdminCenterAccount.View all users") }}
          </kendo-button>

          <div class="group-expand">
            <span>{{ $t("AdminCenterAccount.Expand all") }} :</span>
            <kendo-switch @change="selectMaxDepth"></kendo-switch>
          </div>
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
            refresh: true,
            messages: {
              itemsPerPage: translations.ItemsPerPage,
              display: translations.Items,
              refresh: translations.Refresh,
              empty: translations.NoData
            }
          }"
          :sortable="true"
          :filterable="{
            extra: false,
            operators: { string: { contains: translations.contains } },
            messages: {
              info: translations.FilterBy + ': ',
              operator: translations.ChooseOperator,
              clear: translations.ClearFilter,
              filter: translations.ApplyFilter,
              value: translations.ChooseValue,
              additionalValue: translations.AditionalValue,
              title: translations.AditionalFilterBy
            }
          }"
          :change="onGroupSelect"
          :persist-selection="true"
          :auto-bind="false"
          @filter="onGroupFilter"
        >
          <kendo-grid-column field="id" :hidden="true" />
          <kendo-grid-column :sortable="false" field="lastname" :title="translations.Groups" type="string" />
        </kendo-grid>
      </div>

      <div class="admin-account-main-section" splitpanes-size="80">
        <header class="admin-account-header">
          <div class="admin-account-header__content">
            <span v-if="selectedGroup" class="group-header">{{ selectedGroup.lastname }} </span>
            <span v-else class="group-header">{{ $t("AdminCenterAccount.All users") }}</span>
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
              :option-label="translations.CreateUser"
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
              :option-label="translations.CreateSubGroup"
              @select="selectCreateGroupConfig"
              @open="addClassOnSelectorContainer"
            />
            <kendo-button
              class="k-primary change-group-btn"
              :disabled="!groupId || groupId === '@users'"
              @click="openGroup"
            >
              {{ $t("AdminCenterAccount.Group info") }}
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
                  refresh: true,
                  messages: {
                    itemsPerPage: translations.ItemsPerPage,
                    display: translations.Items,
                    refresh: translations.Refresh,
                    empty: translations.NoData
                  }
                }"
                :sortable="true"
                :filterable="{
                  extra: false,
                  operators: { string: { contains: translations.contains } },
                  messages: {
                    info: translations.FilterBy + ': ',
                    operator: translations.ChooseOperator,
                    clear: translations.ClearFilter,
                    filter: translations.ApplyFilter,
                    additionalValue: translations.ChooseValue,
                    additionalValue: translations.AditionalValue,
                    title: translations.AditionalFilterBy
                  }
                }"
                :persist-selection="true"
                :auto-bind="false"
              >
                <kendo-grid-column field="id" :hidden="true" />
                <kendo-grid-column field="login" :title="translations.Login" type="string" />
                <kendo-grid-column field="firstname" :title="translations.FirstName" type="string" />
                <kendo-grid-column field="lastname" :title="translations.LastName" type="string" />
                <kendo-grid-column field="mail" :title="translations.Email" type="string" />
                <kendo-grid-column
                  :command="{ text: translations.Display, click: openUser }"
                  :filterable="false"
                  width="8rem"
                />
              </kendo-grid>
            </div>

            <div class="ank-se-parent">
              <div v-if="!selectedUser" class="user-empty">
                <div class="token-logo">
                  <span class="material-icons">account_circle</span>
                </div>
                <div>{{ $t("AdminCenterAccount.Select an user") }}</div>
              </div>
              <ank-smart-element ref="openDoc" class="account-element-content" />
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
