/*global define*/
define(["underscore"], function require_constraintHandler(_)
{
    'use strict';

    return function ConstraintHandler()
    {
        var _messages = [];
        this.addConstraintMessage = function addConstraintMessage(message, index)
        {
            index = _.isNumber(index) ? index : -1;
            _messages.push({message: message, index: index});
        };
        this.getConstraintMessages = function getConstraintMessages()
        {
            return _messages.slice(0);
        };
        this.hasConstraintMessages = function hasConstraintMessages()
        {
            return _messages.length > 0;
        };
        this.deleteConstaintMessages = function deleteConstaintMessages()
        {
            _messages = [];
        };
    };

});