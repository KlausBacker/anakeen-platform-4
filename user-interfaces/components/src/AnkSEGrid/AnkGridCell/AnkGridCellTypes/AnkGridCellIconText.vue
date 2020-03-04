<template>
  <a href="#" @click.prevent="onClickLink">
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
import GridEvent from "../../AnkGridEvent/AnkGridEvent";
import { SmartGridCellPropertyValue } from "../../AnkSEGrid.component";

@Component({
  name: "ank-grid-cell-icontext"
})
export default class AnkGridCellIconText extends Mixins(AnkGridCellMixin) {
  public get linkUrl() {
    const url = (this.fieldValue as { [key: string]: string}).url;
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
        return (this.fieldValue as { [key: string]: string}).thumbnail;
      default:
        return (this.fieldValue as { [key: string]: string}).icon;
    }
  }
  public onClickLink() {
    const event = new GridEvent(
            {
              url: this.linkUrl,
              fieldValue: this.fieldValue
            },
            null,
            true // Cancelable
    );
    this.gridComponent.$emit("beforeDocidLink", event);
    if (!event.isDefaultPrevented()) {
      window.open(this.linkUrl);
    }
  }
}
</script>
