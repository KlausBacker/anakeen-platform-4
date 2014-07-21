define([
    'underscore',
    'mustache',
    'ckeditor-jquery',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, ckEditor) {
    'use strict';

    $.widget("dcp.dcpHtmltext", $.dcp.dcpText, {

        options: {
            id: "",
            type: "htmltext"
        },

        ckEditorInstance: null,

        _initDom: function () {
            this._super();
            if (this.getMode() === "write") {


                var options = _.extend(this._ckOptions(), this.options.renderOptions);


                this.ckEditorInstance = this.contentElements().ckeditor(
                    options
                ).editor;
            }
        },


        _ckOptions: function () {
            var locale = this._documentModel().get('locale');
            return   {
                language: locale.substring(0, 2),
                contentsCss: ['lib/ckeditor/contents.css', 'css/dcp/document/ckeditor.css'],
                removePlugins: 'elementspath', // no see HTML path elements
                toolbarCanCollapse: true,
                toolbar_Full: [
                    { name: 'document', items: [ 'Source', '-', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates' ] },
                    { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
                    { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-' ] },
                    { name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton',
                        'HiddenField' ] },
                    '/',
                    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
                        '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
                    { name: 'links', items: [ 'Link', 'Unlink' ] },
                    { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
                    '/',
                    { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                    { name: 'tools', items: [ 'Maximize', 'ShowBlocks', '-', 'About' ] }
                ],
                toolbar_Default: [
                    { name: 'document', items: [  'Source'] },
                    { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
                    { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll' ] },
                    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
                        '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
                    { name: 'links', items: [ 'Link', 'Unlink' ] },
                    { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe' ] },
                    { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                    { name: 'tools', items: [ 'Maximize', 'ShowBlocks', '-', 'About' ] }
                ],
                toolbar_Simple: [
                    { name: 'document', items: [] },
                    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
                    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-',
                        '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                    { name: 'links', items: [ 'Link', 'Unlink' ] },
                    { name: 'insert', items: [ 'Image', 'Table', 'SpecialChar' ] },
                    { name: 'styles', items: [ 'Format', 'FontSize' ] },
                    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                    { name: 'tools', items: [ 'Maximize', 'Source', '-', 'About' ] }
                ],
                toolbar_Basic: [
                    { name: 'links', items: ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'quicksave', 'About'] }
                ],
                removeButtons: ""
            };
        },

        _initEvent: function _initEvent() {
            var scope = this;
            this._super();
            if (this.ckEditorInstance) {
                this.ckEditorInstance.on("change", function () {
                    scope._model().setValue({value: this.getData()}, scope._getIndex());
                });

                this.ckEditorInstance.on("focus", function () {
                    var ktTarget = scope.element;
                    scope.showInputTooltip(ktTarget);
                    scope.element.find(".cke").addClass("k-state-focused");
                });

                this.ckEditorInstance.on("blur", function () {
                    var ktTarget = scope.element;
                    scope.hideInputTooltip(ktTarget);
                    scope.element.find(".cke").removeClass("k-state-focused");
                });
            }
        },
        /**
         * Define inputs for focus
         * @protected
         */
        _focusInput: function () {
            return this.element;
        },


        setValue: function (value) {
            if (value.value === null) {
                // ckEditor restore original value if set to null
                value.value = '';
            }
            if (this.getMode() === "write") {
                // Flash element only
                var contentElement = this.contentElements();
                var originalValue = this.ckEditorInstance.getData();
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    // Modify value only if different
                    // this.ckEditorInstance.setData(value.value);
                    this.flashElement($('iframe'));
                }
            }

            this._super(value);
        },
        getType: function () {
            return "htmltext";
        }

    });
});