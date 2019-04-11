<!--suppress HtmlFormInputWithoutLabel -->
<template>
    <div class="vault-pie-chart">
        <div class="vault-infos card">

            <div class="vault-infos-content">
                <div class="vault-infos-card">
                    <div class="vault-infos-card-content">
                        <div class="vault-infos-card-content-items">
                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">Logical capacity used:&nbsp</span>
                                <span class="vault-card-content-item-value">{{convertBytes(info.metrics.usedSize)}},&nbsp<i>({{info.metrics.totalCount}} Files)</i></span>
                            </span>

                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">Free logical capacity:&nbsp</span>
                                <span class="vault-card-content-item-value">{{info.freespace}}</span>
                            </span>

                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">Disk Capacity:&nbsp</span>
                                <span class="vault-card-content-item-value">{{convertBytes(info.disk.totalSize)}}</span>
                            </span>
                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">Disk capacity used:&nbsp</span>
                                <span class="vault-card-content-item-value">{{convertBytes(info.disk.usedSize)}}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vault-buttons container-fluid">
                <div class="row">
                    <div class="col-md vault-title">
                        Vault server path:&nbsp;<b>{{info.path}}</b>
                    </div>
                    <div class="col-md">

                        <kendo-button class="k-primary" @click="onMovePath">Move</kendo-button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md vault-title">
                        Logical capacity:&nbsp;<b>{{convertBytes(info.metrics.totalSize)}}</b>
                    </div>
                    <div class="col-md">
                        <kendo-button class="k-primary" @click="onResizeDisk">Resize</kendo-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="vault-graphs">
            <div class="disk-gauges">
                <div class="disk-gauge" :class="{ diskerror: info.metrics.totalSize < info.metrics.usedSize }">
                    <p class="arc-title">Logical vault capacity</p>
                    <kendo-arcgauge ref="logicalGauge"
                                       class="logical-gauge arc-gauge"
                                       :scale-min="0"
                                       :value="getGaugeLogicalUsed"
                                       :scale-max="getGaugeLogicalMax"
                                       :scale-ranges="getGaugeLogicalRanges"
                                       :centerTemplate="logicalTemplate">
                        <kendo-arcgauge-color :to="info.metrics.totalSize * 0.75"
                                              :color="'#28a745'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.metrics.totalSize * 0.75"
                                              :to="info.metrics.totalSize * 0.90"
                                              :color="'#ffc107'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.metrics.totalSize * 0.90"
                                              :color="'#dc3545'"></kendo-arcgauge-color>
                    </kendo-arcgauge>
                </div>
                <div class="disk-gauge" :class="{ diskerror: info.disk.totalSize === 0 }">
                    <p class="arc-title">Physical disk capacity</p>
                    <kendo-arcgauge ref="diskGauge"
                                       class="physical-gauge arc-gauge"
                                       :scale-min="0"
                                       :value="getGaugeDiskUsed"
                                       :scale-max="getGaugeDiskMax"
                                       :scale-ranges="getGaugeDiskRanges"
                                       :centerTemplate="diskTemplate">
                        <kendo-arcgauge-color :to="info.metrics.totalSize * 0.75"
                                              :color="'#28a745'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.metrics.totalSize * 0.75"
                                              :to="info.metrics.totalSize * 0.90"
                                              :color="'#ffc107'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.metrics.totalSize * 0.90"
                                              :color="'#dc3545'"></kendo-arcgauge-color>

                    </kendo-arcgauge>
                </div>
                <div class="disk-chart">
                    <kendo-chart ref="chart"
                                 class="vault-chart"
                                 :title-text="'Vault disk file repartition'"
                                 :title-position="'top'"
                                 :chart-area-background="''"
                                 :legend-visible="false"
                                 :series="getSeries"
                                 :series-defaults-labels-visible="true"
                                 :series-defaults-labels-position="'outsideEnd'"
                                 :series-defaults-labels-background="'transparent'"
                                 :series-defaults-labels-template="labelTemplate"
                                 :tooltip-visible="false"
                                 :theme="'sass'">
                    </kendo-chart>
                </div>
            </div>
        </div>
        <div ref="movePathForm" class="vault-move-form" style="display: none">
            <p> Current path is</p>
            <pre><b>{{ info.path }}</b></pre>

            <input ref="newPath" class="k-textbox" placeholder="New path">
            <div class="vault-buttons">
                <kendo-button
                        class="k-primary"
                        @click="requestMoveIt">Move it
                </kendo-button>

                <kendo-button @click="closeWindow">Cancel</kendo-button>
            </div>
        </div>

        <div ref="resizeVolumeForm" class="vault-resize-form" style="display: none">
            <p> Current size is</p>
            <pre><b>{{ convertBytes(info.metrics.totalSize) }}</b></pre>

            <div class="vault-resize-inputs">
                <input ref="newSize" class="k-textbox" placeholder="New size">

                <kendo-dropdownlist
                        ref="kNewSizeUnit"
                        :data-source="sizeOptions"
                        value="1048576"
                        :data-text-field="'text'"
                        :data-value-field="'value'"
                        :options-label="'Select Size...'">
                </kendo-dropdownlist>
            </div>
            <div class="vault-buttons">
                <kendo-button
                        class="k-primary"
                        @click="requestResizeIt">Resize it
                </kendo-button>

                <kendo-button @click="closeWindow">Cancel</kendo-button>
            </div>
        </div>

        <div ref="infoUpdate" class="vault-updated" style="display: none">
            <p>
                <b>{{ requestMessage }}</b>
            </p>
            <kendo-button @click="closeWindow">Close</kendo-button>

        </div>
    </div>
</template>
<style lang="scss">
    @import "./VaultInfo.scss";
</style>
<script src="./VaultInfo.controller.ts" lang="ts">