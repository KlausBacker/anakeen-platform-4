<template>
  <hub-dock
    ref="innerDock"
    :position="position"
    :expandable="isExpandable"
    @dockResized="resizeWindow"
  >
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
          :is="entry.component.name"
          ref="headerComponents"
          v-bind="entry.component.props"
          :entry-options="entry.entryOptions"
          :is-dock-collapsed="dockIsCollapsed"
          @hook:mounted="onComponentMounted(entry, 'headerComponents', index)"
        />
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
        :is="entry.component.name"
        ref="centerComponents"
        v-bind="entry.component.props"
        :entry-options="entry.entryOptions"
        :is-dock-collapsed="dockIsCollapsed"
        @hook:mounted="onComponentMounted(entry, 'centerComponents', index)"
      />
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
          :is="entry.component.name"
          ref="footerComponents"
          v-bind="entry.component.props"
          :entry-options="entry.entryOptions"
          :is-dock-collapsed="dockIsCollapsed"
          @hook:mounted="onComponentMounted(entry, 'footerComponents', index)"
        />
      </hub-dock-entry>
    </template>
  </hub-dock>
</template>
<script lang="ts" src="./HubStationDock.component.ts"></script>
