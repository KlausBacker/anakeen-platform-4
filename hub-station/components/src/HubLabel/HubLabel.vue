<template>
  <div>
    <nav v-if="isDockCollapsed">
      <div v-html="label"></div>
    </nav>
    <nav v-else-if="isDockExpanded">
      <div v-html="extendedLabel"></div>
    </nav>

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
  @Prop({ type: String, default: "" }) extendedLabel;
}
</script>
