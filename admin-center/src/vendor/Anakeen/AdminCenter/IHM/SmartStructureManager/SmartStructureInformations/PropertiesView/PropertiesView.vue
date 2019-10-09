<template>
    <div class="se-properties-view" v-if="isReady">
        <!--<h1>{{elementProperties.name||elementProperties.title}} properties</h1>-->
        <table class="se-properties-main table table-condensed table-hover">
            <thead>
            <tr class="se-properties-header">
                <th class="se-properties-header--description">Property</th>
                <th class="se-properties-header--value">Value</th>
            </tr>
            </thead>
            <tbody>
                <tr v-for="(property, index) in propertiesList" :key="index">
                    <td class="se-properties-description" style="width: 13rem;">{{property.label}}</td>
                    <td class="se-properties-value"><span v-html="renderPropValue(property.id, property.value)"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./PropertiesView.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./PropertiesView.controller.js"></script>
