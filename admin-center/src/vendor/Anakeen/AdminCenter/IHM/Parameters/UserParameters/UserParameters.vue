<template>
  <div class="user-parameters">
    <div class="user-search">
      <form class="user-search-form" @submit.prevent>
        <div class="form-group search-group">
          <span>&nbsp{{ $t("globalParameter.System") }}&nbsp</span>
          <label class="switch">
            <input
              type="checkbox"
              class="switch-parameters"
              @click="switchParameters"
            />
            <span class="slider round"></span>
          </label>
          <span>&nbsp{{ $t("globalParameter.User") }}&nbsp</span>
          <div class="input-group">
            <input
              autofocus
              class="form-control user-search-input"
              v-model.trim="inputSearchValue"
              :placeholder="translations.SearchUser"
            />
            <div class="input-group-append reset-button">
              <i
                class="input-group-addon material-icons reset-search-btn user-search-reset-btn"
                @click="clearSearchInput"
                >close</i
              >
            </div>
            <div class="input-group-append">
              <button
                class="btn btn-secondary"
                @click.stop="searchUser"
              >
                {{ $t("globalParameter.Search") }}
              </button>
            </div>
          </div>
        </div>
      </form>
      <div class="users-grid"></div>
      <div
        class="delete-confirmation-window"
        v-show="false"
        @keyup.enter.stop="closeDeleteConfirmation"
      >
        <div class="information-text">
          {{ $t("globalParameter.Parameter successfully") }}
        </div>
        <a
          class="delete-confirmation-btn form-parameter-btn"
          @click="closeDeleteConfirmation"
          >{{ $t("globalParameter.Back to parameters") }}</a
        >
      </div>
      <div
        class="delete-error-window"
        v-show="false"
        @keyup.enter.stop="closeDeleteError"
      >
        <div class="information-text">
          {{ $t("globalParameter.An error occured") }}
        </div>
        <a class="delete-error-btn form-parameter-btn" @click="closeDeleteError"
          >{{ $t("globalParameter.Back to parameters") }}</a
        >
      </div>
    </div>
    <div class="parameters-div">
      <div class="user-parameters-tree"></div>
      <admin-center-parameter-editor
        :edited-item="editedItem"
        :editRoute="editRoute"
        @closeEditor="updateAtEditorClose"
      ></admin-center-parameter-editor>
    </div>
  </div>
</template>

<script src="./UserParameters.controller.ts" lang="ts">
</script>

<style scoped>
@import "./UserParameters.css";
</style>
