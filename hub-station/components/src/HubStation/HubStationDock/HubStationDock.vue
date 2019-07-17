<template>
  <hub-dock :position="position" @dockResized="resizeWindow" ref="innerDock" :expandable="isExpandable">
    <template slot="header">
      <hub-dock-entry
        v-for="(entry, index) in getDock(InnerDockPosition.HEADER, dockContent)"
        :key="`${position.toLowerCase()}-header-${index}`"
        :name="`${position.toLowerCase()}-header-${index}`"
        :selectable="isSelectableEntry(entry)"
        :selected="isSelectedEntry(entry)"
        :data-entry-route="getEntryRoute(entry)"
        @dockEntrySelected="onEntrySelected($event, entry)"
      >
        <component
          ref="headerComponents"
          @hook:mounted="onComponentMounted(entry, 'headerComponents', index)"
          :is="entry.component.name"
          v-bind="entry.component.props"
          :entryOptions="entry.entryOptions"
          :isDockCollapsed="dockIsCollapsed"
        ></component>
      </hub-dock-entry>
    </template>
    <hub-dock-entry
      v-for="(entry, index) in getDock(InnerDockPosition.CENTER, dockContent)"
      :key="`${position.toLowerCase()}-center-${index}`"
      :name="`${position.toLowerCase()}-center-${index}`"
      :selectable="isSelectableEntry(entry)"
      :selected="isSelectedEntry(entry)"
      :data-entry-route="getEntryRoute(entry)"
      @dockEntrySelected="onEntrySelected($event, entry)"
    >
      <component
        ref="centerComponents"
        @hook:mounted="onComponentMounted(entry, 'centerComponents', index)"
        :is="entry.component.name"
        v-bind="entry.component.props"
        :entryOptions="entry.entryOptions"
        :isDockCollapsed="dockIsCollapsed"
      ></component>
    </hub-dock-entry>
    <template slot="footer">
      <hub-dock-entry
        v-for="(entry, index) in getDock(InnerDockPosition.FOOTER, dockContent)"
        :key="`${position.toLowerCase()}-footer-${index}`"
        :name="`${position.toLowerCase()}-footer-${index}`"
        :selectable="isSelectableEntry(entry)"
        :selected="isSelectedEntry(entry)"
        :data-entry-route="getEntryRoute(entry)"
        @dockEntrySelected="onEntrySelected($event, entry)"
      >
        <component
          ref="footerComponents"
          @hook:mounted="onComponentMounted(entry, 'footerComponents', index)"
          :is="entry.component.name"
          v-bind="entry.component.props"
          :entryOptions="entry.entryOptions"
          :isDockCollapsed="dockIsCollapsed"
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
