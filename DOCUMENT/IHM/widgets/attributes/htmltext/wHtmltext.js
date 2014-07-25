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

                this.options.value.value=this.ckEditorInstance.getData();
            }
        },

        _ckOptions: function () {
            var locale = this.options.locale;
            return   {
                language: locale.substring(0, 2),
                contentsCss: ['lib/ckeditor/contents.css', 'css/dcp/document/ckeditor.css'],
                removePlugins: 'elementspath', // no see HTML path elements
                toolbarCanCollapse: true,
                entities: false, // no use HTML entities
                filebrowserImageBrowseUrl:'?sole=Y&app=FDL&action=CKIMAGE',
                filebrowserImageUploadUrl:'?sole=Y&app=FDL&action=CKUPLOAD',
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
                    scope.setValue({value: this.getData()});
                });

                this.ckEditorInstance.on("focus", function () {
                    var ktTarget = scope.element.find(".input-group");
                    scope.showInputTooltip(ktTarget);
                    scope.element.find(".cke").addClass("k-state-focused");
                });

                this.ckEditorInstance.on("blur", function () {
                    var ktTarget = scope.element.find(".input-group");;
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
        /**
         * No use parent change
         * @private
         */
     _initChangeEvent: function _initChangeEvent() {

     },

        getWidgetValue: function () {
            return this.contentElements().val();
        },

        /**
         *
         * @param value
         */
        setValue: function wHtmltextSetValue(value) {
            if (value.value === null) {
                // ckEditor restore original value if set to null
                value.value = '';
            }
            //value.value=value.value.trim();
            if (this.getMode() === "write") {
                // Flash element only
                var originalValue = this.ckEditorInstance.getData();
                // : explicit lazy equal

                //noinspection JSHint
                if (originalValue.trim() != value.value.trim()) {
                    console.log("Flash", {original:originalValue, newV : value.value});

                    // Modify value only if different
                    this.contentElements().val(value.value);
                    // this.ckEditorInstance.setData(value.value);
                    this.flashElement(this.element.find('iframe'));
                }
            } else if (this.getMode() === "read") {
                contentElement.text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }

            // call wAttribute::setValue()
            $.dcp.dcpAttribute.prototype.setValue.apply(this, [value]);
        },
        getType: function () {
            return "htmltext";
        }

    });
});