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

    AttributPrototype.prototype.setValue = function AttributeInterfaceSetValue(value)
    {
        if (!_.isObject(value) || _.isUndefined(value.value)) {
            throw new Error("Value must be an object with value and displayValue properties");
        }
        value = _.defaults(value, {value: "", displayValue: ""});
        this._attributeModel.set("attributeValue", value);
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