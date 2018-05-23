<template>
    <button id="logout-button" :title="title" class="logout-button" @click="logout"><slot><i class="fa fa-power-off"></i></slot></button>
</template>



<style lang="scss">
  @import './Logout.scss';
</style>

<script src="./Logout.component.js"></script>