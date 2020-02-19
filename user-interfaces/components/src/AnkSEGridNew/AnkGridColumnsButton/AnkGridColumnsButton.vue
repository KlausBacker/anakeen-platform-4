<template>
  <div ref="columnsWrapper" class="columns-wrapper">
    <div ref="columnsButton" class="grid-columns-button"></div>
    <div ref="kendoWindow" class="smart-element-grid-window" style="display: none">
      <div class="columns-search input-group">
        <input
          class="filter-input form-control k-textbox"
          type="search"
          v-model="searchInput"
          :placeholder="translations.columnSearch"
        />
        <div class="clear-button input-group-append">
          <i class="material-icons clear-filter-icon" @click="searchInput = ''">close</i>
        </div>
      </div>
      <div ref="dragWrapper" class="columns-list-wrapper">
        <div class="columns-list">
          <div class="columns-list-header">
            <div class="column-grow">{{ translations.label }}</div>
            <div>{{ translations.display }}</div>
          </div>
          <div class="columns-list-content">
            <div
              v-for="col in validColumns"
              :class="{
                'column-management-row': true,
                'column-actions': !!col.command
              }"
            >
              <span class="cell-title column-grow"><span v-html="col.title"></span></span>
              <span>
                <label class="switch">
                  <input type="checkbox" :checked="!col.hidden" @click="onDisplayColumn($event, col)" />
                  <span class="slider round"></span>
                </label>
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="ank-se-grid-window-buttons">
        <button class="k-button k-button-icontext k-primary" @click="acceptChanges">
          <i class="material-icons">check</i> {{ translations.applyChanges }}
        </button>
        <button class="k-button ank-se-grid-window-cancel-button" @click="close">
          {{ translations.cancel }}
        </button>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
@import "./AnkGridColumnsButton.scss";
</style>
<script src="./AnkGridColumnsButton.controller.ts" lang="ts"></script>
