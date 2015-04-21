/*global define, _super*/
define([
    'underscore',
    'jquery',
    'ckeditor-jquery',
    'dcpDocument/widgets/attributes/text/wText'
], function (_, $)
{
    'use strict';

    $.widget("dcp.dcpHtmltext", $.dcp.dcpText, {

        options: {
            type: "htmltext",
            renderOptions: {
                toolbar: 'Basic',
                height: '100px',
                toolbarStartupExpanded: true,
                ckEditorConfiguration: {}
            },
            locale: "en"
        },

        ckEditorInstance: null,

        _initDom: function wHtmlTextInitDom()
        {
            this._super();
            try {
                if (this.getMode() === "write") {
                    var options = _.extend(this.ckOptions(), this.options.renderOptions.ckEditorConfiguration);
                    this.ckEditorInstance = this.getContentElements().ckeditor(
                        options
                    ).editor;
                    this.options.attributeValue.value = this.ckEditorInstance.getData();
                }
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
        },

        /**
         * Define option set for ckEditor widget
         * @returns {{language: string, contentsCss: string[], removePlugins: string, toolbarCanCollapse: boolean, entities: boolean, filebrowserImageBrowseUrl: string, filebrowserImageUploadUrl: string, toolbar_Full: *[], toolbar_Default: *[], toolbar_Simple: *[], toolbar_Basic: *[], removeButtons: string}}
         */
        ckOptions: function wHtmlTextCkOptions()
        {
            var locale = this.options.locale;
            if (this.options.renderOptions.toolbar) {
                this.options.renderOptions.ckEditorConfiguration.toolbar = this.options.renderOptions.toolbar;
            }
            if (this.options.renderOptions.height) {
                this.options.renderOptions.ckEditorConfiguration.height = this.options.renderOptions.height;
            }
            if (!_.isUndefined(this.options.renderOptions.toolbarStartupExpanded)) {
                this.options.renderOptions.ckEditorConfiguration.toolbarStartupExpanded = this.options.renderOptions.toolbarStartupExpanded;
            }
            return {
                language: locale.substring(0, 2),
                contentsCss: ['lib/ckeditor/4/contents.css', 'css/dcp/document/ckeditor.css'],
                removePlugins: 'elementspath', // no see HTML path elements
                //extraPlugins: 'sourcedialog',
                toolbarCanCollapse: true,
                entities: false, // no use HTML entities
                filebrowserImageBrowseUrl: '?sole=Y&app=FDL&action=CKIMAGE',
                filebrowserImageUploadUrl: '?sole=Y&app=FDL&action=CKUPLOAD',
                toolbar_Full: [
                    {
                        name: 'document',
                        items: ['Sourcedialog', '-', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates']
                    },
                    {
                        name: 'clipboard',
                        items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
                    },
                    {name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-']},
                    {
                        name: 'forms',
                        items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton',
                            'HiddenField']
                    },
                    '/',
                    {
                        name: 'basicstyles',
                        items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                    },
                    {
                        name: 'paragraph',
                        items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
                            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
                    },
                    {name: 'links', items: ['Link', 'Unlink']},
                    {
                        name: 'insert',
                        items: ['Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe']
                    },
                    '/',
                    {name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
                    {name: 'colors', items: ['TextColor', 'BGColor']},
                    {name: 'tools', items: ['Maximize', 'ShowBlocks', '-', 'About']}
                ],
                toolbar_Default: [
                    {name: 'document', items: ['Sourcedialog']},
                    {
                        name: 'clipboard',
                        items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
                    },
                    {name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll']},
                    {
                        name: 'basicstyles',
                        items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                    },
                    {
                        name: 'paragraph',
                        items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
                            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
                    },
                    {name: 'links', items: ['Link', 'Unlink']},
                    {name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe']},
                    {name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
                    {name: 'colors', items: ['TextColor', 'BGColor']},
                    {name: 'tools', items: ['Maximize', 'ShowBlocks', '-', 'About']}
                ],
                toolbar_Simple: [
                    {name: 'document', items: []},
                    {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']},
                    {
                        name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-',
                        '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                    },
                    {name: 'links', items: ['Link', 'Unlink']},
                    {name: 'insert', items: ['Image', 'Table', 'SpecialChar']},
                    {name: 'styles', items: ['Format', 'FontSize']},
                    {name: 'colors', items: ['TextColor', 'BGColor']},
                    {name: 'tools', items: ['Maximize', 'Sourcedialog', '-', 'About']}
                ],
                toolbar_Basic: [
                    {
                        name: 'links',
                        items: ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'quicksave', 'About']
                    }
                ],
                removeButtons: ""
            };
        },

        _initEvent: function _initEvent()
        {
            var currentWidget = this;
            this._super();
            if (this.ckEditorInstance) {
                this.ckEditorInstance.on("change", function ()
                {
                    currentWidget.setValue({value: this.getData()});
                });

                this.ckEditorInstance.on("focus", function ()
                {
                    var ktTarget = currentWidget.element.find(".input-group");
                    currentWidget.showInputTooltip(ktTarget);
                    currentWidget.element.find(".cke").addClass("k-state-focused");
                });

                this.ckEditorInstance.on("blur", function ()
                {
                    var ktTarget = currentWidget.element.find(".input-group");
                    currentWidget.hideInputTooltip(ktTarget);
                    currentWidget.element.find(".cke").removeClass("k-state-focused");
                });

                this.ckEditorInstance.on("loaded", function ()
                {
                    currentWidget._trigger("widgetReady");
                });

                this.element.on("postMoved" + this.eventNamespace, function wHtmlTextOnPostMoved(event, eventData)
                {
                    if (eventData && (eventData.to === currentWidget.options.index )) {
                        currentWidget.redraw();
                    }
                });
            }
        },
        /**
         * Define inputs for focus
         * @protected
         */
        _getFocusInput: function ()
        {
            return this.element;
        },
        /**
         * No use parent change
         */
        _initChangeEvent: function _initChangeEvent()
        {

        },

        getWidgetValue: function ()
        {
            return this.getContentElements().val();
        },

        /**
         * Change the value of the widget
         * @param value
         */
        setValue: function wHtmltextSetValue(value)
        {
            value = _.clone(value);
            if (value.value === null) {
                // ckEditor restore original value if set to null
                value.value = '';
            }
            if (_.has(value, "value") && !_.has(value, "displayValue")) {
                value.displayValue = value.value;
            }
            if (this.getMode() === "write") {
                // Flash element only
                var originalValue = this.ckEditorInstance.getData();
                // : explicit lazy equal

                //noinspection JSHint
                if (originalValue.trim() != value.value.trim()) {

                    // Modify value only if different
                    this.getContentElements().val(value.value);
                    // this.ckEditorInstance.setData(value.value);
                    this.flashElement(this.element.find('iframe'));
                }
            } else
                if (this.getMode() === "read") {
                    this.getContentElements().html(value.displayValue);
                } else {
                    throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
                }

            // call wAttribute::setValue()
            $.dcp.dcpAttribute.prototype.setValue.call(this, value);
        },

        getType: function ()
        {
            return "htmltext";
        },

        _destroy: function wHtmlTextDestroy()
        {
            var currentWidget = this;
            if (this.ckEditorInstance && this.ckEditorInstance.destroy) {
                if (this.ckEditorInstance.status === "loaded" || this.ckEditorInstance.status === "ready") {
                    this.ckEditorInstance.destroy();
                    _.defer(function ()
                    {
                        currentWidget._destroy();
                    });
                    return;
                } else
                    if (this.ckEditorInstance.status === "unloaded") {
                        this.ckEditorInstance.on("loaded", function ()
                        {
                            currentWidget._destroy();
                        });
                        return;
                    }
            }
            this._super();
        },

        /**
         * Trigger a ready event when widget is render
         */
        _triggerReady: function wAttributeReady()
        {
            if (this.getMode() !== "write") {
                this._super();
            }
        }

    });

    return $.fn.dcpHtmltext;
});