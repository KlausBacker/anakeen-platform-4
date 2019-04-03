import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";

@Component({
    components: {
        "ank-se-grid": AnkSEGrid
    }
})
export default class Welcome extends Vue {
    @Prop({ default: () => [], type: Array}) public creation!: Array<object>;
    @Prop({ default: () => [], type: Array}) public gridCollections!: Array<object>;
    protected onCreateClick(createInfo, event) {
        this.$emit("tabWelcomeCreate", createInfo);
    }

    public mounted() {
        this.$nextTick(() => {
            $(window).trigger("resize");
        })
    }
}
