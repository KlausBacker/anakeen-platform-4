<template>
    <div class="login-parent">
        <div class="login-banner">
            <img class="imgbanner" src="/CORE/Images/anakeen-logo.svg" alt="Anakeen logo">
        </div>
        <div class="login-form">
            <ank-authent default-language="auto" authent-languages="fr_FR, en_US"></ank-authent>
        </div>
    </div>
</template>
<script lang="ts">
  import Vue from "vue";
  import AnkAuthent from "../../../../../../components/src/Authent/Authent.vue";

  import { Component } from "vue-property-decorator";

  @Component({
    components: {
      "ank-authent": AnkAuthent
    }
  })
  export default class LoginPage extends Vue{

  }
</script>