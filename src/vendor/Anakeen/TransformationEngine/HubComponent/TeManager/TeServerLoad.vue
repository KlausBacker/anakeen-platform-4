<template>
    <section class="te-serverload">
        <div>
            <label>Refresh</label>
            <kendo-dropdownlist v-model="updateInterval"
                                @change="onChangeInterval"
                                :data-text-field="'text'"
                                :data-value-field="'value'"
                                :data-source="refreshSelect"/>

        </div>
        <div class="te-cpu-load">
            <kendo-chart
                    ref="cpuLoadChart"
                    :transitions="false"
                    :title-text="'Server CPU load'"
                    :legend-position="'bottom'"
                    :series-defaults-type="'area'"
                    :series="cpuLoadData.series"
                    :value-axis="cpuLoadData.axis"
                    :tooltip-visible="true"
                    :category-axis-categories="cpuLoadData.abscisses"

                    :tooltip-template="cpuLoadData.tooltiptemplate"
                    :theme="'sass'"/>

        </div>
        <div class="te-status-chart">
            <kendo-chart
                    ref="statusChart"
                    :transitions="false"
                    :title-text="'Tasks in progress'"
                    :series-defaults-type="'line'"
                    :legend-position="'bottom'"
                    :series="statusData.series"
                    :value-axis="statusData.axis"
                    :tooltip-visible="true"
                    :category-axis-categories="statusData.abscisses"
                    :tooltip-template="statusData.tooltiptemplate"
                    :theme="'sass'"/>
        </div>
    </section>
</template>
<style lang="scss">
    @import "./TeServerLoad.scss";
</style>
<script src="./TeServerLoad.controller.ts" lang="ts"></script>
