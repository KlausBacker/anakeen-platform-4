<template>
  <td class="smart-element-grid-cell">
   <span v-if="columnConfig.property">{{dataItem.properties[field]}}</span>
   <span v-else-if="columnConfig.abstract">{{dataItem.abstract[field].displayValue}}</span>
   <div v-else>
       <a v-if="columnConfig.smartType === 'docid'" href="#">
           <img :src="dataItem.attributes[field].icon"/>
           {{dataItem.attributes[field].displayValue}}
       </a>
       <span v-else>{{dataItem.attributes[field].displayValue}}</span>
    </div>
  </td>
</template>

<!-- CSS to this component only -->
<style lang="scss" scoped>
@import "./AnkGridCell.scss";
</style>

<script src="./AnkGridCell.component.ts" lang="ts"></script>
