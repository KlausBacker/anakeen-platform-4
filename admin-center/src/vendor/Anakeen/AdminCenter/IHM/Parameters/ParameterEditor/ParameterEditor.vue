<template>
  <div class="edition-window">
    <form v-if="editedItem" @keypress.enter.prevent="modifyParameter">
      <div class="form-group">
        <div class="form-label">{{ $t("globalParameter.Description") }} :</div>
        <span class="description-text">
          {{ editedItem.description }}
        </span>
      </div>
      <div class="form-group">
        <div class="form-label">{{ $t("globalParameter.Value") }} :</div>
        <input
          :type="parameterInputType"
          class="form-control value-input parameter-new-value"
          :value="inputSelectedValue"
          v-if="
            parameterInputType === 'text' ||
              parameterInputType === 'number' ||
              parameterInputType === 'password'
          "
          title="value"
        />
        <textarea
          :type="parameterInputType"
          class="form-control value-input parameter-new-value"
          :value="inputSelectedValue"
        v-else-if="parameterInputType === 'json'"
        title="value">
        </textarea>
        <select
          class="value-input parameter-new-value enum-drop-down"
          :value="inputSelectedValue"
          v-else="parameterInputType === 'enum'"
          title="value"
        >
          <option v-for="value in enumPossibleValues">{{ value }}</option>
        </select>
      </div>
      <div v-if="isNotJson" class="alert alert-warning invalid-json-warning" role="alert">
        {{ $t("globalParameter.Parameter is not a valid json") }}
      </div>
      <a class="modify-btn form-parameter-btn" @click="modifyParameter"
        >{{ $t("globalParameter.Save new value") }}</a
      >
      <a class="cancel-btn form-parameter-btn" @click="closeEditor"
        >{{ $t("globalParameter.Cancel value modification") }}</a
      >
    </form>
    <div
      class="confirmation-window"
      v-show="false"
      @keyup.enter.stop="closeConfirmationAndEditor"
    >
      <div class="information-text">{{ $t("globalParameter.Parameter successfully modified") }}</div>
      <a
        class="close-confirmation-btn form-parameter-btn"
        @click="closeConfirmationAndEditor"
        >{{ $t("globalParameter.Back to parameters") }}</a
      >
    </div>
    <div
      class="error-window"
      v-show="false"
      @keyup.enter.stop="closeErrorAndEditor"
    >
      <div class="information-text">
        {{ $t("globalParameter.An error occurred") }}
      </div>
      <a class="close-error-btn form-parameter-btn" @click="closeErrorAndEditor"
        >{{ $t("globalParameter.Back to parameters") }}</a
      >
    </div>
  </div>
</template>

<style scoped>
@import "./ParameterEditor.css";
</style>

<script src="./ParameterEditor.controller.ts" lang="ts">
</script>
