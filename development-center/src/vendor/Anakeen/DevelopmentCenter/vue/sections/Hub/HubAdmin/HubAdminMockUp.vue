<template>
  <div class="hub-mockup">
    <nav class="top">
      <div class="start">
        <div
          v-for="item in info.TOP_LEFT"
          :title="item.title"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          @click="selectConfig(item.initid)"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="middle">
        <div
          v-for="item in info.TOP_CENTER"
          :title="item.title"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          @click="selectConfig(item.initid)"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="end">
        <div
          v-for="item in info.TOP_RIGHT"
          :title="item.title"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          @click="selectConfig(item.initid)"
        >
          {{ item.key.value }}
        </div>
      </div>
    </nav>

    <section>
      <nav class="left">
        <div class="start">
          <div
            v-for="item in info.LEFT_TOP"
            :title="item.title"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            @click="selectConfig(item.initid)"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="middle">
          <div
            v-for="item in info.LEFT_CENTER"
            :title="item.title"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            @click="selectConfig(item.initid)"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="end">
          <div
            v-for="item in info.LEFT_BOTTOM"
            :title="item.title"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            @click="selectConfig(item.initid)"
          >
            {{ item.key.value }}
          </div>
        </div>
      </nav>

      <div class="content" />

      <nav class="right">
        <div class="start">
          <div
            v-for="item in info.RIGHT_TOP"
            :title="item.title"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            @click="selectConfig(item.initid)"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="middle">
          <div
            v-for="item in info.RIGHT_CENTER"
            :title="item.title"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            @click="selectConfig(item.initid)"
          >
            {{ item.key.value }}
          </div>
        </div>
        <div class="end">
          <div
            v-for="item in info.RIGHT_BOTTOM"
            :title="item.title"
            :class="{ selected: item.initid == selectedId }"
            class="component"
            @click="selectConfig(item.initid)"
          >
            {{ item.key.value }}
          </div>
        </div>
      </nav>
    </section>

    <nav class="bottom">
      <div class="start">
        <div
          v-for="item in info.BOTTOM_LEFT"
          :title="item.title"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          @click="selectConfig(item.initid)"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="middle">
        <div
          v-for="item in info.BOTTOM_CENTER"
          :title="item.title"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          @click="selectConfig(item.initid)"
        >
          {{ item.key.value }}
        </div>
      </div>
      <div class="end">
        <div
          v-for="item in info.BOTTOM_RIGHT"
          :title="item.title"
          :class="{ selected: item.initid == selectedId }"
          class="component"
          @click="selectConfig(item.initid)"
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
