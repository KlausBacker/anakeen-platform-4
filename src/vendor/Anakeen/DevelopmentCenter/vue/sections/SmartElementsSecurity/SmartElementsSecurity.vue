<template>
    <div class="security-se-section">
        <ank-se-grid urlContent="/api/v2/devel/security/profiles/" filterable="inline" @before-grid-cell-render="cellRender">
            <ank-se-grid-column :sortable="true" :filterable="true" field="title" :property="true" smartType="text"></ank-se-grid-column>
            <ank-se-grid-column title="Logical Name" :sortable="true" :filterable="true" field="name" :property="true" smartType="text"></ank-se-grid-column>
            <ank-se-grid-column title="Type" field="family" :sortable="true" :filterable="true" property="true" smartType="text"></ank-se-grid-column>
            <ank-se-grid-column title="Dynamic" field="dpdoc_famid" :sortable="true" :filterable="true" smartType="text"></ank-se-grid-column>
        </ank-se-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./SmartElementsSecurity.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./SmartElementsSecurity.controller.js">
</script>
