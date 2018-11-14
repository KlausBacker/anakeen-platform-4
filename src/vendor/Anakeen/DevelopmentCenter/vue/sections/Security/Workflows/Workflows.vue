<template>
    <div class="security-workflows-section">
        <div class="security-workflows-section-title">
            <h3>Workflows Security Configuration</h3>
        </div>
        <div class="security-workflows-section-content">
            <workflows-list listUrl="/api/v2/devel/smart/workflows/" :filter="{ placeholder: 'Search a workflow'}"
                            vendorCategory="auto" routeName="Security::Workflows::Content"
                            routeParamField="workflowId"></workflows-list>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./Workflows.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./Workflows.controller.js"></script>
