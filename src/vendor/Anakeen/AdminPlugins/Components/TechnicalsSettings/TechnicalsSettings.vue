<template>
    <div>
        <h1 class="main-title">Examples of kendo widget used in admin</h1>
        <div id="calendar"></div>
    </div>
</template>

<script>
    export default {
        name: "ank-admin-settings",
        mounted() {
            this.$('#calendar').kendoCalendar();
        }
    }
</script>

<style scoped>
    .main-title {
        text-decoration: underline;
        text-transform: capitalize;
    }
</style>