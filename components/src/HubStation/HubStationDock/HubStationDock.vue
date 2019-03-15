<template>
  <hub-dock :position="position" @dockResized="resizeWindow" ref="innerDock">
    <template slot="header">
      <hub-dock-entry
        v-for="(entry, index) in getDockHeaders(dockContent)"
        :key="`${position.toLowerCase()}-header-${index}`"
        :name="`${position.toLowerCase()}-header-${index}`"
        :selectable="isSelectableEntry(entry)"
        :selected="isSelectedEntry(entry)"
        :data-entry-route="getEntryRoute(entry)"
        @dockEntrySelected="onEntrySelected($event, entry)"
      >
        <component
          :is="entry.component.name"
          v-bind="entry.component.props"
          :entryOptions="entry.entryOptions"
          :displayType="dockState"
        ></component>
      </hub-dock-entry>
    </template>
    <hub-dock-entry
      v-for="(entry, index) in getDockCenter(dockContent)"
      :key="`${position.toLowerCase()}-center-${index}`"
      :name="`${position.toLowerCase()}-center-${index}`"
      :selectable="isSelectableEntry(entry)"
      :selected="isSelectedEntry(entry)"
      :data-entry-route="getEntryRoute(entry)"
      @dockEntrySelected="onEntrySelected($event, entry)"
    >
      <component
        :is="entry.component.name"
        v-bind="entry.component.props"
        :entryOptions="entry.entryOptions"
        :displayType="dockState"
      ></component>
    </hub-dock-entry>
    <template slot="footer">
      <hub-dock-entry
        v-for="(entry, index) in getDockFooter(dockContent)"
        :key="`${position.toLowerCase()}-footer-${index}`"
        :name="`${position.toLowerCase()}-footer-${index}`"
        :selectable="isSelectableEntry(entry)"
        :selected="isSelectedEntry(entry)"
        :data-entry-route="getEntryRoute(entry)"
        @dockEntrySelected="onEntrySelected($event, entry)"
      >
        <component
          :is="entry.component.name"
          v-bind="entry.component.props"
          :entryOptions="entry.entryOptions"
          :displayType="dockState"
        ></component>
      </hub-dock-entry>
    </template>
  </hub-dock>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss"></style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./HubStationDock.component.ts"></script>
