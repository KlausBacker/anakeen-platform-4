<template>
  <div
    :class="
      `hub-station-component ${
        config.instanceName ? `hub-instance-${config.instanceName}` : ''
      }`
    "
  >
    <header
      v-if="isHeaderEnabled"
      class="hub-station-bar hub-station-bar--header"
    >
      <hub-dock
        ref="dockTop"
        :expanded="true"
        :expandable="false"
        size="4.5rem"
        :position="DockPosition.TOP"
      >
        <template slot="header">
          <hub-dock-entry
            v-for="(entry, index) in getDockHeaders(configData.top)"
            :key="`top-header-${index}`"
            :name="`top-header-${index}`"
            :selectable="isSelectableEntry(entry)"
          >
            <template slot="collapsedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.COLLAPSED"
              ></component>
            </template>
            <template slot="expandedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.EXPANDED"
              ></component>
            </template>
          </hub-dock-entry>
        </template>
        <hub-dock-entry
          v-for="(entry, index) in getDockContent(configData.top)"
          :key="`top-content-${index}`"
          :name="`top-content-${index}`"
          :selectable="isSelectableEntry(entry)"
          :route="getEntryRoutePath(entry.entryOptions)"
        >
          <template slot="collapsedContent">
            <component
              :is="entry.component.name"
              v-bind="entry.component.props"
              :entryOptions="entry.entryOptions"
              :displayType="HubElementDisplayTypes.COLLAPSED"
            ></component>
          </template>
          <template slot="expandedContent">
            <component
              :is="entry.component.name"
              v-bind="entry.component.props"
              :entryOptions="entry.entryOptions"
              :displayType="HubElementDisplayTypes.EXPANDED"
            ></component>
          </template>
        </hub-dock-entry>
        <template slot="footer">
          <hub-dock-entry
            v-for="(entry, index) in getDockFooter(configData.top)"
            :key="`top-footer-${index}`"
            :name="`top-footer-${index}`"
            :selectable="isSelectableEntry(entry)"
          >
            <template slot="collapsedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.COLLAPSED"
              ></component>
            </template>
            <template slot="expandedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.EXPANDED"
              ></component>
            </template>
          </hub-dock-entry>
        </template>
      </hub-dock>
    </header>
    <section class="hub-station-center-area">
      <aside v-if="isLeftEnabled" class="hub-station-aside hub-station-left">
        <hub-dock ref="dockLeft" :position="DockPosition.LEFT">
          <template slot="header">
            <hub-dock-entry
              v-for="(entry, index) in getDockHeaders(configData.left)"
              :key="`left-header-${index}`"
              :name="`left-header-${index}`"
              :selectable="isSelectableEntry(entry)"
            >
              <template slot="collapsedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.COLLAPSED"
                ></component>
              </template>
              <template slot="expandedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.EXPANDED"
                ></component>
              </template>
            </hub-dock-entry>
          </template>
          <hub-dock-entry
            v-for="(entry, index) in getDockContent(configData.left)"
            :key="`left-content-${index}`"
            :name="`left-content-${index}`"
            :selectable="isSelectableEntry(entry)"
            :route="getEntryRoutePath(entry.entryOptions)"
          >
            <template slot="collapsedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.COLLAPSED"
              ></component>
            </template>
            <template slot="expandedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.EXPANDED"
              ></component>
            </template>
          </hub-dock-entry>
          <template slot="footer">
            <hub-dock-entry
              v-for="(entry, index) in getDockFooter(configData.left)"
              :key="`left-footer-${index}`"
              :selectable="isSelectableEntry(entry)"
              :name="`left-footer-${index}`"
            >
              <template slot="collapsedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.COLLAPSED"
                ></component>
              </template>
              <template slot="expandedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.EXPANDED"
                ></component>
              </template>
            </hub-dock-entry>
          </template>
        </hub-dock>
      </aside>
      <section class="hub-station-content">
        <router-view class="hub-station-content-view"></router-view>
      </section>
      <aside v-if="isRightEnabled" class="hub-station-right">
        <hub-dock
          ref="dockRight"
          class="hub-dock hub-dock--right"
          :position="DockPosition.RIGHT"
        >
          <template slot="header">
            <hub-dock-entry
              v-for="(entry, index) in getDockHeaders(configData.right)"
              :key="`right-header-${index}`"
              :selectable="isSelectableEntry(entry)"
              :name="`right-header-${index}`"
            >
              <template slot="collapsedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.COLLAPSED"
                ></component>
              </template>
              <template slot="expandedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.EXPANDED"
                ></component>
              </template>
            </hub-dock-entry>
          </template>
          <hub-dock-entry
            v-for="(entry, index) in getDockContent(configData.right)"
            :key="`right-content-${index}`"
            :name="`right-content-${index}`"
            :selectable="isSelectableEntry(entry)"
            :route="getEntryRoutePath(entry.entryOptions)"
          >
            <template slot="collapsedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.COLLAPSED"
              ></component>
            </template>
            <template slot="expandedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.EXPANDED"
              ></component>
            </template>
          </hub-dock-entry>
          <template slot="footer">
            <hub-dock-entry
              v-for="(entry, index) in getDockFooter(configData.right)"
              :key="`right-footer-${index}`"
              :name="`right-footer-${index}`"
              :selectable="isSelectableEntry(entry)"
            >
              <template slot="collapsedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.COLLAPSED"
                ></component>
              </template>
              <template slot="expandedContent">
                <component
                  :is="entry.component.name"
                  v-bind="entry.component.props"
                  :entryOptions="entry.entryOptions"
                  :displayType="HubElementDisplayTypes.EXPANDED"
                ></component>
              </template>
            </hub-dock-entry>
          </template>
        </hub-dock>
      </aside>
    </section>
    <footer
      v-if="isFooterEnabled"
      class="hub-station-bar hub-station-bar--footer"
    >
      <hub-dock
        ref="dockBottom"
        @tabSelected="_onDockTabSelected(DockPosition.BOTTOM, $event)"
        :expanded="true"
        size="4.5rem"
        :expandable="false"
        :position="DockPosition.BOTTOM"
      >
        <template slot="header">
          <hub-dock-entry
            v-for="(entry, index) in getDockHeaders(configData.bottom)"
            :key="`bottom-header-${index}`"
            :name="`bottom-header-${index}`"
            :selectable="isSelectableEntry(entry)"
          >
            <template slot="collapsedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.COLLAPSED"
              ></component>
            </template>
            <template slot="expandedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.EXPANDED"
              ></component>
            </template>
          </hub-dock-entry>
        </template>
        <hub-dock-entry
          v-for="(entry, index) in getDockContent(configData.bottom)"
          :key="`bottom-content-${index}`"
          :name="`bottom-content-${index}`"
          :selectable="isSelectableEntry(entry)"
          :route="getEntryRoutePath(entry.entryOptions)"
        >
          <template slot="collapsedContent">
            <component
              :is="entry.component.name"
              v-bind="entry.component.props"
              :entryOptions="entry.entryOptions"
              :displayType="HubElementDisplayTypes.COLLAPSED"
            ></component>
          </template>
          <template slot="expandedContent">
            <component
              :is="entry.component.name"
              v-bind="entry.component.props"
              :entryOptions="entry.entryOptions"
              :displayType="HubElementDisplayTypes.EXPANDED"
            ></component>
          </template>
        </hub-dock-entry>
        <template slot="footer">
          <hub-dock-entry
            v-for="(entry, index) in getDockFooter(configData.bottom)"
            :key="`bottom-footer-${index}`"
            :name="`bottom-footer-${index}`"
            :selectable="isSelectableEntry(entry)"
          >
            <template slot="collapsedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.COLLAPSED"
              ></component>
            </template>
            <template slot="expandedContent">
              <component
                :is="entry.component.name"
                v-bind="entry.component.props"
                :entryOptions="entry.entryOptions"
                :displayType="HubElementDisplayTypes.EXPANDED"
              ></component>
            </template>
          </hub-dock-entry>
        </template>
      </hub-dock>
    </footer>
    <ank-notifier
      v-if="withNotifier"
      ref="ankNotifier"
      position="top-right"
      defaultType="notice"
    ></ank-notifier>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./HubStation.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./HubStation.component.ts"></script>
