<template>
    <button id="logout-button" :title="translations.title" class="logout-button" @click="logout"><slot name="content"><i class="fa fa-power-off"></i></slot></button>
</template>



<style lang="scss" scoped>
  @import './Logout.scss';
</style>

<script src="./Logout.component.js"></script>