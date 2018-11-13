<template>
    <div class="se-properties-view" v-if="isReady">
        <h1>{{elementProperties.name||elementProperties.title}} properties</h1>
        <table class="se-properties-main table table-condensed table-hover">
            <thead>
            <tr class="se-properties-header">
                <th class="se-properties-header--description">Propriété</th>
                <th class="se-properties-header--value">Valeur</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="se-properties-description">Titre</td>
                <td class="se-properties-value">{{elementProperties.title}}</td>
            </tr>
            <tr>
                <td class="se-properties-description">Identifiant</td>
                <td class="se-properties-value">{{elementProperties.id}}</td>
            </tr>
            <tr>
                <td class="se-properties-description">Nom logique</td>
                <td class="se-properties-value">{{elementProperties.name}}</td>
            </tr>
            <tr>
                <td class="se-properties-description">Révision</td>
                <td class="se-properties-value">{{elementProperties.revision}}</td>
            </tr>
            <tr>
                <td class="se-properties-description">Version</td>
                <td class="se-properties-value">{{elementProperties.version}}</td>
            </tr>
            <tr>
                <td class="se-properties-separator" colspan="2"></td>
            </tr>
            <tr>
                <td class="se-properties-description">Cycle de vie</td>
                <td class="se-properties-value">
                    <a v-if="elementProperties.workflow" :href="`/api/v2/documents/${elementProperties.wid}.html`"><img :src="elementProperties.workflow.icon">{{elementProperties.workflow.title}}</a>
                </td>
            </tr>
            <tr>
                <td class="se-properties-description">Activité</td>
                <td class="se-properties-value"></td>
            </tr>
            <tr>
                <td class="se-properties-description">Structure</td>
                <td class="se-properties-value">
                    <router-link v-if="elementProperties.family" :to="`/devel/smartStructures/${elementProperties.family.name}`">
                        <img :src="elementProperties.family.icon">{{elementProperties.family.title}}
                        <span v-if="elementProperties.family.name" class="se-properties-value--famname"> ({{elementProperties.family.name}})</span>
                    </router-link>

                </td>
            </tr>
            <tr>
                <td class="se-properties-separator" colspan="2"></td>
            </tr>
            <tr>
                <td class="se-properties-description">Créé par</td>
                <td class="se-properties-value">
                    <a v-if="elementProperties.createdBy"
                       target="_blank"
                       :href="`/api/v2/documents/${elementProperties.createdBy.id}.html`"><img
                        :src="elementProperties.createdBy.icon">{{elementProperties.createdBy.title}}</a>
                </td>
            </tr>
            <tr>
                <td class="se-properties-description">Verrouillé par</td>
                <td v-if="!elementProperties.locked" class="se-properties-value">Aucun verrou</td>
                <td class="se-properties-value" v-else>
                    <a v-if="elementProperties.security"
                       target="_blank"
                       :href="`/api/v2/documents/${elementProperties.security.lock.lockedBy.id}.html`"><img
                            :src="elementProperties.security.lock.lockedBy.icon">{{elementProperties.security.lock.lockedBy.title}}</a>
                </td>
            </tr>
            <tr>
                <td class="se-properties-description">Confidentiel</td>
                <td class="se-properties-value">Non confidentiel</td>
            </tr>
            <tr>
                <td class="se-properties-separator" colspan="2"></td>
            </tr>
            <tr>
                <td class="se-properties-description">Date de création</td>
                <td class="se-properties-value">
                    <span v-if="elementProperties.creationDate">{{formatDate(elementProperties.creationDate)}}</span>
                </td>
            </tr>
            <tr>
                <td class="se-properties-description">Date de dernière modification</td>
                <td class="se-properties-value">
                    <span v-if="elementProperties.lastModificationDate">{{formatDate(elementProperties.lastModificationDate)}}</span>
                </td>
            </tr>
            <tr>
                <td class="se-properties-separator" colspan="2"></td>
            </tr>
            <tr>
                <td class="se-properties-description">Profil</td>
                <td class="se-properties-value">
                    <router-link v-if="elementProperties.security && elementProperties.security.profil"
                       :to="`/devel/security/profiles/${elementProperties.security.profil.id}`">
                        <img :src="elementProperties.security.profil.icon">{{elementProperties.security.profil.title}}
                    </router-link>
                </td>
            </tr>
            <tr>
                <td class="se-properties-description">Profil de référence</td>
                <td class="se-properties-value"></td>
            </tr>
            <tr>
                <td class="se-properties-description">Field access</td>
                <td class="se-properties-value">
                    <a v-if="elementProperties.security && elementProperties.security.fieldAccess"
                       target="_blank"
                       :href="`/api/v2/documents/${elementProperties.security.fieldAccess.id}.html`"><img
                            :src="elementProperties.security.fieldAccess.icon">{{elementProperties.security.fieldAccess.title}}</a>
                </td>
            </tr>
            <tr>
                <td class="se-properties-description">Contrôle de vue</td>
                <td class="se-properties-value">
                    <a v-if="elementProperties.viewController"
                       target="_blank"
                       :href="`/api/v2/documents/${elementProperties.viewController.id}.html`"><img
                            :src="elementProperties.viewController.icon">{{elementProperties.viewController.title}}</a>
                </td>
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
