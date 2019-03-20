<template>
    <ank-se-grid
            url-config="/api/v2/uitest/gridConfig/<collection>"
            collection="DEVNOTE"
            checkable="true"
            reorderable="true"
            context-titles="false"
            server-paging="true"
            server-filtering="true"
            server-sorting="true"
            persist-state-key="grid-test-page"
            persist-selection="true"
    >
    </ank-se-grid>
</template>
<script lang="ts">
    import Vue from "vue";
    import {AnkSEGrid} from "../../../../../../components/src";
    import {Component} from "vue-property-decorator";

    @Component({
    "ank-se-grid": AnkSEGrid
    })
    export default class TestGridPage extends Vue {

    }

</script>