import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.datepicker.js";
import { Component, Mixins, Vue } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import * as $ from "jquery";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(ButtonsInstaller);

@Component
export default class ActiveUsersController extends Mixins(AnkI18NMixin) {
  currentMonth = (new Date().getMonth() + 1).toString().padStart(2, "0");
  currentYear = new Date().getFullYear();
  public monthFrom = `${this.currentYear}-${this.currentMonth}`;
  public monthTo = `${this.currentYear}-${this.currentMonth}`;
  public activeUsersTotal = 0;
  public totalActiveUsers(): void {
    this.$http.get(`/api/v2/stats/connexions/login/count/${this.monthFrom}/${this.monthTo}`).then(response => {
      this.activeUsersTotal = response.data.data.count;
    });
  }
  public mounted(): void {
    this.totalActiveUsers();
    $(this.$refs.monthPickerFrom).kendoDatePicker({
      value: new Date(),
      max: new Date(),
      start: "year",
      depth: "year",
      format: "MMMM yyyy",
      dateInput: true,
      change: e => {
        const d = new Date(e.sender.value());
        const month = (d.getMonth() + 1).toString().padStart(2, "0");
        const year = d.getFullYear();
        this.monthFrom = `${year}-${month}`;
        this.checkRange();
      }
    });
    $(this.$refs.monthPickerTo).kendoDatePicker({
      value: new Date(),
      max: new Date(),
      start: "year",
      depth: "year",
      format: "MMMM yyyy",
      dateInput: true,
      change: e => {
        const d = new Date(e.sender.value());
        const month = (d.getMonth() + 1).toString().padStart(2, "0");
        const year = d.getFullYear();
        this.monthTo = `${year}-${month}`;
        this.checkRange();
      }
    });
  }
  public checkRange() {
    if (this.monthTo && this.monthFrom) {
      const to = $(this.$refs.monthPickerTo).data("kendoDatePicker");
      const from = $(this.$refs.monthPickerFrom).data("kendoDatePicker");
      if (from.value() > to.value()) {
        const tmp = from.value();
        $(this.$refs.monthPickerFrom)
          .data("kendoDatePicker")
          .value(to.value());
        $(this.$refs.monthPickerTo)
          .data("kendoDatePicker")
          .value(tmp);
        from.trigger("change");
        to.trigger("change");
      }
    }
  }
  public exportActiveUsers(): void {
    if (this.monthFrom && this.monthTo) {
      window.open(`/api/v2/stats/connexions/login/months/${this.monthFrom}/${this.monthTo}.xlsx`, "_self");
    } else {
      throw new Error("Please select a range of date");
    }
  }
}
