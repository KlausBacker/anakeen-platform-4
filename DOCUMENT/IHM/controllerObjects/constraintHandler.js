/*global define*/
define([

], function () {
    'use strict';

    return function ConstraintHandler() {
        var _messages = [];
        this.addConstraintMessage = function addConstraintMessage(message, options) {
            _messages.push({message : message, options : options});
        };
        this.getConstraintMessages = function getConstraintMessages() {
            return _messages.slice(0);
        };
        this.hasConstraintMessages = function hasConstraintMessages() {
            return _messages.length > 0;
        };
        this.deleteConstaintMessages = function deleteConstaintMessages() {
            _messages = [];
        };
    };

});