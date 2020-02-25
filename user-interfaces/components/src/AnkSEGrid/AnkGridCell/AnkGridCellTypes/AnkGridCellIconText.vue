<template>
  <a :href="linkUrl" target="_blank">
    <img class="smart-element-grid-cell-content--icon" :src="iconUrl" />
    <span class="smart-element-grid-cell-content--title">{{ fieldValue.displayValue }}</span>
  </a>
</template>

<style lang="scss" scoped>
a {
  display: flex;
  align-items: center;
  cursor: pointer;
  &:hover {
    text-decoration: underline;
  }
  .smart-element-grid-cell-content--title {
    margin-left: 0.25rem;
  }
}
</style>

<script lang="ts">
import { Component, Mixins } from "vue-property-decorator";
import AnkGridCellMixin from "../AnkGridCellMixin";

@Component({
  name: "ank-grid-cell-icontext"
})
export default class AnkGridCellIconText extends Mixins(AnkGridCellMixin) {
  public get linkUrl() {
    const url = this.fieldValue.url;
    if (url) {
      if (this.columnConfig.smartType === "file" || this.columnConfig.smartType === "image") {
        const reg = /inline=(\w+)&?/;
        let matched = false;
        let urlNoInline = url.replace(reg, (match, capture) => {
          let replace = "inline=no";
          if (match && capture) {
            matched = true;
            if (match.charAt(match.length - 1) === "&") {
              replace += "&";
            }
            return replace;
          }
        });
        if (!matched) {
          urlNoInline += "&inline=no";
        }
        return urlNoInline;
      }
    }
    return url;
  }

  public get iconUrl() {
    switch (this.columnConfig.smartType) {
      case "image":
        return this.fieldValue.thumbnail;
      default:
        return this.fieldValue.icon;
    }
  }
}
</script>
