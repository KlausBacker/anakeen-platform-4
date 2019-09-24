<template>
  <div class="ank-business-app">
    <div class="ank-business-app-list">
      <ank-se-list
        ref="businessAppList"
        class="ank-business-app-list-widget"
        :selectable="false"
        @se-clicked="onSelectListItem"
        @after-se-list-page-change="afterPageChange"
        @se-list-filter-change="onListFilterChange"
      >
        <div
          v-if="isMultiCollection"
          slot="title"
          class="ank-business-app-header"
        >
          <select
            class="ank-business-app-collection-selector"
            ref="businessAppCollectionSelector"
          >
          </select>
        </div>
      </ank-se-list>
    </div>
    <div class="ank-business-app-tabs-wrapper">
      <ank-tabs
        class="ank-business-app-tabs"
        ref="businessAppTabs"
        v-model="selectedTab"
        @tabRemove="onTabRemove"
        @tabClick="onTabClick"
      >
        <ank-tab v-if="hasWelcomeTab" :closable="false" name="welcome">
          <template slot="label">
            <span
              class="ank-business-app-welcome-title"
              v-html="welcomeTab.title"
            ></span>
          </template>
          <ank-welcome
            @tabWelcomeCreate="onCreateElement"
            @tabWelcomeGridConsult="onGridConsult"
            :creation="welcomeTab.creation"
            :gridCollections="welcomeTab.gridCollections"
            ref="businessWelcomeTab"
          ></ank-welcome>
        </ank-tab>
        <ank-se-tab
          v-for="(tab, index) in tabs"
          :identifier="tab.name"
          :key="tab.tabId"
          :tabId="tab.tabId"
          :closable="!!tab.closable"
          :viewId="tab.viewId || '!defaultConsultation'"
          :revision="tab.revision"
          ref="seTab"
          @seTabAfterSave="onAfterSave"
          @seTabAfterDelete="onAfterDelete"
          @seTabActionClick="onActionClick"
          @seTabDisplayError="onDisplayError"
          @seTabDisplayMessage="onDisplayMessage"
        >
        </ank-se-tab>
      </ank-tabs>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./BusinessApp.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./BusinessApp.component.ts"></script>