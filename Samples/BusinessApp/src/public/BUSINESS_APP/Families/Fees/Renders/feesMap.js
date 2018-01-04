window.dcp.document.documentController(
    'addEventListener',
   'attributeReady',
    {
        name: 'BA_FEES::fee_fr_viz',
        documentCheck: function (documentObject) {
            return documentObject.family.name === 'BA_FEES';
        },

        attributeCheck: function (attribute) {
            if (attribute.id === 'fee_fr_viz') {
                return true;
            }
        },
    },
    function (event, documentObject, attribute, el) {
        var _this = this;
        var dates = $(this).documentController('getValue', 'fee_exp_file_stamp_date');
        var stampIds = $(this).documentController('getValue', 'fee_exp_file_stampid');
        var onSelectTab = function (event) {
            var index = $(event.item).index();
            var selectedMarker = markers[index];
            selectedMarker.openPopup();
            var date = new Date(dates[index].value);
            $('#fee-exp-date-value').text(date.toLocaleDateString());
            $('#fee-exp-time-value').text(date.toLocaleTimeString());
            var stampId = stampIds[index].value;
            if (stampId) {
                $('#fee-exp-file-value').prop('href', 'https://2d802663ae12cde:0f45595d-6779-4410-96e8-4266a5e942c@api-prod.stampery.com/stamps/' + stampId + '.pdf');
            }

        };

        var tabstrip = $('#fees-expenses-viztabstrip').kendoTabStrip({
            animation: {
                open: {
                    effects: 'fadeIn',
                },
            },
            select: onSelectTab,
            tabPosition: 'left',
        }).data('kendoTabStrip');

        var map = el.find('#feesMap');
        var mymap;
        if (map.length) {
            var latitudes = $(this).documentController('getValue', 'fee_exp_file_lat');
            var longitudes = $(this).documentController('getValue', 'fee_exp_file_lng');
            var positions = [];
            if (latitudes && longitudes && latitudes.length === longitudes.length) {
                for (var i = 0; i < latitudes.length; i++) {
                    positions.push([latitudes[i].value, longitudes[i].value]);
                }

                mymap = L.map(map[0]).fitBounds(positions);
                L.tileLayer('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png').addTo(mymap);

                var markers = [];
                var marker = null;
                for (var i = 0; i < positions.length; i++) {
                    marker = L.marker(positions[i]).addTo(mymap);
                    marker.bindPopup('Dépense ' + (i + 1), {
                        closeButton: false,
                        closeOnClick: false,
                    });
                    marker.on('click', (function (pos) {
                        return function () {
                            tabstrip.select(pos);
                        };
                    }(i)));

                    markers.push(marker);
                }

                $(map[0]).find('.leaflet-control-attribution.leaflet-control a').prop('target', '_blank');
            }
        }

        tabstrip.select(0);
    }
);
