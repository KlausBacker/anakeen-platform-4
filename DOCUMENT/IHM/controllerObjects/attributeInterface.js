/*global define*/
define([
    "underscore"
], function (_)
{
    'use strict';

    var AttributPrototype = function AttributePrototype()
    {
        this.id = this._attributeModel.id;
    };
    /**
     * Get the property of the current attribute
     *
     * @returns {*}
     */
    AttributPrototype.prototype.getProperties = function AttributeInterfaceGetProperties()
    {
        var properties = _.clone(this._attributeModel.attributes);
        return _.omit(properties, "isValueAttribute", "title", "options", "attributeValue");
    };

    /**
     * Get the data to build a widget
     *
     * @returns {*}
     */
    AttributPrototype.prototype.getWidgetData = function AttributeInterfaceGetWidgetData(index)
    {
        return _.clone(this._attributeModel.toData(index, true));
    };

    /**
     * Get the options of the current attribute
     * @returns {*}
     */
    AttributPrototype.prototype.getOptions = function AttributeInterfaceGetOptions()
    {
        var options = this._attributeModel.getOptions();
        return _.clone(options);
    };

    /**
     * Get an option of the current attribute
     *
     * @param name
     * @returns {*}
     */
    AttributPrototype.prototype.getOption = function AttributeInterfaceGetOption(name)
    {
        return this._attributeModel.getOption(name);
    };

    /**
     * Set an option of the current
     * @param name
     * @param value
     * @constructor
     */
    AttributPrototype.prototype.setOption = function AttributeInterfaceSetOption(name, value)
    {
        this.setOption(name, value);
    };

    AttributPrototype.prototype.getValue = function AttributeInterfaceGetValue(type)
    {
        if (_.isUndefined(type) || type === "current") {
            return this._attributeModel.get("attributeValue");
        }
        if (type === "previous") {
            return this._attributeModel.previous("attributeValue");
        }
        if (type === "initial") {
            return this._attributeModel._initialAttributeValue;
        }
        if (type === "all") {
            return {
                "current": this._attributeModel.get("attributeValue"),
                "previous": this._attributeModel.previous("attributeValue"),
                "initial": this._attributeModel._initialAttributeValue
            };
        }
        throw new Error("Unknown type of getValue (current, previous, initial, all");
    };

    AttributPrototype.prototype.setValue = function AttributeInterfaceSetValue(value, dryRun)
    {
        var index;
        var currentValue;
        if (this._attributeModel.get("multiple")) {
            if (_.isArray(value)) {
                _.each(value, function (singleValue)
                {
                    if (!_.isObject(singleValue) || _.isUndefined(singleValue.value)) {
                        throw new Error("Each values must be an object with at least value properties");
                    }
                });
            } else {
                if (!_.isObject(value) || _.isUndefined(value.value) || _.isUndefined(value.index) || value.index === null) {
                    throw new Error("Value must be an object with at least value and index properties");
                }


                index = parseInt(value.index);
                if (index < 0) {
                    throw new Error("Index value must be positive or null");
                }

                if (this._attributeModel.isDoubleMultiple()) {
                    if (!_.isArray(value.value)) {
                        throw new Error("Value must be an array for multiple in arrays");
                    }
                    value = value.value;
                } else {
                    value = _.defaults(value, {displayValue: value.value});
                }


                currentValue = this._attributeModel.get("attributeValue").slice();
                currentValue[index] = _.clone(value);
                value = currentValue;
            }

        } else {
            if (!_.isObject(value) || _.isUndefined(value.value)) {
                throw new Error("Value must be an object with at least value properties");
            }

            value = _.defaults(value, {displayValue: value.value});
        }
        if (!dryRun) {
            this._attributeModel.set("attributeValue", value);
        }
    };

    AttributPrototype.prototype.toJSON = function AttributeInterfacetoJSON()
    {
        return {
            "id": this._attributeModel.id,
            "properties": _.omit(this._attributeModel.attributes, "isValueAttribute", "title", "options", "attributeValue"),
            "options": this._attributeModel.getOptions()
        };
    };

    var AttributeInterface = function AttributeInterface(attributeModel)
    {
        this._attributeModel = attributeModel;
        AttributPrototype.call(this);
    };

    AttributeInterface.prototype = Object.create(AttributPrototype.prototype);
    AttributeInterface.prototype.constructor = AttributPrototype;

    return AttributeInterface;

});