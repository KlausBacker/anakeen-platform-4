<template>
    <div class="te-manager">
        <div class="te-manager-box" v-if="true">
            <kendo-tabstrip @activate="onTabActivate">
                <ul>
                    <li class="k-state-active">
                        Configuration
                    </li>
                    <li  data-id="supervision">
                        Supervision
                    </li>
                    <li data-id="load">
                        Server load
                    </li>
                </ul>
                <div class="te-configuration">
                    <te-config/>
                </div>
                <div class="te-supervision">
                    <te-supervision v-if="supervisionActived"  ref="supervisionComponent"/>
                </div>

                <div class="te-server-load">
                    <te-server-load v-if="loadActived" ref="serverLoadComponent" />
                </div>

            </kendo-tabstrip>
        </div>
    </div>
</template>
<style lang="scss">
    @import "./TeManager.scss";
</style>

<script src="./TeManager.controller.ts" lang="ts"></script>
