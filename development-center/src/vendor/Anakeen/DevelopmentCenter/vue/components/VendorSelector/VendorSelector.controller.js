import * as MutationsTypes from "../../store/mutation-types";
export default {
  computed: {
    vendorCategory: {
      get() {
        return this.$store.getters.vendorCategory;
      },

      set(value) {
        this.$store.dispatch("selectVendorCategory", value);
      }
    }
  },
  mounted() {
    // if (this.$route.query && this.$route.query.vendorCategory) {
    //   if (this.vendorCategory !== this.$route.query.vendorCategory) {
    //     this.vendorCategory = this.$route.query.vendorCategory;
    //   }
    // }
    this.$store.subscribe(mutation => {
      if (mutation.type === MutationsTypes.SELECT_VENDOR_CATEGORY) {
        // this.$router.addQueryParams({ vendorCategory: mutation.payload });
      }
    });
    this.$(this.$refs.selector).kendoDropDownList({
      value: this.vendorCategory,
      select: e => {
        this.vendorCategory = e.dataItem.value;
      }
    });
  }
};
