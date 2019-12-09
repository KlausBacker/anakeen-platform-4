<template>
  <hub-dock
    ref="innerDock"
    :position="position"
    :expandable="isExpandable"
    @dockResized="resizeWindow"
  >
    <template slot="header">
      <hub-dock-entry
        v-for="(entry) in getDock(InnerDockPosition.HEADER, dockContent)"
        :key="`${position.toLowerCase()}-header-${entry.uuid}`"
        :name="`${position.toLowerCase()}-header-${entry.uuid}`"
        :selectable="isSelectableEntry(entry)"
        :selected="isSelectedEntry(entry)"
        :data-entry-route="getEntryRoute(entry)"
        @dockEntrySelected="onEntrySelected($event, entry)"
      >
        <component
          :is="entry.component.name"
          :ref="`headerComponents-${entry.uuid}`"
          v-bind="entry.component.props"
          :entry-options="entry.entryOptions"
          :is-dock-collapsed="dockIsCollapsed"
          @hook:mounted="onComponentMounted(entry, `headerComponents-${entry.uuid}`)"
        />
      </hub-dock-entry>
    </template>
    <hub-dock-entry
      v-for="(entry) in getDock(InnerDockPosition.CENTER, dockContent)"
      :key="`${position.toLowerCase()}-center-${entry.uuid}`"
      :name="`${position.toLowerCase()}-center-${entry.uuid}`"
      :selectable="isSelectableEntry(entry)"
      :selected="isSelectedEntry(entry)"
      :data-entry-route="getEntryRoute(entry)"
      @dockEntrySelected="onEntrySelected($event, entry)"
    >
      <component
        :is="entry.component.name"
        :ref="`centerComponents-${entry.uuid}`"
        v-bind="entry.component.props"
        :entry-options="entry.entryOptions"
        :is-dock-collapsed="dockIsCollapsed"
        @hook:mounted="onComponentMounted(entry, `centerComponents-${entry.uuid}`)"
      />
    </hub-dock-entry>
    <template slot="footer">
      <hub-dock-entry
        v-for="(entry) in getDock(InnerDockPosition.FOOTER, dockContent)"
        :key="`${position.toLowerCase()}-footer-${entry.uuid}`"
        :name="`${position.toLowerCase()}-footer-${entry.uuid}`"
        :selectable="isSelectableEntry(entry)"
        :selected="isSelectedEntry(entry)"
        :data-entry-route="getEntryRoute(entry)"
        @dockEntrySelected="onEntrySelected($event, entry)"
      >
        <component
          :is="entry.component.name"
          :ref="`footerComponents-${entry.uuid}`"
          v-bind="entry.component.props"
          :entry-options="entry.entryOptions"
          :is-dock-collapsed="dockIsCollapsed"
          @hook:mounted="onComponentMounted(entry, `footerComponents-${entry.uuid}`)"
        />
      </hub-dock-entry>
    </template>
  </hub-dock>
</template>
<script lang="ts" src="./HubStationDock.component.ts"></script>
