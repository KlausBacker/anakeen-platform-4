<template>
  <div class="fullsearch-list">
    <h1>list</h1>

    <ul>
      <li v-for="domain in domains" :key="domain">
        {{ domain.name }}
        <ul>
          <li v-for="config in domain.configs" :key="config">
            {{ config.structure }}
            <ul>
              <li v-for="field in config.fields" :key="field">
                {{ field.field }}: {{ field.weight }}
              </li>
            </ul>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</template>

<style lang="scss">
@import "./FullsearchConfig.scss";
</style>

<script src="./FullsearchConfig.controller.ts" lang="ts"></script>
