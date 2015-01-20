/*global define*/
define([
    "underscore"
], function (_) {
    'use strict';

    return function AttributeInterface(attributeModel) {
        var _attributeModel = attributeModel;

        this.id = _attributeModel.id;

        /**
         * Get the property of the current attribute
         *
         * @returns {*}
         */
        this.getProperties = function AttributeInterfaceGetProperties() {
            var properties = _.clone(_attributeModel.attributes);
            return _.omit(properties, "isValueAttribute", "title", "options", "attributeValue");
        };

        /**
         * Get the options of the current attribute
         * @returns {*}
         */
        this.getOptions = function AttributeInterfaceGetOptions() {
            var options = _attributeModel.getOptions();
            return _.clone(options);
        };

        /**
         * Get an option of the current attribute
         *
         * @param name
         * @returns {*}
         */
        this.getOption = function AttributeInterfaceGetOption(name) {
            return _attributeModel.getOption(name);
        };

        /**
         * Set an option of the current
         * @param name
         * @param value
         * @constructor
         */
        this.setOption = function AttributeInterfaceSetOption(name, value) {
            this.setOption(name, value);
        };

        this.getValue = function AttributeInterfaceGetValue(type) {
            if (_.isUndefined(type) || type === "current") {
                return _attributeModel.get("attributeValue");
            }
            if (type === "previous") {
                return _attributeModel.previous("attributeValue");
            }
            if (type === "initial") {
                return _attributeModel._initialAttributeValue;
            }
            if (type === "all") {
                return {
                    "current" : _attributeModel.get("attributeValue"),
                    "previous" : _attributeModel.previous("attributeValue"),
                    "initial" : _attributeModel._initialAttributeValue
                };
            }
            throw new Error("Unknown type of getValue (current, previous, initial, all");
        };

        this.setValue = function AttributeInterfaceSetValue(value) {
            if (!_.isObject(value) || _.isUndefined(value.value)) {
                throw new Error("Value must be an object with value and displayValue properties");
            }
            value = _.defaults(value, {value : "", displayValue : ""});
            _attributeModel.set("attributeValue", value);
        };

        this.toJSON = function AttributeInterfacetoJSON() {
            return {
                "id" : _attributeModel.id,
                "properties" : _.omit(_attributeModel.attributes, "isValueAttribute", "title", "options", "attributeValue"),
                "options" : _attributeModel.getOptions()
            };
        };

    };

});