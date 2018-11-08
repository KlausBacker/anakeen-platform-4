<template>
    <div class="smart-structure-infos-section" v-if="isReady">
        <h1 class="smart-structure-title"><img class="smart-structure-icon" :src="structureProperties.icon"/> {{ssName}} ({{structureProperties.title}}) Overview</h1>
        <div class="smart-structure-infos-content">
            <div class="smart-structure-infos-card">
                <div class="infos-card-title">Hierarchy</div>
                <div class="infos-card-content">
                    <structure-hierarchy :currentStructure="ssName" :data="structureHierarchy"></structure-hierarchy>
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
                <div class="infos-card-title">Tags</div>
                <div class="infos-card-content">
                    <div v-html="formatTags(structureDetails.tags)"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script src="./Infos.controller.js">

</script>

<style lang="scss">
    .smart-structure-infos-section {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 1rem;
        .smart-structure-title {
            display: flex;
            align-items: center;
            .smart-structure-icon {
                margin-right: 1rem;
            }
        }

        .smart-structure-infos-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            flex: 1;
            overflow-y: auto;
            .smart-structure-infos-card {

                .infos-card-title {
                    font-size: 1.5rem;
                    margin-bottom: 1rem;
                }

                & + .smart-structure-infos-card {
                    /*margin-left: 1rem;*/
                }
                margin: auto;
                padding: .5rem;
                border: 1px solid rgb(210, 210, 210);
                display: inline-block;
                box-shadow: 3px 3px rgba(210, 210, 210, 0.5);
                height: 40%;
                width: 45%;
                min-width: 30%;

                .infos-card-content {
                    overflow-y: auto;
                    height: calc(100% - 2.8rem);
                    font-size: 1.1rem;
                    .card-content-item-label {
                        font-weight: bold;
                        margin-right: .5rem;
                    }
                }
            }
        }
    }
</style>