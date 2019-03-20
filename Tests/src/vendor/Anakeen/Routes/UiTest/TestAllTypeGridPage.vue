<template>
    <ank-se-grid context-titles-separator=">"
                 url-config="/api/v2/uitest/gridConfig/<collection>"
                 context-titles="false"
                 collection="TST_DDUI_ALLTYPE"
                 server-paging="true"
                 server-filtering="true"
                 server-sorting="true"
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
    export default class TestAllTypeGridPage extends Vue {
      
    }
</script>