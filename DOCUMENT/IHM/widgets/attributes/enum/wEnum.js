/*global define, _super*/
define([
    'underscore',
    'mustache',
    'widgets/attributes/text/wText',
    'kendo/kendo.dropdownlist'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpEnum", $.dcp.dcpText, {

        options: {
            type: "enum",
            editFormat: "list", // possible values are ["list', 'vertical', 'horizontal', 'bool']'
            canUseEmpty: true,
            canChooseOther: false,
            canAddNewItem: false,
            sourceValues : [] // [{value:234, displayValue: "one hundred thirty four"}, ...]
        },
        _initDom: function wEnumInitDom() {

            if (this.getMode() === "read") {
                if (this._isMultiple()) {
                    this.options.values = _.toArray(this.options.value);
                    this.options.isMultiple = true;
                } else {
                    this.options.values = [this.options.value];
                    this.options.isMultiple = false;
                }
                console.log("enum values", this.options);
                this._super();
            }

            if (this.getMode() === "write") {

                if (this._isMultiple()) {
                    console.log("not implemented enum multiple");
                } else {
                    this.singleDropdown(this.kendoWidget);
                }

            }
        },


        singleDropdown: function wEnumSingleDropdown() {
            var source=[];
            var scope=this;
            var oneSelected=false;
            var item;
            _.each(this.options.sourceValues, function (displayValue,rawValue) {
                item={};
                item.value=rawValue;
                item.displayValue=displayValue;
                console.log("cmp",rawValue,  scope.options.value.value);
                if (rawValue == scope.options.value.value) {
                   oneSelected=true;
                   item.selected=true;
                } else {
                   item.selected=false;
                }
                source.push(item);
            });

            if (! oneSelected) {
                source.push({value:this.options.value.value, displayValue:this.options.value.displayValue, selected:true});
            }
            this.options.sourceValues=source;
            console.log("soue",source );
            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
console.log("kendlist", this.kendoWidget);
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            this.kendoWidget.kendoDropDownList();
        },

        getType: function () {
            return "enum";
        }

    });

    return $.fn.dcpEnum;
});