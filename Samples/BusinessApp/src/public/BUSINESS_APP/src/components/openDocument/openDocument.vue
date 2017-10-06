<template>
    <div class="documentsList__openDocuments__wrapper">
        <iframe v-if="urlDocument" :src="urlDocument"></iframe>

    </div>
</template>
<script src="./openDocument.controller.js"></script>
