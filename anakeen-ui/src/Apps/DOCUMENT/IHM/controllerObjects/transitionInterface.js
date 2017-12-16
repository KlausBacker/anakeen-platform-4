/*global define*/
define([
    "underscore"
], function require_transitionInterface(_)
{
    'use strict';

    var TransitionInterfacePrototype = function TransitionInterfacePrototype()
    {

    };

    TransitionInterfacePrototype.prototype.getValues = function TransitionInterfacePrototype_getValues() {
        if (!this._TransitionModel) {
            return null;
        }
        return this._TransitionModel.toJSON();
    };

    TransitionInterfacePrototype.prototype.hide = function TransitionInterfacePrototype_hide()
    {
        if (!this._TransitionModel) {
            return null;
        }
        return this._TransitionModel.trigger("hide");
    };

    TransitionInterfacePrototype.prototype.show = function TransitionInterfacePrototype_show()
    {
        if (!this._TransitionModel) {
            return null;
        }
        return this._TransitionModel.trigger("show");
    };

    TransitionInterfacePrototype.prototype.close = function TransitionInterfacePrototype_close()
    {
        if (!this._TransitionModel) {
            return null;
        }
        return this._TransitionModel.trigger("close");
    };

    var TransitionInterface = function TransitionInterface(transitionModel, $el, nextState, transition)
    {
        this._TransitionModel = transitionModel;
        this.$el = $el;
        this.nextState = nextState;
        this.transition = transition;
        TransitionInterfacePrototype.call(this);
    };

    TransitionInterface.prototype = Object.create(TransitionInterfacePrototype.prototype);
    TransitionInterface.prototype.constructor = TransitionInterfacePrototype;

    return TransitionInterface;

});