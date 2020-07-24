<template>
  <div class="tasks-plugin">
    <ank-tabs ref="smartTab" v-model="mySelectedTab">
      <ank-tab tab-id="incomingTaskActions" :label="translations.incomingActions">
        <incoming-task v-bind="$props"></incoming-task>
      </ank-tab>
      <ank-tab tab-id="previousTaskActions" :label="translations.previousActions">
        <previous-task v-bind="$props"></previous-task>
      </ank-tab>
    </ank-tabs>
  </div>
</template>

<style lang="scss">
@import "./Tasks.scss";
</style>
<script lang="ts" src="./Tasks.ts" />
