<template>
    <kendo-splitter ref="ankSplitter" class="splitter-wrapper" orientation="horizontal" style="height:100%; background-color: #DEDEDE" :panes="panes">
        <slot name="left"></slot>
        <div class="splitter-wrapper-right-content">
            <div  class="splitter-empty" v-if="splitterEmpty">
                <div class="splitter-empty-content">
                    <slot class="splitter-empty-icon" name="emptyIcon"><span class="k-icon k-i-information splitter-empty-icon"></span></slot>
                    <slot class="splitter-empty-text" name="emptyText"><span class="splitter-empty-text"> Please select an element to open </span></slot>
                </div>
            </div>
            <slot v-else name="right"></slot>
        </div>
    </kendo-splitter>
</template>
<style scoped lang="scss">
    .splitter-wrapper {
        & /deep/ {
            .k-splitbar.k-splitbar-horizontal[role=separator],
            .k-ghost-splitbar-horizontal {
                &:active, &.k-state-focused {
                    background: #157EFB;
                }
                width: 1.5rem;
                .k-icon {
                    margin: 0;
                    font-size: 1.75rem;
                    &.k-collapse-prev, &.k-expand-next {
                        &::before {
                            content: "\e016";
                        }
                        cursor: w-resize;
                        flex: 1;
                        display: flex;
                        align-items: center;
                    }

                    &.k-collapse-next, &.k-expand-prev {
                        &::before {
                            content: "\e014";
                        }
                        cursor: e-resize;
                        flex: 1;
                        display: flex;
                        align-items: center;
                    }

                    &.k-resize-handle {
                        margin: 1rem 0;
                        border-top: 1px solid;
                        border-bottom: 1px solid;
                        cursor: col-resize;
                    }
                }
            }
        }
        .splitter-empty {
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            display: flex;
            background-color: #DEDEDE;
            .splitter-empty-icon {
                display: block;
                font-size: 10rem;
                padding-bottom: 5rem;
                padding-left: 6.5rem;
                color: #A4A4A4;
            }
            .splitter-empty-text {
                font-size: 1.5rem;
                color: #A4A4A4;
            }
        }
    }
</style>
<script>
  import Vue from "vue";
  import { Splitter, LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";

  Vue.use(LayoutInstaller);
  export default {
    name: "ank-splitter",
    components: {
      "ank-spliter": Splitter
    },
    props: {
      panes: {
        type: Array,
        default: () => []
      },
      localStorageKey: {
        type: String,
        default: ""
      }
    },
    data() {
      return {
        splitterEmpty: true
      };
    },
    mounted() {
      if (this.localStorageKey) {
        const savedSize = window.localStorage.getItem(this.localStorageKey);
        if (savedSize) {
          this.$refs.ankSplitter.kendoWidget().size(".k-pane:first", savedSize);
        }
        this.$refs.ankSplitter
          .kendoWidget()
          .bind(
            "resize",
            this.onSplitterResize
          );
      }
    },
    methods: {
      onSplitterResize() {
        window.localStorage.setItem(
          this.localStorageKey,
          this.$refs.ankSplitter.kendoWidget().size(".k-pane:first")
        );
      },
      disableEmptyContent() {
        this.splitterEmpty = false;
      },
      enableEmptyContent() {
        this.splitterEmpty = true;
      },
      toggleEmptyContent() {
        this.splitterEmpty = !this.splitterEmpty;
      },
      isEmptyContent() {
        return this.splitterEmpty;
      },
      expandPane(pane) {
        let realPane = pane;
        if (pane === "right") {
          realPane = ".k-pane:last"
        } else if (pane === "left") {
          realPane = ".k-pane:first";
        }
        this.$refs.ankSplitter.expand(realPane);
      },
      collapsePane(pane) {
        let realPane = pane;
        if (pane === "right") {
          realPane = ".k-pane:last"
        } else if (pane === "left") {
          realPane = ".k-pane:first";
        }
        this.$refs.ankSplitter.collapse(realPane);
      }
    }
  };

</script>