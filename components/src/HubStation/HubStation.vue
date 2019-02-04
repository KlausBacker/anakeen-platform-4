<template>
    <div class="hub-station-component">
        <header v-if="isHeaderEnabled" class="hub-station-bar hub-station-bar--header">
            <hub-dock ref="dockTop" :expanded="true" :expandable="false" size="4.5rem"
                      :position="DockPosition.TOP">
            </hub-dock>
        </header>
        <section class="hub-station-center-area">
            <aside v-if="isLeftEnabled" class="hub-station-aside hub-station-left">
                <hub-dock ref="dockLeft" @tabSelected="_onDockTabSelected(DockPosition.LEFT, $event)"
                          :position="DockPosition.LEFT">
                    <hub-dock-entry v-for="(entry, index) in getDockContent(configData.left)" :key="`entry-content-${index}`" :name="`${entry.component.name}-entry-${index}`" @dockEntrySelected="onDockEntrySelected(entry)">
                        <template slot="collapsedContent">
                            <component :is="getCollapsedTemplate(entry)"></component>
                        </template>
                        <template slot="expandedContent">
                            <component :is="getExpandedTemplate(entry)"></component>
                        </template>
                    </hub-dock-entry>
                </hub-dock>
            </aside>
            <section class="hub-station-content">
                <div id="hubStationContent"></div>
                <!--<router-multi-view class="hub-station-content-view"></router-multi-view>-->
            </section>
            <aside v-if="isRightEnabled" class="hub-station-right">
                <hub-dock ref="dockRight"
                          @tabSelected="_onDockTabSelected(DockPosition.RIGHT, $event)" class="hub-dock hub-dock--right"
                           :position="DockPosition.RIGHT">
                </hub-dock>
            </aside>
        </section>
        <footer v-if="isFooterEnabled" class="hub-station-bar hub-station-bar--footer">
            <hub-dock ref="dockBottom"
                      @tabSelected="_onDockTabSelected(DockPosition.BOTTOM, $event)"
                      :expandable="false" :position="DockPosition.BOTTOM">
            </hub-dock>
        </footer>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./HubStation.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script lang="ts" src="./HubStation.component.ts">
</script>
