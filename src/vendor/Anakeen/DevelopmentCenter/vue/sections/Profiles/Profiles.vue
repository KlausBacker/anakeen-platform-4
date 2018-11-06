<template>
    <div class="security-se-section">
        <ank-se-grid urlConfig="/api/v2/devel/security/profiles/gridConfig" filterable="inline" @before-grid-cell-render="cellRender">
        </ank-se-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./Profiles.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./Profiles.controller.js">
</script>
