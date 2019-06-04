<template>
  <div class="localization-section">
    <kendo-grid
      ref="localizationGrid"
      height="100%"
      :column-menu-columns="true"
      :column-menu-filterable="false"
      filterable-mode="row"
      filterable-operators-string-contains="Contains"
      :sortable="false"
      :resizable="true"
      :pageable="{ pageSizes: [100, 500, 1000] }"
      @hook:mounted="bindFilters"
    >
      <kendo-grid-column
        field="msgctxt"
        title="Context"
        :filterable-cell-template="privateMethods.filterTemplate('msgctxt')"
      ></kendo-grid-column>
      <kendo-grid-column
        field="msgid"
        title="msgid"
        :filterable-cell-template="privateMethods.filterTemplate('msgid')"
      ></kendo-grid-column>
      <kendo-grid-column
        v-for="(lang, index) in supportedLanguages"
        :key="index"
        :sortable="false"
        :attributes="{ class: 'lang-cell-value' }"
        :header-template="privateMethods.countryHeaderTemplate(lang)"
        :filterable-cell-template="privateMethods.filterTemplate(lang.field)"
        v-bind="lang"
      ></kendo-grid-column>
      <kendo-grid-column
        field="files"
        title="Origin Files"
        :filterable-cell-template="privateMethods.filterTemplate('files')"
        :template="privateMethods.filesTemplate()"
      ></kendo-grid-column>
      <kendo-datasource
        slot="kendo-datasource"
        ref="dataSource"
        :transport-read="privateMethods.readData"
        :schema-parse="privateMethods.parseData"
        :schema-model="listModel"
        :pageSize="500"
      ></kendo-datasource>
    </kendo-grid>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./Localization.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./Localization.controller.js"></script>
