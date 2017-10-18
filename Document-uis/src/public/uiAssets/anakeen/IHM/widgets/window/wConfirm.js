define([
    'jquery',
    'underscore',
    'mustache',
    'dcpDocument/widgets/window/wWindow'
], function wConfirm($, _, Mustache) {
    'use strict';

    $.widget("dcp.dcpConfirm", $.dcp.dcpWindow, {

        options: {
            modal: true,
            templateData: {
                templates: {
                    window: {
                        confirm:'<div class="confirm--body"> <div class="confirm--content">  <div>{{messages.textMessage}}</div><div>{{{messages.htmlMessage}}}</div> </div> <div class="confirm--buttons"> <button class="button--cancel" type="button">{{messages.cancelMessage}}</button> <button class="button--ok k-primary" type="button">{{messages.okMessage}}</button> </div> </div>'
                    }
                }
            },
            messages: {
                textMessage: 'Are you sure ?',
                htmlMessage: '',
                okMessage: "Ok",
                cancelMessage: "Cancel"
            },
            cancel: function wConfirmCancel() {
            },

            confirm: function wConfirmConfirm() {

            },
            actions: [
                "Close"
            ],
            height: "150px"

        },

        _create: function wConfirmCreate() {
            var scope = this;
            var scoppedCancel = _.bind(this.options.cancel, this);
            this.options.close = scoppedCancel;
            this.options.templateData.messages = this.options.messages || [];
            this.currentWidget = $(Mustache.render(this._getWindowTemplate('confirm') || "", this.options.templateData));
            this.element.append(this.currentWidget);
            this.currentWidget.kendoWindow(this.options);
            this.currentWidget.data("kendoWindow").center();

            this.currentWidget.find('.button--cancel').kendoButton({
                click: function wConfirmCancelClick() {
                    scoppedCancel();
                    scope.destroy();
                }
            });

            var scoppedConfirm = _.bind(this.options.confirm, this);

            this.currentWidget.find('.button--ok').kendoButton({
                click: function wConfirmOkClick() {
                    scoppedConfirm();
                    scope.destroy();
                }
            });

            this.element.data("dcpWindow", this);
        }

    });
});