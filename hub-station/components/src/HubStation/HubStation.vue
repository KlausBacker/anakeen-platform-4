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
      <hub-station-dock
        ref="dockTop"
        :position="DockPosition.TOP"
        :dockContent="configData.top"
        :rootUrl="rootUrl"
        @hubElementSelected="onHubElementSelected"
      ></hub-station-dock>
    </header>
    <section class="hub-station-center-area">
      <aside v-if="isLeftEnabled" class="hub-station-aside hub-station-left">
        <hub-station-dock
          ref="dockLeft"
          :position="DockPosition.LEFT"
          :dockContent="configData.left"
          :rootUrl="rootUrl"
          :activeRoute="activeRoute"
          @hubElementSelected="onHubElementSelected"
        ></hub-station-dock>
      </aside>
      <section class="hub-station-content">
        <div
          v-for="(entry, index) in panes"
          v-if="alreadyVisited[entry.entryOptions.route]"
          :class="{
            'hub-station-route-content': true,
            'route-active': entry.entryOptions.route === activeRoute
          }"
          :data-route="entry.entryOptions.route"
        >
          <vnodes :vnodes="entry.hubContentVNodes"></vnodes>
<!--          <component-->
<!--            class="hub-station-component-route-wrapper"-->
<!--            :is="entry.hubContentFunction"-->
<!--            v-bind="entry.component.props"-->
<!--            :entryOptions="entry.entryOptions"-->
<!--            ref="hubElementsContent"-->
<!--            @hook:mounted="onHubElementContentMounted(index)"-->
<!--          ></component>-->
        </div>
      </section>
      <aside v-if="isRightEnabled" class="hub-station-aside hub-station-right">
        <hub-station-dock
          ref="dockRight"
          :position="DockPosition.RIGHT"
          :dockContent="configData.right"
          :rootUrl="rootUrl"
          @hubElementSelected="onHubElementSelected"
        ></hub-station-dock>
      </aside>
    </section>
    <footer
      v-if="isFooterEnabled"
      class="hub-station-bar hub-station-bar--footer"
    >
      <hub-station-dock
        ref="dockBottom"
        :position="DockPosition.BOTTOM"
        :dockContent="configData.bottom"
        :rootUrl="rootUrl"
        @hubElementSelected="onHubElementSelected"
      ></hub-station-dock>
    </footer>
  </div>
</template>
<style scoped lang="scss">
@import "./HubStation.scss";
</style>
<style lang="scss">
@import "./HubStationStyle.scss";
</style>
<script lang="ts" src="./HubStation.component.ts"></script>
