<template>
  <div class="ank-business-app">
    <div class="ank-business-app-list" v-if="collections.length > 0">
      <ank-se-list
        ref="businessAppList"
        class="ank-business-app-list-widget"
        :selectable="false"
        :pageable="pageableConfig"
        :s-breakpoint="0"
        @itemClicked="onSelectListItem"
        @filterChange="onListFilterChange"
        @pageChange="onPageChange"
        :page="currentListPage"
        :filterValue="currentListFilter"
      >
        <template v-if="isMultiCollection" v-slot:label>
          <select class="ank-business-app-collection-selector" ref="businessAppCollectionSelector"> </select>
        </template>
      </ank-se-list>
    </div>
    <div class="ank-business-app-tabs-wrapper">
      <ank-tabs class="ank-business-app-tabs" ref="businessAppTabs" v-model="selectedTab" @tabRemove="onTabRemove">
        <ank-tab v-if="hasWelcomeTab" :closable="false" tabId="welcome">
          <template slot="label">
            <span class="ank-business-app-welcome-title" v-html="welcomeTab.title"></span>
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
          @smartElementTabBeforeRender="(event, se) => onTabBeforeRender(event, se, tab)"
          @smartElementTabAfterSave="onAfterSave"
          @smartElementTabAfterDelete="onAfterDelete"
          @smartElementTabActionClick="onActionClick"
          @smartElementTabDisplayError="onDisplayError"
          @smartElementTabDisplayMessage="onDisplayMessage"
          @smartElementTabClose="onTabClose"
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
