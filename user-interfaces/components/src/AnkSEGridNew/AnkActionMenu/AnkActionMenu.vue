<template>
  <td class="k-command-cell smart-element-grid-action-cell">
    <div class="smart-element-grid-action-cell--content">
      <a
        v-for="action in primaryActions"
        :key="action.action"
        :data-actionType="action.action"
        role="button"
        :class="
          `smart-element-grid-action smart-element-grid-action--${action.action} k-button k-grid-${action.action}`
        "
        @click="onRowActionClick($event, action)"
      >
        <i
          v-if="action.iconClass"
          :class="{ [action.iconClass]: !!action.iconClass, 'smart-element-grid-action--icon': true }"
        />
        <span class="smart-element-grid-action--label">{{ action.title || action.action }}</span>
      </a>
      <span
        v-if="secondaryActions.length"
        class="smart-element-grid-action-menu k-button"
        @mouseenter="hoverPopup = true"
        @mouseleave="hoverPopup = false"
      >
        <i
          v-if="secondaryActions"
          ref="secondaryActionsMenu"
          class="k-icon k-i-more-vertical"
          @click="showSecondaryActionsMenu = !showSecondaryActionsMenu"
        />
        <Popup
          anchor="secondaryActionsMenu"
          popup-class="smart-element-grid-action-menu--content"
          :show="showSecondaryActionsMenu"
          :anchor-align="{ horizontal: 'left', vertical: 'bottom' }"
          :popup-align="{ horizontal: 'right', vertical: 'top' }"
        >
          <a
            v-for="action in secondaryActions"
            :key="action.action"
            :data-actionType="action.action"
            role="button"
            :class="
              `smart-element-grid-action smart-element-grid-action--${action.action} k-button k-grid-${action.action}`
            "
            @click="onRowActionClick($event, action)"
          >
            <i
              v-if="action.iconClass"
              :class="{ [action.iconClass]: !!action.iconClass, 'smart-element-grid-action--icon': true }"
            />
            <span class="smart-element-grid-action--label">{{ action.title || action.action }}</span>
          </a>
        </Popup>
      </span>
    </div>
  </td>
</template>
<style lang="scss">
@import "./AnkActionMenu.scss";
</style>
<script src="./AnkActionMenu.controller.ts" lang="ts"></script>
