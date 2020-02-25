<template>
  <div class="hub-mockup">
    <nav class="top">
      <div class="start">
        <div
          :title="item.title"
          @click="selectConfig(item.initid)"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          v-for="item in info.TOP_LEFT"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="middle">
        <div
          :title="item.title"
          @click="selectConfig(item.initid)"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          v-for="item in info.TOP_CENTER"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="end">
        <div
          :title="item.title"
          @click="selectConfig(item.initid)"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          v-for="item in info.TOP_RIGHT"
        >
          {{ item.key.value }}
        </div>
      </div>
    </nav>

    <section>
      <nav class="left">
        <div class="start">
          <div
            :title="item.title"
            @click="selectConfig(item.initid)"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            v-for="item in info.LEFT_TOP"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="middle">
          <div
            :title="item.title"
            @click="selectConfig(item.initid)"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            v-for="item in info.LEFT_CENTER"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="end">
          <div
            :title="item.title"
            @click="selectConfig(item.initid)"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            v-for="item in info.LEFT_BOTTOM"
          >
            {{ item.key.value }}
          </div>
        </div>
      </nav>

      <div class="content" />

      <nav class="right">
        <div class="start">
          <div
            :title="item.title"
            @click="selectConfig(item.initid)"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            v-for="item in info.RIGHT_TOP"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="middle">
          <div
            :title="item.title"
            @click="selectConfig(item.initid)"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            v-for="item in info.RIGHT_CENTER"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="end">
          <div
            :title="item.title"
            @click="selectConfig(item.initid)"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            v-for="item in info.RIGHT_BOTTOM"
          >
            {{ item.key.value }}
          </div>
        </div>
      </nav>
    </section>

    <nav class="bottom">
      <div class="start">
        <div
          :title="item.title"
          @click="selectConfig(item.initid)"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          v-for="item in info.BOTTOM_LEFT"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="middle">
        <div
          :title="item.title"
          @click="selectConfig(item.initid)"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          v-for="item in info.BOTTOM_CENTER"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="end">
        <div
          :title="item.title"
          @click="selectConfig(item.initid)"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          v-for="item in info.BOTTOM_RIGHT"
        >
          {{ item.key.value }}
        </div>
      </div>
    </nav>
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
@import "HubAdminMockUp";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./HubAdminMockUp.controller.js"></script>
