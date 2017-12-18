import Highcharts from 'highcharts';

require('highcharts/modules/exporting')(Highcharts);

export default {
    data() {
        return {
            chart: null,
        };
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
                    text: 'Dépenses',
                },
            },
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
                shared: true,
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                },
            },
            series: [],
        });
        this.chart.series.push({
                name: 'Déplacement',
                data: [5, 3, 4, 7, 2, 1, 2, 6, 2, 4, 3, 4],
            }, {
                name: 'Nourriture',
                data: [2, 2, 3, 2, 1, 5, 3, 3, 1, 3, 5, 2],
            }, {
                name: 'Logement',
                data: [3, 4, 4, 2, 5, 3, 4, 1, 2, 3, 2, 4],
            }, {
                name: 'Péage',
                data: [3, 4, 4, 2, 5, 3, 4, 1, 2, 3, 2, 4],
            });
        this.$(window).resize(() => {
            if (this.chart) {
                this.chart.reflow();
            }
        });
    },
};
