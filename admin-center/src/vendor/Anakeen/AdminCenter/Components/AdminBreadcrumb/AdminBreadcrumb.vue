<template>
    <div class="ank-breadcrumb-wrapper">
        <span v-if="rootLabel" class="ank-breadcrumb-root ank-breadcrumb-item">
            <span class="ank-breadcrumb-item-label ank-breadcrumb-root-label" @click="onRootClick">{{rootLabel}}</span>
        </span>
        <span v-for="path in paths" class="ank-breadcrumb-item">
          <span class="ank-breadcrumb-item-separator">{{breadcrumbSeparator}}</span>
          <span class="ank-breadcrumb-item-label" @click="onBreadcrumbItemClick(path)">{{path.label||''}}</span>
        </span>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./AdminBreadcrumb.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./AdminBreadcrumb.controller.js"></script>
