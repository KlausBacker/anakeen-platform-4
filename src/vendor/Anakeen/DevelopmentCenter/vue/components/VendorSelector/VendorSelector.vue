<template>
    <div class="vendor-selector">
        <select ref="selector" :value="vendorCategory">
            <option value="anakeen">Anakeen</option>
            <option value="vendor">Vendors</option>
        </select>
    </div>
</template>
<!-- Global CSS -->
<style lang="scss">
    @import "./VendorSelector.scss";
</style>
<script src="./VendorSelector.controller.js"></script>
