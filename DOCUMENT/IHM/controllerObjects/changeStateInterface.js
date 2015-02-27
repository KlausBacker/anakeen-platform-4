/*global define*/
define([
    "underscore"
], function (_)
{
    'use strict';

    var ChangeStateInterfacePrototype = function ChangeStateInterfacePrototype()
    {

    };

    ChangeStateInterfacePrototype.prototype.getValues = function changeStateInterfacePrototype_getValues() {
        if (!this._changeStateModel) {
            return null;
        }
        return this._changeStateModel.toJSON();
    };

    ChangeStateInterfacePrototype.prototype.hide = function changeStateInterfacePrototype_hide()
    {
        if (!this._changeStateModel) {
            return null;
        }
        return this._changeStateModel.trigger("hide");
    };

    ChangeStateInterfacePrototype.prototype.show = function changeStateInterfacePrototype_show()
    {
        if (!this._changeStateModel) {
            return null;
        }
        return this._changeStateModel.trigger("show");
    };

    ChangeStateInterfacePrototype.prototype.close = function changeStateInterfacePrototype_close()
    {
        if (!this._changeStateModel) {
            return null;
        }
        return this._changeStateModel.trigger("close");
    };

    var ChangeStateInterface = function ChangeStateInterface(transitionModel, $el, nextState, transition)
    {
        this._changeStateModel = transitionModel;
        this.$el = $el;
        this.nextState = nextState;
        this.transition = transition;
        ChangeStateInterfacePrototype.call(this);
    };

    ChangeStateInterface.prototype = Object.create(ChangeStateInterfacePrototype.prototype);
    ChangeStateInterface.prototype.constructor = ChangeStateInterfacePrototype;

    return ChangeStateInterface;

});