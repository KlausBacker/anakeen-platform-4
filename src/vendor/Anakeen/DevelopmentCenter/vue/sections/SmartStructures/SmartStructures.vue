<template>
    <div class="smart-structure-section">
        <ss-list routeName="SmartStructures::name"
                 routeParamField="ssName"
                 position="left"
        >
        </ss-list>
    </div>
</template>

<style>
    .smart-structure-section {
        min-height: 0;
        padding: 2rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
</style>