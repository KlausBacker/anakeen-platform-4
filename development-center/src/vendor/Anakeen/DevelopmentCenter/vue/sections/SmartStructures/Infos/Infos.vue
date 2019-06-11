<template>
    <div class="smart-structure-infos-section" v-if="isReady">
        <div class="smart-structure-title">
            <h1><img class="smart-structure-icon" :src="structureProperties.icon"/> {{ssName}}</h1>
            <div class="smart-structure-toolbar">
                <div class="k-button-group create-buttons-group">
                    <button class="k-button k-button-icontext k-primary" @click="onCreate"><i class="k-icon k-i-plus"></i>&nbsp;Create</button>
                </div>
                <div class="k-button-group view-buttons-group">
                    <button class="k-button k-button-icontext" @click="onView('json')"><i class="k-icon k-i-css"></i>View JSON</button>
                    <button class="k-button k-button-icontext" @click="onView('xml')"><i class="k-icon k-i-html"></i>View XML</button>
                </div>
            </div>
        </div>
        <div class="smart-structure-infos-content">
            <div class="smart-structure-infos-column">
                <div class="smart-structure-infos-card hierarchy-card">
                    <div class="infos-card-title">Inheritance</div>
                    <div class="infos-card-content">
                        <structure-hierarchy v-if="!isHierarchyEmpty" :currentStructure="ssName" :data="structureHierarchy"></structure-hierarchy>
                        <div style="text-align: center" v-else><span>No inheritance</span></div>
                    </div>
                </div>
                <div class="smart-structure-infos-card">
                    <div class="infos-card-title">Structure</div>
                    <div class="infos-card-content">
                        <div class="infos-card-content-item">
                            <span class="card-content-item-label">Number of fields (Scalar fields / All fields):</span>
                            <span class="card-content-item-value">{{structureInfo.fields.leafCount}} / {{structureInfo.fields.count}}</span>
                        </div>
                        <div class="infos-card-content-item">
                            <span class="card-content-item-label">Number of parameters (Scalar parameters / All parameters): </span>
                            <span class="card-content-item-value">{{structureInfo.parameters.leafCount}} / {{structureInfo.parameters.count}}</span>
                        </div>
                    </div>
                </div>
                <div class="smart-structure-infos-card">
                    <div class="infos-card-title">Elements</div>
                    <div class="infos-card-content">
                        <div class="infos-card-content-item">
                            <span class="card-content-item-label">Classname :</span>
                            <span class="card-content-item-value">{{structureDetails.className}}</span>
                        </div>
                        <div class="infos-card-content-item">
                            <span class="card-content-item-label">Number of Smart Elements (with structures children): </span>
                            <span class="card-content-item-value">{{structureInfo.elements.count}} ({{structureInfo.elements.countWithChilds}})</span>
                        </div>
                        <div class="infos-card-content-item">
                            <span class="card-content-item-label">Is revisable :</span>
                            <span class="card-content-item-value">{{structureInfo.revision.isRevisable ? "Yes" : "No"}}</span>
                            <span class="card-content-item-value" v-if="structureInfo.revision.isRevisable">(max revision: {{structureInfo.revision.maxRevision ? structureInfo.revision.maxRevision : "&infin;"}})</span>
                        </div>
                    </div>
                </div>
                <div class="smart-structure-infos-card">
                    <div class="infos-card-title">Infos</div>
                    <div class="infos-card-content" style="display: flex; justify-content: center; align-items: center;">
                        <infos-nav :infoSections="otherInfoSections"></infos-nav>
                    </div>
                </div>
            </div>
            <div class="smart-structure-infos-column">
                <div class="smart-structure-infos-card">
                    <div class="infos-card-title">Properties</div>
                    <div class="infos-card-content">
                        <properties-view :elementId="ssName"></properties-view>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script src="./Infos.controller.js">

</script>

<style scoped lang="scss">
    @import "./Infos.scss";
</style>