<template>
  <div class="conditions-wrapper">
    <div class="condition-enum" ref="conditionEnum">
      <div class="input-group margin-bottom-sm">
        <div class="k-textbox condition-type-wrapper">
          <div class="conditions-radio-container">
            <input
              class="conditions-enum-radio k-radio"
              id="enumAnd"
              type="radio"
              name="conditionType"
              value="and"
              ref="andRadioButton"
              @change="onConditionRuleTypeChange"
              v-model="conditionRuleType"
            />
            <label class="conditions-enum-label k-radio-label" for="enumAnd">Satisfait toutes les conditions</label>
          </div>
          <div class="conditions-radio-container">
            <input
              class="conditions-enum-radio k-radio"
              id="enumOr"
              type="radio"
              name="conditionType"
              value="or"
              ref="orRadioButton"
              @change="onConditionRuleTypeChange"
              v-model="conditionRuleType"
            />
            <label class="conditions-enum-label k-radio-label" for="enumOr">Satisfait au moins une condition</label>
          </div>
          <div class="conditions-radio-container">
            <input
              class="conditions-enum-radio k-radio"
              id="enumCustom"
              type="radio"
              name="conditionType"
              value="perso"
              ref="customRadioButton"
              @change="onConditionRuleTypeChange"
              v-model="conditionRuleType"
            />
            <label class="conditions-enum-label k-radio-label" for="enumCustom">Personnalisée</label>
          </div>
        </div>
      </div>
    </div>
    <div class="conditions-detail">
      <table class="conditions-detail-table" :class="isPerso ? 'conditions-table-custom' : ''">
        <thead>
          <tr>
            <th class="conditions-column-head-tool"></th>
            <th v-for="column in columns" :class="column.class" v-show="column.visible">
              <span>{{ column.title }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(condition, index) in conditions">
            <td class="conditions-table-delete-condition">
              <button class="btn btn-default btn-xs" :data-row="index" @click="deleteCondition($event, index)">
                <span class="fa fa-trash-o"></span>
              </button>
            </td>
            <td v-for="column in columns" v-show="column.visible">
              <component
                :is="column.componentName"
                :name="column.name"
                :row="index"
                :key="column.name + index"
                @valueChange="updateConditions"
                v-bind="condition[column.name]"
              ></component>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="conditions-table-tools">
        <div class="conditions-table-tools-add-line" title="Ajouter une nouvelle ligne">
          <button type="button" class="conditions-table-tools-add-line-button" @click="onAddLineButtonClick">
            <span class="fa fa-plus"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script src="./SearchConditions.controller.js"></script>
<style lang="scss">
@import "./SearchConditions.scss";
</style>
