<template>
  <td class="k-command-cell">

    <ul class="actionMenu" ref="actionMenu">
      <li v-for="action in actions" :key="action.name" :class="action.className" :data-actionType="action.name">
        <a v-if="!action.iconClass && !action.text" role="button" href="##" :class="'k-button k-grid' - +action.name">
          {{ action.name }}
        </a>

        <a v-else-if="!action.text" role="button" href="##" :class="'k-button k-button-icon k-grid-' + action.name">
          <span :class="action.iconClass" />
        </a>
        <a v-else-if="!action.iconClass" role="button" href="##" :class="'k-button k-grid-' + action.name">
          <span class="action-label">{{ action.text }}</span>
        </a>
        <a v-else role="button" href="##" :class="'k-button k-button-icontext k-grid-' + action.name">
          <span :class="action.iconClass" />
          <span class="action-label">{{ action.text }}</span>
        </a>
        <ul v-if="action.subActions" class="subactionsItems k-button k-button-icon k-grid-_subcommands">
          <li
            v-for="subAction in action.subActions"
            :key="subAction.name"
            :class="subAction.className"
            :data-actionType="subAction.name"
          >
            <a
              v-if="!subAction.iconClass && !subAction.text"
              role="button"
              href="##"
              :class="'k-button k-grid-' + subAction.name"
            >
              {{ subAction.name }}
            </a>
            <a
              v-else-if="!subAction.text"
              role="button"
              href="##"
              :class="'k-button k-button-icon k-grid-' + subAction.name"
            >
              <span :class="subAction.iconClass" />
            </a>
            <a v-else-if="!subAction.iconClass" role="button" href="##" :class="'k-button k-grid-' + subAction.name">
              <span class="subAction-label">{{ subAction.text }}</span>
            </a>
            <a v-else role="button" href="##" :class="'k-button k-button-icontext k-grid-' + subAction.name">
              <span :class="subAction.iconClass" />
              <span class="subAction-label">{{ subAction.text }}</span>
            </a>
          </li>
        </ul>
      </li>
    </ul>
  </td>
</template>
<style lang="scss">
@import "./AnkActionMenu.scss";
</style>
<script src="./AnkActionMenu.controller.ts" lang="ts"></script>
