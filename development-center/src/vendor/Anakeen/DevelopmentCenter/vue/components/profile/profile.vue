<template>
  <div class="profile-acls-view">
    <div ref="profileContent" class="profile-content">
      <div ref="profileDescribe" class="profile-describe container-fluid">
        <div class="row profile-infos">
          <div class="col-sm">
            Title : <b>{{ title }}</b>
          </div>
          <div class="col-sm">
            Logical name : <b>{{ name }}</b>
          </div>
          <div class="col-sm">
            Id : <b>{{ id }}</b>
          </div>
          <div v-if="refProfile" class="col-sm">
            Computed from :
            <a href="#" @click="onClickRefProfile"
              ><img :src="refProfile.icon" /> {{ refProfile.title || refProfile.name || refProfile.id }}</a
            >
          </div>
          <div v-if="detachable" class="detach-button">
            <button class="k-button k-button-icon" @click="onDetachProfile">
              <i class="k-icon k-i-hyperlink-open"></i>
            </button>
          </div>
        </div>
        <div class="row-sm profile-toolbar">
          <div class="col-sm">
            <div class="switch-container">
              <label class="switch">
                <input id="extendedView" v-model="displayAllElements" type="checkbox" @change="updateGrid" />
                <span class="slider round"></span>
              </label>
              <label class="switch-label" for="extendedView">
                <span>Show all</span>
              </label>
            </div>
            <div class="show-all-switch switch-container" style="margin-left: 1rem">
              <label class="switch">
                <input v-model="showLabels" type="checkbox" />
                <span class="slider round"></span>
              </label>
              <label class="switch-label">
                <span>Show labels</span>
              </label>
            </div>
            <div class="show-all-columns-switch switch-container" style="margin-left: 1rem">
              <label class="switch">
                <input v-model="showColumns" type="checkbox" />
                <span class="slider round"></span>
              </label>
              <label class="switch-label">
                <span>Show all columns</span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="profileTreeReady" ref="profileTreeList"></div>
    <div v-if="showError" class="profile-error-content">
      <p class="profile-error-message">{{ errorToDisplay }}</p>
    </div>
  </div>
</template>

<script src="./profile.controller.js"></script>

<style scoped lang="scss">
@import "./profile.scss";
@import "./profile-header-rotate.scss";
</style>
