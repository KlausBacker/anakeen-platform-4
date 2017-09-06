/*global define*/
define([
    'underscore',
    'backbone'
], function mMenu(_, Backbone) {
    'use strict';

    return Backbone.Model.extend({
        typeModel:"ddui:menu",
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