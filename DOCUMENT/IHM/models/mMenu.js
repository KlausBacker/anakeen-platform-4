/*global define*/
define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    return Backbone.Model.extend({
        /**
         * Menu model are not linked to REST element so always new
         * @returns {boolean}
         */
        isNew: function mMenu_isNew()
        {
            return true;
        }
    });

});