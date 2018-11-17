<template>
    <div class="ui-parent-section">
        <ss-list routeName="Ui::name"
                 routeParamField="ssName"
                 position="left"
        >
        </ss-list>
    </div>
</template>

<style>
    .ui-parent-section {
        min-height: 0;
        padding: 2rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
</style>