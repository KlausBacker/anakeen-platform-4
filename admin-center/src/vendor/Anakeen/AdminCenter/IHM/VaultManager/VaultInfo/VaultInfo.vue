<!--suppress HtmlFormInputWithoutLabel -->
<template>
    <div class="vault-pie-chart">
        <div class="vault-infos card">

            <div class="vault-infos-content">
                <div class="vault-infos-card">
                    <div class="vault-infos-card-content">
                        <div class="vault-infos-card-content-items">
                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">{{ $t("AdminCenterVaultManager.Logical capacity used") }}:&nbsp</span>
                                <span class="vault-card-content-item-value">{{convertBytes(info.metrics.usedSize)}},&nbsp<i>({{info.metrics.totalCount}} Files)</i></span>
                            </span>

                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">{{ $t("AdminCenterVaultManager.Free logical capacity") }}:&nbsp</span>
                                <span class="vault-card-content-item-value">{{info.freespace}}</span>
                            </span>

                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">{{ $t("AdminCenterVaultManager.Disk Capacity") }}:&nbsp</span>
                                <span class="vault-card-content-item-value">{{convertBytes(info.disk.totalSize)}}</span>
                            </span>
                            <span class="vault-card-content-item">
                                <span class="vault-card-content-item-label">{{ $t("AdminCenterVaultManager.Disk capacity used") }}:&nbsp</span>
                                <span class="vault-card-content-item-value">{{convertBytes(info.disk.usedSize)}}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vault-buttons container-fluid">
                <div class="vault-action">
                    {{ $t("AdminCenterVaultManager.Logical capacity") }}:&nbsp;<b>{{convertBytes(info.metrics.totalSize)}}</b>&nbsp;:
                    <kendo-button class="k-primary" @click="onResizeDisk">{{ $t("AdminCenterVaultManager.btn Resize") }}</kendo-button>
                </div>
                    <div class="vault-action">
                        {{ $t("AdminCenterVaultManager.Vault server path") }}:&nbsp;<b>{{info.path}}</b>&nbsp;:
                        <kendo-button class="k-primary" @click="onMovePath">{{ $t("AdminCenterVaultManager.btn Move") }}</kendo-button>
                    </div>

            </div>
        </div>
        <div class="vault-graphs">
            <div class="disk-gauges" v-if="info.metrics.totalSize">
                <div class="disk-gauge" :class="{ diskerror: info.metrics.totalSize < info.metrics.usedSize }">

                    <p class="arc-title">{{ $t("AdminCenterVaultManager.Logical vault capacity") }}</p>
                    <kendo-lineargauge ref="logicalGauge"
                                       class="logical-gauge arc-gauge"
                                       :scale-min="0"
                                       :scale-vertical="false"
                                       :scale-max="getGaugeLogicalMax"
                                       :scale-ranges="getGaugeLogicalRanges"
                                       :scale-labels-format="getGaugeLogicalRangeLabel"
                                       >
                        <kendo-lineargauge-pointer :value="getGaugeLogicalUsed"
                                                   color="#5047E1AA"
                                                   :size="20"
                                                   shape="arrow"/>

                    </kendo-lineargauge>
                    <p class="arc-legend" v-html="logicalTemplate()"></p>
                </div>
                <div class="disk-gauge" :class="{ diskerror: info.disk.totalSize === 0 }">
                    <p class="arc-title">{{ $t("AdminCenterVaultManager.Physical disk capacity") }}</p>
                    <kendo-lineargauge ref="diskGauge"
                                       class="physical-gauge arc-gauge"
                                       :scale-min="0"
                                       :scale-vertical="false"
                                       :scale-max="getGaugeDiskMax"
                                       :scale-ranges="getGaugeDiskRanges"
                                       :scale-labels-format="getGaugeDiskRangeLabel">
                        <kendo-lineargauge-pointer :value="getGaugeDiskUsed"
                                                   color="#5047E1AA"
                                                   :size="20"
                                                   shape="arrow"/>

                    </kendo-lineargauge>
                    <p class="arc-legend" v-html="diskTemplate()"></p>
                </div>
            </div>
            <div class="disk-chart">
                <p class="disk-chart-legend">{{ $t("AdminCenterVaultManager.Vault disk file repartition") }}</p>
                <kendo-chart ref="chart"
                             class="vault-chart"
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
        <div ref="movePathForm" class="vault-move-form" style="display: none">
            <p> {{ $t("AdminCenterVaultManager.Current path is") }}</p>
            <pre><b>{{ info.path }}</b></pre>

            <input ref="newPath" class="k-textbox" :placeholder="translations.NewPath">
            <div class="vault-buttons">
                <kendo-button
                        class="k-primary"
                        @click="requestMoveIt">{{ $t("AdminCenterVaultManager.btn Move it") }}
                </kendo-button>

                <kendo-button @click="closeWindow">{{ $t("AdminCenterVaultManager.btn Cancel") }}</kendo-button>
            </div>
        </div>

        <div ref="resizeVolumeForm" class="vault-resize-form" style="display: none">
            <p> {{ $t("AdminCenterVaultManager.Current size is") }}</p>
            <pre><b>{{ convertBytes(info.metrics.totalSize) }}</b></pre>

            <div class="vault-resize-inputs">
                <input ref="newSize"
                       @keyup.enter="requestResizeIt" class="k-textbox" :placeholder="translations.NewSize">

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
                        @click="requestResizeIt">{{ $t("AdminCenterVaultManager.btn Resize it") }}
                </kendo-button>

                <kendo-button @click="closeWindow">{{ $t("AdminCenterVaultManager.btn Cancel") }}</kendo-button>
            </div>
        </div>

        <div ref="infoUpdate" class="vault-updated" style="display: none">
            <p>
                <b>{{ requestMessage }}</b>
            </p>
            <kendo-button @click="closeWindow">{{ $t("AdminCenterVaultManager.btn Close") }}</kendo-button>

        </div>
    </div>
</template>
<style lang="scss">
@import "./VaultInfo.scss";
</style>
<script src="./VaultInfo.controller.ts" lang="ts"/>
