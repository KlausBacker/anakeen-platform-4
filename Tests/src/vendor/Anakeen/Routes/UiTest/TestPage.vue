<template>
  <div class="ui-test-parent">
    <div class="systemLeft">
      <ank-se-list
        class="systemLeftDown"
        id="seList"
        label="Tests UI"
        smart-collection="TST_RENDER"
        logo-url="CORE/Images/anakeen-logo.svg"
        bind-event="se-selected: #seTabs.publicMethods.addSE"
      >
        <div class="systemListSlot">
          <ank-identity
            large="true"
            email-alterable="true"
            password-alterable="true"
          ></ank-identity>
          <ank-logout class="systemLeftUpRight"></ank-logout>
        </div>
      </ank-se-list>
    </div>
    <div class="systemRight">
      <!--<ank-se-tabs-->
        <!--id="seTabs"-->
        <!--bind-event="se-after-save: #seList.publicMethods.refreshList,-->
                                      <!--se-after-delete: #seList.publicMethods.refreshList"-->
      <!--&gt;-->
      <!--</ank-se-tabs>-->
      <div class="systemTop">
        <ank-se-grid collection="IUSER"></ank-se-grid>
      </div>
      <div class="systemBot">
        <ank-logout></ank-logout>
        <ank-identity :emailAlterable="true" :passwordAlterable="true"></ank-identity>
        <ank-authent></ank-authent>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
  import Vue from "vue";
  import Identity from "../../../../../../components/src/Identity/Identity.vue";
  import Logout from "../../../../../../components/src/Logout/Logout.vue";
  import SeList from "../../../../../../components/src/SEList/seList.vue";
  import SeTabs from "../../../../../../components/src/Tabs/Tabs.vue";
  import AnkSeGrid from "../../../../../../components/src/Grid/Grid.vue";
  import AnkAuthent from "../../../../../../components/src/Authent/Authent.vue";

  import { Component } from "vue-property-decorator";

  @Component({
    components: {
      "ank-logout": Logout,
      "ank-identity": Identity,
      "ank-se-list": SeList,
      "ank-se-tabs": SeTabs,
      "ank-se-grid": AnkSeGrid,
      "ank-authent": AnkAuthent
    }
  })
  export default class TestPage extends Vue{

  }
</script>
<!-- CSS to this component only -->
<style lang="scss">
@import "./TestPage.scss";
</style>
