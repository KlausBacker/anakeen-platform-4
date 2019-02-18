<template>
    <div class="dock-tab-component">
        <div class="compact-fragment">
            <slot name="compact-label"></slot>
        </div>
        <div class="expanded-fragment">
            <slot  name="expanded-label"></slot>
        </div>
        <div class="content-fragment">
            <slot name="content"></slot>
        </div>
    </div>
</template>

<style scoped>

</style>

<script src="./DockTab.component.js"></script>