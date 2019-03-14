<template>
  <div
    ref="kendoWindow"
    class="smart-element-grid-window"
    style="display: none"
  >
    <div class="columns-search input-group">
      <input
        class="filter-input form-control k-textbox"
        type="search"
        v-model="searchInput"
        :placeholder="translations.columnSearch"
      />
      <div class="clear-button input-group-append">
        <i class="material-icons clear-filter-icon" @click="searchInput = ''"
          >close</i
        >
      </div>
    </div>
    <div ref="dragWrapper" class="columns-list-wrapper">
      <div class="columns-list">
        <div class="columns-list-header">
          <div class="column-grow">{{ translations.label }}</div>
          <div>{{ translations.display }}</div>
        </div>
        <div class="columns-list-content">
          <div
            :class="{
              'column-management-row': true,
              'column-actions': !!col.command
            }"
            v-for="col in validColumns"
          >
            <span class="cell-title column-grow"
              ><span v-html="col.title"></span
            ></span>
            <span>
              <label class="switch">
                <input
                  type="checkbox"
                  :checked="!col.hidden"
                  @click="onDisplayColumn($event, col)"
                />
                <span class="slider round"></span>
              </label>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="ank-se-grid-window-buttons">
      <button
        class="k-button k-button-icontext k-primary"
        @click="acceptChanges"
      >
        <i class="material-icons">check</i> {{ translations.applyChanges }}
      </button>
      <button class="k-button ank-se-grid-window-cancel-button" @click="close">
        {{ translations.cancel }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";
declare var kendo;

@Component
export default class GridDialog extends Vue {
  @Prop({ type: Array, default: () => [] }) public colsConfig;
  @Prop({ type: String, default: "" }) public title;
  @Watch("colsConfig")
  public watchColsConfig(newValue, oldValue) {
      this.columns = newValue;
  }
  @Watch("searchInput")
  public watchSearchInput(newValue, oldValue) {
      this.filter(newValue);
  }
  public changes: object = {};
  public columns: Array<any> = this.colsConfig;
  public kendoWindow: any = null;
  public kendoSortable: any = null;
  public searchInput: string = "";

  public get validColumns() {
      return this.columns.filter(c => !!c.field);
}
  public get dialogTitle() {
      return this.title || this.translations.dialogTitle
    }
  public get translations() {
      return {
        cancel: this.$pgettext("GridDialog", "Cancel"),
        applyChanges: this.$pgettext("GridDialog", "Apply changes"),
        organize: this.$pgettext("GridDialog", "Organize"),
        label: this.$pgettext("GridDialog", "Title"),
        display: this.$pgettext("GridDialog", "Display"),
        columnSearch: this.$pgettext("GridDialog", "Search a column..."),
        dialogTitle: this.$pgettext("GridDialog", "Columns management")
      }
    }
  public mounted() {
    this.kendoWindow = kendo.jquery(this.$refs.kendoWindow)
      .kendoWindow({
        visible: false,
        width: "50%",
        maxHeight: "80%",
        actions: [],
        modal: true,
        title: this.dialogTitle,
        close: e => {
          this.searchInput = "";
          this.$forceUpdate();
        },
        open: (e) => {
          e.sender.wrapper.find(".k-window-title").css("text-align", "center");
          e.sender.wrapper.find(".k-window-title").css("font-size", "1.5rem");
          e.sender.wrapper.find(".k-window-title").css("color", "#6F6F6F");
          e.sender.wrapper.find(".k-window-titlebar").css("border", "0");
        }
      })
      .data("kendoWindow");
  }
  public filter(filterInput = "") {
      if (!filterInput) {
        this.columns = this.colsConfig;
      } else {
        this.columns = this.colsConfig.filter(col => {
          const title = col.title ? col.title.toLowerCase() : col.title;
          if (title) {
            return title.includes(filterInput.toLowerCase())
          }
          return false;
        })
      }
    }
    public close() {
      if (this.kendoWindow) {
        this.kendoWindow.close();
      }
    }
    public open() {
      if (this.kendoWindow) {
        this.kendoWindow.center().open();
      }
    }
    public resize() {
      if (this.kendoWindow) {
        this.kendoWindow.resize();
      }
    }
    public acceptChanges() {
      this.$emit("change", this.changes);
      this.close();
    }
    public onDisplayColumn(e, colConfig) {
      if (e.target.checked) {
        this.changes[colConfig.field] = { display: true};
      } else {
        this.changes[colConfig.field] = { display: false};
      }
    }
}
</script>
<style lang="scss"></style>

<style lang="scss" scoped>
$switch-height: 1.333rem;
$switch-width: 2.5rem;
$slider-width: calc(#{$switch-height} - 0.333rem);
$slider-height: $slider-width;
.smart-element-grid-window {
  color: #6f6f6f;
  display: flex;
  flex-direction: column;

  .columns-search {
    min-height: 38px;
    position: relative;

    .clear-button {
      position: absolute;
      z-index: 10;
      top: 0.5rem;
      right: 0.5rem;

      .clear-filter-icon {
        font-size: 1.3rem;
        cursor: pointer;
        border-radius: 50%;
        &:hover {
          color: white;
          background: red;
        }
      }
    }
  }

  .columns-list-wrapper {
    overflow-y: auto;
    .columns-list {
      flex: 1;

      .column-actions .drag-handle {
        cursor: not-allowed;
      }

      .column-grow {
        flex: 1;
      }

      .columns-list-header {
        display: flex;
        font-size: 0.75rem;
        margin-bottom: 0.41rem;
        padding: 0.375rem 0.75rem;
      }

      .columns-list-content {
        .column-management-row {
          display: flex;
          align-items: center;
          border: 1px solid #e6e6e6;
          border-radius: 3px 3px 3px 3px;
          padding: 0.375rem 0.75rem;
          height: 3.333rem;

          & + .column-management-row {
            margin-top: 0.41rem;
          }
        }

        .cell-title {
          font-size: 1.0833rem;
          &::first-letter,
          & > span::first-letter {
            text-transform: capitalize;
          }
        }
      }

      /* The switch - the box around the slider */
      .switch {
        position: relative;
        display: inline-block;
        width: $switch-width;
        height: $switch-height;
        margin-bottom: 0;
      }

      /* Hide default HTML checkbox */
      .switch input {
        display: none;
      }

      /* The slider */
      .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: 0.4s;
        transition: 0.4s;
      }

      .slider:before {
        position: absolute;
        content: "";
        height: $slider-height;
        width: $slider-width;
        left: calc((#{$switch-height} - #{$slider-height}) / 2);
        bottom: calc((#{$switch-height} - #{$slider-height}) / 2);
        background-color: white;
        -webkit-transition: 0.4s;
        transition: 0.4s;
      }

      input:checked + .slider {
        background-color: #2196f3;
      }

      input:focus + .slider {
        box-shadow: 0 0 1px #2196f3;
      }

      input:checked + .slider:before {
        -webkit-transform: translateX(
          calc(
            #{$switch-width} - (#{$slider-width} + (#{$switch-height} - #{$slider-height}))
          )
        );
        -ms-transform: translateX(
          calc(
            #{$switch-width} - (#{$slider-width} + (#{$switch-height} - #{$slider-height}))
          )
        );
        transform: translateX(
          calc(
            #{$switch-width} - (#{$slider-width} + (#{$switch-height} - #{$slider-height}))
          )
        );
      }

      /* Rounded sliders */
      .slider.round {
        border-radius: $switch-height;
      }

      .slider.round:before {
        border-radius: 50%;
      }
    }
  }

  .ank-se-grid-window-buttons {
    display: flex;
    flex-direction: column;
    min-height: 100px;
    justify-content: center;
    align-items: center;

    .ank-se-grid-window-cancel-button {
      background: transparent;
      border: none;
      color: inherit;
      text-decoration: underline;
    }

    .k-button + .k-button {
      margin-top: 0.5rem;
    }
  }
}
</style>
