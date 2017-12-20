import Highcharts from 'highcharts';

require('highcharts/modules/exporting')(Highcharts);

export default {
    data() {
        return {
            chart: null,
            year: new Date().getFullYear(),
            formattedData: null,
        };
    },

    created() {
        this.privateScope = {
            sendGetRequest: (url, config, domEl) => {
                const element = this.$(domEl);
                this.$kendo.ui.progress(element, true);
                return new Promise((resolve, reject) => {
                    this.$http.get(url, config)
                        .then((response) => {
                            this.$kendo.ui.progress(element, false);
                            resolve(response);
                        }).catch((error) => {
                        this.$kendo.ui.progress(element, false);
                        reject(error);
                    });
                });
            },

            formatData: (data) => {
                const categoriesData = {};
                data.forEach((d) => {
                    const attributes = d.attributes;
                    const categories = attributes.fee_exp_category;
                    categories.forEach((category, i) => {
                        const date = new Date(attributes.fee_exp_date[i].value);
                        console.log(date);
                        if (categoriesData[category.displayValue] !== undefined) {
                            categoriesData[category.displayValue].data[date.getMonth()] += attributes.fee_exp_tax[i].value;
                        } else {
                            const serieData = new Array(12);
                            serieData.fill(0);
                            serieData[date.getMonth()] = attributes.fee_exp_tax[i].value;
                            categoriesData[category.displayValue] = {
                                data: serieData,
                            };
                        }
                    });
                });
                return categoriesData;
            }
        };
    },

    computed: {
      categoriesArray() {
          if (!this.formattedData) {
              return [];
          }

          return Object.keys(this.formattedData).map((key) => {
              return {
                  name: key,
                  amount: this.formattedData[key].data.reduce((acc, currentValue) => {
                      return acc + currentValue;
                  }, 0),
              };
          });
      }
    },

    mounted() {
        this.chart = Highcharts.chart(this.$refs.chart, {
            chart: {
                type: 'column',
            },
            title: {
                text: 'Évolution des dépenses',
            },
            xAxis: {
                categories: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                title: {
                    text: "Mois de l'année",
                },
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Dépenses (en €)',
                },
            },
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} €</b> ({point.percentage:.0f}%)<br/>',
                shared: true,
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                },
            },
            series: [],
        });
        this.privateScope.sendGetRequest(`api/v1/sba/reporting/${this.year}`, {
            params: {
                fields: 'document.attributes.fee_exp_date, document.attributes.fee_exp_category, document.attributes.fee_exp_tax',
            },
        }, this.$refs.wrapper).then((response) => {
            this.formattedData = this.privateScope.formatData(response.data.data.documents);
            Object.keys(this.formattedData).forEach((key) => {
                this.chart.addSeries({
                    name: key,
                    data: this.formattedData[key].data,
                });
            });
        });
        this.$(window).resize(() => {
            if (this.chart) {
                this.chart.reflow();
            }
        });
    },
};
