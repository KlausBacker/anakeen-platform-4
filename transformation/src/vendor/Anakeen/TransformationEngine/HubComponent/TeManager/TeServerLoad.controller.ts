import { ChartInstaller, KendoChart } from "@progress/kendo-charts-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import "@progress/kendo-ui/js/dataviz/chart/chart";
import { Component, Prop, Vue } from "vue-property-decorator";

Vue.use(ChartInstaller);
Vue.use(DropdownsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "TeServerLoad"
})
export default class TeServerLoad extends Vue {
  @Prop({ default: 30, type: Number })
  public statusMaxRange: number;

  public updateInterval = 5;
  public $refs!: {
    statusChart: KendoChart;
    cpuLoadChart: KendoChart;
  };

  public refreshSelect = [
    {
      text: "2 s",
      value: 2
    },
    {
      text: "5 s",
      value: 5
    },
    {
      text: "10 s",
      value: 10
    },
    {
      text: "60 s",
      value: 60
    },
    {
      text: "Never",
      value: 0
    }
  ];

  public cpuLoadData = {
    abscisses: Array(this.statusMaxRange).fill(""),
    axis: [
      {
        labels: {
          format: "{0}"
        },
        name: "Y"
      }
    ],
    series: [
      {
        axis: "Y",
        data: [],
        line: {
          style: "smooth"
        },
        name: "1 min"
      },
      {
        axis: "Y",
        data: [],
        line: {
          style: "smooth"
        },
        name: "5 min"
      },
      {
        axis: "Y",
        data: [],
        line: {
          style: "smooth"
        },
        name: "15 min"
      }
    ],
    tooltiptemplate: "#= series.name #: #= value #"
  };

  public statusData = {
    abscisses: Array(this.statusMaxRange).fill(""),
    axis: [
      {
        labels: {
          format: "{0}"
        },
        name: "Y"
      }
    ],
    series: [
      {
        axis: "Y",
        color: "#5484ca",
        data: [],
        name: "Processing"
      },
      {
        axis: "Y",
        color: "#c69e47",
        data: [],
        name: "Waiting"
      },
      {
        axis: "Y",
        color: "#a53935",
        data: [],
        name: "Transferring"
      }
    ],
    tooltiptemplate: "#= series.name #: #= value #"
  };

  protected kCpuLoad: any;
  protected kStatusChart: any;
  private currentTimeout: number;

  public onChangeInterval(e) {
    this.updateInterval = parseInt(e.sender.value(), 10);
    if (this.currentTimeout) {
      window.clearTimeout(this.currentTimeout);
      if (this.updateInterval > 0) {
        this.updateLoadInfo();
      }
    }
  }
  public updateLoadInfo() {
    this.$http
      .get("/api/admin/transformationengine/load")
      .then(response => {
        // update Load Cpu graph
        response.data.data.load.forEach((key, index) => {
          this.kCpuLoad.options.series[index].data.push(response.data.data.load[index] || 0);

          if (this.kCpuLoad.options.series[index].data.length > this.statusMaxRange) {
            this.kCpuLoad.options.series[index].data.shift();
          }
        });

        // update Status graph
        if (response.data.data.status_breakdown) {
          const statusKeys = ["P", "W", "T"];

          statusKeys.forEach((key, index) => {
            this.kStatusChart.options.series[index].data.push(response.data.data.status_breakdown[key] || 0);

            if (this.kStatusChart.options.series[index].data.length > this.statusMaxRange) {
              this.kStatusChart.options.series[index].data.shift();
            }
          });
        }
        this.kCpuLoad.refresh();
        this.kStatusChart.refresh();
      })
      .catch(() => {
        this.updateInterval = 0;
        if (this.currentTimeout) {
          window.clearTimeout(this.currentTimeout);
        }
      });
    if (this.updateInterval > 0) {
      this.currentTimeout = window.setTimeout(() => {
        this.updateLoadInfo();
      }, this.updateInterval * 1000);
    }
  }

  public beforeDestroy() {
    if (this.currentTimeout) {
      window.clearTimeout(this.currentTimeout);
    }
    $(window).off("resize.teload");
  }

  public mounted() {
    this.kCpuLoad = this.$refs.cpuLoadChart.kendoWidget();
    this.kStatusChart = this.$refs.statusChart.kendoWidget();

    this.updateLoadInfo();

    $(window).on("resize.teload", () => {
      this.kCpuLoad.refresh();
      this.kStatusChart.refresh();
    });
  }
}
