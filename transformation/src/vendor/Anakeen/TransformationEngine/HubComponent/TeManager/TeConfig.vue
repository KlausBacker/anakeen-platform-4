<template>
    <div class="te-configuration">
        <label>
            <span>Activate TE Engine  :</span>
            <span><kendo-switch name="switch" v-model="info.TE_ACTIVATE"/></span>
        </label>
        <label>
            <span>Hostname of transformation engine server  :</span>
            <input class="k-textbox" v-model="info.TE_HOST"/>
        </label>
        <label>
            <span>Port number of transformation engine server :</span>
            <input class="k-textbox" type="number" v-model="info.TE_PORT"/>
        </label>
        <label>
            <span>Callback url for response :</span>
            <input class="k-textbox" placeholder="https://..." v-model="info.TE_URLINDEX"/>
        </label>
        <label>
            <span>Waiting delay (in seconds) to connect to transformation engine server :</span>
            <input class="k-textbox" type="number" v-model="info.TE_TIMEOUT"/>
        </label>


        <section class="te-check-section">
            <div class="te-check-button">
                <kendo-button @click="checkConfig" :disabled="testRunning" class="k-primary">Check connection</kendo-button>
                <div class="te-version" v-show="teVersion">
                    Version : <b>{{teVersion}}</b>
                </div>
            </div>
            <div class="te-check" v-show="this.kProgress">
                <div class="te-checkbar">
                    <p>{{ this.kProgress && this.kProgress.value() }} - {{ progressText }}</p>
                    <div ref="progressBar"/>
                </div>
                <div class="te-check-log">
                    <ol>
                        <li v-for="msg in progressMessages">{{ msg }}</li>
                    </ol>
                </div>
            </div>
        </section>
        <div class="te-config-buttons">
            <kendo-button @click="recordConfig" class="k-primary">Save</kendo-button>
        </div>
    </div>
</template>
<style lang="scss">
    @import "./TeConfig.scss";
</style>
<script src="./TeConfig.controller.ts" lang="ts"></script>
