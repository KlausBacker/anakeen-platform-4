<template>
  <div class="ank-business-app">
    <ank-splitter
      ref="businessAppSplitter"
      class="ank-business-app-splitter"
      :panes="panes"
      localStorageKey="ank-business-app-splitter"
    >
      <template slot="left">
        <div class="ank-business-app-left-content">
          <div class="ank-business-app-list">
            <ank-se-list
              ref="businessAppList"
              class="ank-business-app-list-widget"
              @se-selected="onSelectListItem"
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
        </div>
      </template>
      <template slot="right">
        <ank-se-tabs
          class="ank-business-app-tabs"
          ref="businessAppTabs"
          v-model="selectedTab"
          @tabRemove="onTabRemove"
        >
          <ank-tab v-if="hasWelcomeTab" label="Welcome" :closable="false" name="ankWelcomePage">
            <ank-welcome @tabWelcomeCreate="onCreateElement" :creation="welcomeTab.creation" :gridCollections="welcomeTab.gridCollections"></ank-welcome>
          </ank-tab>
          <ank-se-tab
            v-for="(tab, index) in tabs"
            :identifier="tab.name"
            :key="tab.name"
            :closable="!!tab.closable"
            :viewId="tab.viewId || '!defaultConsultation'"
            ref="seTab"
          >
            <a slot="label" :href="`/api/v2/smart-elements/${tab.name}/views/${tab.viewId || '!defaultConsultation'}.html`" :title="tab.title" @click.prevent>
              <img :src="tab.icon" :alt="`${tab.name} icon`"/> <span>{{tab.title}}</span>
            </a>
          </ank-se-tab>
        </ank-se-tabs>
      </template>
    </ank-splitter>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./BusinessApp.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./BusinessApp.component.ts"></script>
