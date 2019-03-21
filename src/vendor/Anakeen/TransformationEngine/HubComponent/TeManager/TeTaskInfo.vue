<template>
    <section class="te-task-information">

        <!-- callback:"http://192.168.251.143:10080/api/admin/transformationengine/tests/894?ank-authorization=0bd6218828b6a147e37a5aa1808ea447e0d3401a"
callreturn:"{"success":true,"data":{"key":894},"messages":[]}"
comment:"return call : {"success":true,"data":{"key":894},"messages":[]}"
engine:"utf8"
fkey:""
infile:"/var/tmp/te-task-CxiVe8/tes-Hsa0L0"
inmime:"text/plain;"
outfile:"/var/tmp/te-task-CxiVe8/ter-KSKpO9.utf8"
pid:""
status:"D"
tid:"5c8614971da198.78727317"-->
        <div class="te-task-info">
            <label>Task Id</label>
            <div>{{data.info.tid}}</div>
        </div>
        <div class="te-task-info">
            <label>Engine</label>
            <div>{{data.info.engine}}</div>
        </div>
        <div class="te-task-info">
            <label>Request date</label>
            <div>{{data.info.cdate}}</div>
        </div>
        <div class="te-task-info">
            <label>Input File</label>
            <div>{{data.info.infile}}</div>
        </div>
        <div class="te-task-info">
            <label>Output File</label>
            <div>{{data.info.outfile}}</div>
        </div>
        <div class="te-task-info">
            <label>Status</label>
            <div :class="statusClass">{{data.info.status}}</div>
        </div>
        <div class="te-task-info">
            <label>Callback</label>
            <div>{{data.info.callback}}</div>
        </div>
        <div class="te-task-info">
            <label>Callback response</label>
            <div>{{data.info.callreturn}}</div>
        </div>
        <div class="te-task-info">
            <label>Comment</label>
            <div>{{data.info.comment}}</div>
        </div>

        <div class="te-task-info">
            <label>Log History</label>


            <div v-for="item in data.histo">
                <div>{{item.date}}</div>
                <div class="te-log-comment">{{item.comment}}</div>
            </div>
        </div>
        <div class="te-task-buttons">
            <kendo-button :disabled="deleting" class="button-delete" @click="deleteTask">Delete</kendo-button>
        </div>

    </section>
</template>
<style lang="scss">
    @import "./TeTaskInfo.scss";
</style>
<script src="./TeTaskInfo.controller.ts" lang="ts"></script>
