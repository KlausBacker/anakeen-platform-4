<template>
    <div class="vault-pie-chart">
        <div class="vault-container">
            <div class="vault-row">
                <div class="col-sm col-sm-left">
                    <kendo-arcgauge ref="logicalGauge"
                                    class="logical-gauge arc-gauge"
                                    :scale-min="0"
                                    :value="info.metrics.usedSize"
                                    :scale-max="info.metrics.totalSize"
                                    :centerTemplate="logicalTemplate">
                        <kendo-arcgauge-color :to="info.metrics.totalSize * 0.75"
                                              :color="'#28a745'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.metrics.totalSize * 0.75"
                                              :to="info.metrics.totalSize * 0.90"
                                              :color="'#ffc107'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.metrics.totalSize * 0.90"
                                              :color="'#dc3545'"></kendo-arcgauge-color>
                    </kendo-arcgauge>
                    <kendo-arcgauge ref="diskGauge"
                                    class="disk-gauge arc-gauge"
                                    :scale-min="0"
                                    :value="info.disk.usedSize"
                                    :scale-max="info.disk.totalSize"
                                    :centerTemplate="diskTemplate">
                        <kendo-arcgauge-color :to="info.disk.totalSize * 0.75"
                                              :color="'#28a745'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.disk.totalSize * 0.75" :to="info.metrics.totalSize * 0.90"
                                              :color="'#ffc107'"></kendo-arcgauge-color>
                        <kendo-arcgauge-color :from="info.disk.totalSize * 0.90"
                                              :color="'#dc3545'"></kendo-arcgauge-color>
                    </kendo-arcgauge>
                    <div class="gauge-legend">
                        <div class="disk-green disk-space"></div><span>&nbsp0-75 %&nbsp</span>
                        <div class="disk-orange disk-space"></div><span>&nbsp75-90 %&nbsp</span>
                        <div class="disk-red disk-space"></div><span>&nbsp> 90 %&nbsp</span>
                    </div>
                </div>
                <div class="col-sm col-sm-right">
                    <kendo-chart ref="chart"
                                 class="vault-chart"
                                 :title-text="'Vault disk usage'"
                                 :chart-area-background="''"
                                 :legend-position="'bottom'"
                                 :series="info.series"
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
        <div class="vault-infos card">
            <span class="vault-title"><b>{{info.path}}</b><a class="modify-btn" title="Modify">Modify</a> <a
                    class="move-btn" title="Consult">Move</a></span>
            <div class="vault-infos-content">
                <div class="vault-infos-card">
                    <div class="vault-infos-card-content">
                        <div class="vault-infos-card-content-item">
                            <span class="vault-card-content-item-label">Logical Capacity:&nbsp</span>
                            <span class="vault-card-content-item-value">{{convertBytes(info.metrics.totalSize)}},&nbsp</span>
                            <span class="vault-card-content-item-label">Logical capacity used:&nbsp</span>
                            <span class="vault-card-content-item-value">{{convertBytes(info.metrics.usedSize)}},&nbsp</span>
                            <span class="vault-card-content-item-label">Disk Capacity:&nbsp</span>
                            <span class="vault-card-content-item-value">{{convertBytes(info.disk.totalSize)}},&nbsp</span>
                            <span class="vault-card-content-item-label">Disk capacity used:&nbsp</span>
                            <span class="vault-card-content-item-value">{{convertBytes(info.disk.usedSize)}},&nbsp</span>
                            <span class="vault-card-content-item-label">Files:&nbsp</span>
                            <span class="vault-card-content-item-value">{{info.metrics.totalCount}},&nbsp</span>
                            <span class="vault-card-content-item-label">Free logical capacity:&nbsp</span>
                            <span class="vault-card-content-item-value">{{info.freespace}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
    @import "./VaultInfo.scss";
</style>
<script src="./VaultInfo.controller.ts" lang="ts">
</script>
