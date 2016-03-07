/*global define*/
define([
    "underscore"
], function menuInterface(_)
{
    'use strict';

    var MenuError = function MenuError(message) {
        this.name = 'MenuError';
        this.message = message || 'Default Message';
        this.stack = (new Error()).stack;
    };
    MenuError.prototype = Object.create(Error.prototype);
    MenuError.prototype.constructor = MenuError;

    var MenuPrototope = function AttributePrototype()
    {
        if (this._menuModel) {
            this.id = this._menuModel.id;
        } else {
            this.id = null;
        }
    };
    MenuPrototope.prototype._menuModel=null;
    MenuPrototope.prototype._set = function MenuPrototope_set(key, value, options)
    {
        if (options && options.strict === true && !this.id) {
            throw new MenuError("This menu doesn't exist");
        }
        if (this.id || (options && options.strict === true)) {
            this._menuModel.set(key, value, options);
        }
    };
    /**
     * Get the property of the current attribute
     *
     * @returns {*}
     */
    MenuPrototope.prototype.getProperties = function menuInterfaceGetProperties(options)
    {
        if (options && options.strict === true && !this.id) {
            throw new MenuError("This menu doesn't exist");
        }
        if (this.id || (options && options.strict === true)) {
            return _.clone(this._menuModel.attributes);
        }
        return {};
    };

    /**
     * Disable an item menu
     *
     * @returns {*}
     */
    MenuPrototope.prototype.disable = function menuInterfaceDisable(options)
    {
        this._set("visibility", "disabled", options);
    };
    /**
     * Enable and show an item menu
     *
     * @returns {*}
     */
    MenuPrototope.prototype.enable = function menuInterfaceEnable(options)
    {
        this._set("visibility", "visible", options);
    };

    /**
     * Hide an item menu
     *
     * @returns {*}
     */
    MenuPrototope.prototype.hide = function menuInterfaceHide(options)
    {
        this._set("visibility", "hidden", options);
    };

    /**
     * Change text label
     * @param label raw text
     *
     * @returns {*}
     * @param options
     */
    MenuPrototope.prototype.setLabel = function menuInterfaceSetLabel(label, options)
    {
        this._set("label", label, options);
    };

    /**
     * Change html label
     * @param label html fragment
     *
     * @returns {*}
     * @param options
     */
    MenuPrototope.prototype.setHtmlLabel = function menuInterfaceSetHtmlLabel(label, options)
    {
        this._set("htmlLabel", label, options);
    };

    /**
     * Change icon url
     * @param url image url
     *
     * @returns {*}
     * @param options
     */
    MenuPrototope.prototype.setIconUrl = function menuInterfaceSetIconUrl(url, options)
    {
        this._set("iconUrl", url, options);
    };

    /**
     * Change url
     * @param url
     *
     * @returns {*}
     * @param options
     */
    MenuPrototope.prototype.setUrl = function menuInterfaceSetUrl(url, options)
    {
        this._set("url", url, options);
    };

    MenuPrototope.prototype.redraw = function menuInterfaceRedraw()
    {
        this._menuModel.trigger("reload");
    };

    var MenuInterface = function menuInterface(attributeModel)
    {
        this._menuModel = attributeModel;
        MenuPrototope.call(this);
    };

    MenuInterface.prototype = Object.create(MenuPrototope.prototype);
    MenuInterface.prototype.constructor = MenuPrototope;

    return MenuInterface;

});