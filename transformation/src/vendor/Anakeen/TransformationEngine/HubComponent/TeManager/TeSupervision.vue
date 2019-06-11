<template>
    <div class="te-supervision-component">
        <ank-splitter ref="teSplitter" class="te-manager-splitter" :panes="tePanes"
                      localStorageKey="te-manager-splitter">
            <template slot="left">
                <div class="te-grid-box">
                    <div ref="teSupervisionGrid" class="te-supervision-grid" />
                </div>
            </template>
            <template slot="right">
                <div>
                    <te-task-info :task-data="selectedTask" @task-deleted="refreshGrid"/>
                </div>

            </template>
            <template slot="emptyIcon">
                <div class="te-logo">
                    <span class="material-icons">file_copy</span>
                </div>
            </template>
            <template slot="emptyText">
                <span class="te-empty-text">Select task to view detail</span>
            </template>
        </ank-splitter>
    </div>
</template>
<style lang="scss">
    @import "./TeSupervision.scss";
</style>
<script src="./TeSupervision.controller.ts" lang="ts"></script>
