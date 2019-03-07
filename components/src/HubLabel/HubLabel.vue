<template>
  <div>
    <div v-html="label"></div>
  </div>
</template>

<script lang="ts">
import { Prop, Component, Vue, Mixins } from "vue-property-decorator";
import HubElementMixins from "../HubElement/Mixins/HubElementMixin";

@Component({
  mixins: [Mixins(HubElementMixins)]
})
export default class HubLabel extends Vue {
  @Prop({ type: String, default: "" }) label;
}
</script>
