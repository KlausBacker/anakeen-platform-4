<template>
    <div class="ui-infos">
        <span class="ui-title"><b>{{ssName}}</b> &nbsp Overview</span>
        <div class="ui-infos-content">
            <div class="ui-infos-card">
                <div class="ui-infos-card-title">View Controller</div>
                <div class="ui-infos-card-content">
                    <div class="ui-infos-card-content-item">
                        <span class="ui-card-content-item-label">Identification:</span>
                        <span class="ui-card-content-item-value">{{cvdoc.id}}</span>
                    </div>
                    <div class="ui-infos-card-content-item">
                        <span class="ui-card-content-item-label">Name:</span>
                        <span class="ui-card-content-item-value"><a data-role="develRouterLink"
                                                                 :href="`/devel/ui/${ssName}/views`"
                                                                 style="text-decoration: underline; color: #157EFB"> {{cvdoc.name}}</a></span>
                    </div>
                    <div class="ui-infos-card-content-item">
                        <span class="ui-card-content-item-label">Title:</span>
                        <span class="ui-card-content-item-value">{{cvdoc.title}}</span>
                    </div>
                </div>
            </div>
            <div class="ui-infos-card">
                <div class="ui-infos-card-title">Render Access</div>
                <div class="ui-infos-card-content">
                    <div class="ui-infos-card-content-item">
                        <span class="ui-card-content-item-label">Access Class:</span>
                        <span class="ui-card-content-item-value">{{renderaccess.renderAccessClass}}</span>
                    </div>
                </div>
            </div>
            <div class="ui-infos-card">
                <div class="ui-infos-card-title">Mask</div>
                <div class="ui-infos-card-content">
                    <div class="ui-infos-card-content-item">
                        <span class="ui-card-content-item-label">Name:</span>
                        <span class="ui-card-content-item-value"><a data-role="develRouterLink"
                                                                    :href="`/devel/ui/${ssName}/masks`"
                                                                    style="text-decoration: underline; color: #157EFB">{{mskfamid}}</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script src="./Infos.controller.js">
</script>
<style lang="scss">
    @import "./Infos.scss";
</style>