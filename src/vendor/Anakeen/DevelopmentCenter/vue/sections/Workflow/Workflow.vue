<template>
  <div class="wfl-parent-section">
    <div class="wfl-ss-list-empty" v-show="wflIsEmpty">
      <span class="k-icon k-i-folder-open wfl-empty-icon"></span>
      <span class="wfl-empty-text"
        >There are currently no Workflows associated with a Smart Structure
        ...</span
      >
    </div>
    <ss-list
      v-show="!wflIsEmpty"
      ref="wflSSList"
      routeName="Wfl::name"
      routeParamField="wflName"
      position="left"
      :filter="{ placeholder: 'Search a workflow' }"
      listUrl="/api/v2/devel/workflow/structures/<type>/"
    >
    </ss-list>
  </div>
</template>
<style>
.wfl-parent-section {
  min-height: 0;
  padding: 2rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.wfl-ss-list-empty {
  padding: 0.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  border: 1px solid #d2d2d2;
  border-radius: 0.25rem;
  align-items: center;
  justify-content: center;
  color: #848484;
  min-height: 0;
  overflow: hidden;
}
.wfl-empty-icon {
  flex: 1;
  font-size: 20rem;
  margin-top: 20rem;
}
.wfl-empty-text {
  flex: 1;
  font-size: 24px;
}
</style>
<script>
    import SSList from "devComponents/SSList/SSList.vue";
export default {
  name: "Wfl",
  components: {
    "ss-list": SSList
  },
  data() {
    return {
      wflIsEmpty: true
    };
  },
  mounted() {
    this.$refs.wflSSList.dataSource.bind("change", e => {
      (e.items && e.items.length === 0)
        ? (this.wflIsEmpty = true)
        : (this.wflIsEmpty = false);
    });
  }
};</script
>œ
