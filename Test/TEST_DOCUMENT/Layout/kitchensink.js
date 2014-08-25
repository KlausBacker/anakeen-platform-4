window.loaders.unshift("mustache");
window.loaders.unshift("jquery");
window.loaders.unshift("underscore");

require(window.loaders, function(_, $, Mustache) {

    "use strict";

    var template = $("#wrapper").text(), keys;

    var getDiv = function(name) {
        var $currentDiv = $('<div data-type="'+name+'"></div>');
        $currentDiv.append(Mustache.render(template));
        $("body").append($currentDiv);
        $currentDiv.find(".labelWrapper").dcpLabel({label : name});
        return $currentDiv;
    };

    keys = _.keys(window.dcp.widgets);

    keys = _.sortBy(keys, function(key) {
        return key;
    });

    _.each(keys, function(name) {
        var widget, $currentDiv;
        if (name === "label") {
            return;
        }

        widget = window.dcp.widgets[name];

        $currentDiv = getDiv(name+" read");
        widget.call($currentDiv.find(".contentWrapper"));

        $currentDiv = getDiv(name + " write");
        widget.call($currentDiv.find(".contentWrapper"), {mode : "write"});
        if (name === "array") {
            return;
        }
        $currentDiv = getDiv(name + " write delete");
        widget.call($currentDiv.find(".contentWrapper"), {mode : "write", deleteButton : true});
    });

});