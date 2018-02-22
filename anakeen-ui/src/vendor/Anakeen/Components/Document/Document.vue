<template>
    <iframe style="visibility:hidden" ref="iDocument" src="api/v2/documents/0.html" class="apDocumentWrapper"></iframe>
</template>

<script src="./Document.component.js" ></script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    iframe.apDocumentWrapper {
        width:100%;
        border:0;
        padding:0;
        margin:0;
    }
</style>


<style lang="scss" >
    @import "./Document.scss";
</style>
