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
        var onSelectTab = function (event) {
            var index = $(event.item).index();
            var selectedMarker = markers[index];
            selectedMarker.openPopup();
            var position = selectedMarker.getLatLng();
            var date = $(_this).documentController('getValue', 'fee_exp_file_date')[index].displayValue;
            $.ajax({
                dataType: 'json',
                success: function (data) {
                    $('#fee-exp-file-address-value, #fee-exp-file-date-value').fadeOut(function () {
                        if (data && data.address) {
                            var address = data.address.building + ', ' + data.address.road + '<br/>' +
                                data.address.postcode + ' ' +  data.address.town + ', ' + data.address.country;
                            $('#fee-exp-file-address-value').html(address).fadeIn();
                        } else if (data.display_name) {
                            $('#fee-exp-file-address-value').text(data.display_name).fadeIn();
                        } else {
                            $('#fee-exp-file-address-value').text('Non renseigné').fadeIn();
                        }

                        if (date) {
                            $('#fee-exp-file-date-value').text(date).fadeIn(50);
                        }
                    });
                },

                error: function (xhr, status, error) {
                    console.error(status, error);
                },

                url: 'http://nominatim.openstreetmap.org/reverse?format=json&lat=' + position.lat + '&lon=' + position.lng,

            });
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
                L.tileLayer('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
                    attribution: 'Map data OpenStreetMap contributors',
                }).addTo(mymap);

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
            }
        }

        tabstrip.select(0);
    }
);
