/*global define*/
(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'underscore',
            'mustache',
            'dcpDocument/widgets/attributes/text/wText'
        ], factory);
    } else {
        //noinspection JSUnresolvedVariable
        factory(window.jQuery, window._, window.Mustache);
    }
}(window, function wFileWidget($, _, Mustache)
{
    'use strict';

    //noinspection JSUnusedLocalSymbols
    $.widget("dcp.dcpFile", $.dcp.dcpText, {

        uploadingFiles: 0, // file upload in progress
        options: {
            type: "file",
            renderOptions: {
                contentDisposition: false,
                htmlLink: {},
                placeHolder: "Click to upload file"
            },
            labels: {
                dropFileHere: "Drop file here",
                tooltipLabel: "Choose file",
                downloadLabel: "Download file",
                recording: "Recording",
                transferring: "Transferring",
                kiloByte: "kB",
                byte: "B"
            }
        },

        _initDom: function wFileInitDom()
        {
            var visibleInput;
            if (this.getMode() === "read") {
                var urlSep = '?';
                if (this.options.attributeValue.url) {
                    if (!this.options.renderOptions.htmlLink.url) {
                        if (this.options.attributeValue.url) {
                            urlSep = (this.options.attributeValue.url.indexOf('?') >= 0) ? "&" : "?";
                            if (this.options.renderOptions.contentDisposition === "inline") {
                                this.options.attributeValue.url = this.options.attributeValue.url.replace('&inline=no', '');
                                this.options.attributeValue.url += urlSep + 'inline=yes';
                            } else {
                                this.options.attributeValue.url = this.options.attributeValue.url.replace('&inline=yes', '');
                                this.options.attributeValue.url += urlSep + 'inline=no';
                            }
                        }
                        this.options.renderOptions.htmlLink.url = this.options.attributeValue.url;

                        if (!this.options.renderOptions.htmlLink.title) {
                            this.options.renderOptions.htmlLink.title = this.options.attributeValue.displayValue;
                            if (this.options.attributeValue.size >= 1024) {
                                this.options.renderOptions.htmlLink.title += '<br/>(' + (Math.round(this.options.attributeValue.size / 1024)) + ' ' +
                                    this.options.labels.kiloByte + ')';
                            } else {
                                this.options.renderOptions.htmlLink.title += '<br/>(' + this.options.attributeValue.size + ' ' +
                                    this.options.labels.byte + ')';
                            }
                        }
                    }

                }
            }
            if (this.options.renderOptions.mimeIconSize) {
                if (["0", "0x0", "x0"].indexOf(this.options.renderOptions.mimeIconSize) !== -1) {
                    this.options.attributeValue.icon = null;
                } else {
                    var reSize = /sizes\/([^\/]+)/;
                    this.options.attributeValue.icon = this.options.attributeValue.icon.replace(reSize, "sizes/" + this.options.renderOptions.mimeIconSize);
                }
            }
            this._super();
            if (this.getMode() === "write") {
                visibleInput = this.element.find("input[type=text]");
                visibleInput.attr("title", this.options.labels.tooltipLabel);
                visibleInput.attr("placeholder", this.options.renderOptions.placeHolder);
                this.element.find(".dcpAttribute__content__button--file").attr("title", this.options.labels.downloadLabel);

                visibleInput.tooltip({
                    trigger: "hover",
                    placement: "bottom",
                    container: ".dcpDocument"
                });
                this.element.find("input[type=file]").attr("fileValue", this.options.attributeValue.value || null);
            }
        },

        _initEvent: function wFileInitEvent()
        {
            var currentWidget = this;
            if (this.getMode() === "write") {
                this._initUploadEvent();
            }

            // Add trigger when try to download file
            this.element.on("click." + this.eventNamespace, '.dcpAttribute__content__link', function wFileClickDownload(event)
            {
                currentWidget._trigger("downloadfile", event, {
                    target: event.currentTarget,
                    index: currentWidget._getIndex()
                });
            });

            this._super();
        },

        _initChangeEvent: function wFileInitChangeEvent()
        {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        _initUploadEvent: function wFileInitUploadEvent()
        {
            var currentWidget = this;
            var inputFile = this.element.find("input[type=file]");
            var inputText = this.element.find(".dcpAttribute__value");
            var fileUrl = this.options.attributeValue.url;

            if (fileUrl) {
                this.element.on("click" + this.eventNamespace, ".dcpAttribute__content__button--file", function wFileOnButtonClickr(event)
                {
                    var isNotPrevented = currentWidget._trigger("downloadfile", event, {
                        target: event.currentTarget,
                        index: currentWidget._getIndex()
                    });
                    if (isNotPrevented) {
                        var url = fileUrl + "&inline=yes", $base = $("base");

                        if (isNotPrevented) {
                            if ($base.length > 0) {
                                // For IE : Not honor base href in this case
                                url = $base.attr("href") + url;
                            }
                            window.open(url);
                        }
                    }
                });
            } else {
                this.element.find(".dcpAttribute__content__button--file").attr("disabled", "disabled");
            }

            if (!_.isUndefined(window.FormData)) {
                this.element.on("dragenter" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnDragEnter(event)
                {
                    inputText.val(currentWidget.options.attributeValue.displayValue);
                    event.stopPropagation();
                    event.preventDefault();
                });
                this.element.on("dragover" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnDragOver(event)
                {
                    inputText.val(currentWidget.options.labels.dropFileHere);
                    event.stopPropagation();
                    event.preventDefault();
                    currentWidget.element.addClass("dcpAttribute__value--dropzone");
                });
                this.element.on("dragleave" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnLeave(event)
                {
                    inputText.val(currentWidget.options.attributeValue.displayValue);
                    event.stopPropagation();
                    event.preventDefault();
                    currentWidget.element.removeClass("dcpAttribute__value--dropzone");
                });

                this.element.on("drop" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnDrop(event)
                {
                    inputText.val(currentWidget.options.attributeValue.displayValue);
                    currentWidget.element.removeClass("dcpAttribute__value--dropzone");
                    event.stopPropagation();
                    event.preventDefault();

                    var dt = event.originalEvent.dataTransfer;
                    var files = dt.files;
                    if (files.length > 0) {
                        currentWidget.uploadFile(files[0]);
                    }
                });

                this.element.on("click" + this.eventNamespace, ".dcpAttribute__value", function wFileOnClick()
                {
                    inputFile.trigger("click");
                });

                this.element.on("keydown" + this.eventNamespace, ".dcpAttribute__value", function wFileFilterKeys(event)
                {
                    if (event.keyCode !== 9 && event.keyCode !== 32 && event.keyCode !== 13) {
                        event.preventDefault();
                    }
                });
                this.element.on("keyup" + this.eventNamespace, ".dcpAttribute__value", function wFileSelectKeys(event)
                {
                    if (event.keyCode !== 9) {
                        event.preventDefault();
                        if (event.keyCode === 32 || event.keyCode === 13) {
                            inputFile.trigger("click");
                        }
                    }
                });

                this.element.on("change" + this.eventNamespace, "input[type=file]", function wFileChange(/*event*/)
                {
                    if (this.files && this.files.length > 0) {
                        currentWidget.uploadFile(this.files[0]);
                    }
                });

            } else {
                this.addOldBrowserForm();

                this.element.on("change" + this.eventNamespace, "input[type=file]", function wFileChangeOld(/*event*/)
                {
                    currentWidget.uploadFileForm();
                });
            }
        },

        getWidgetValue: function wFilegetWidgetValue()
        {
            var $inputFile = this.element.find("input[type=file]");
            return $inputFile.attr("fileValue") || null;
        },

        /**
         * Add real form and iframe for browsers without FormData
         */
        addOldBrowserForm: function wFileAddOldBrowserForm()
        {
            var inputFile = this.element.find("input[type=file]");
            var inputText = this.element.find(".dcpAttribute__value");
            var inputId = inputFile.attr("id");
            var fileTarget = "fileupload" + inputId + '-' + this.options.index;
            var originalText = inputText.val();
            var scope = this;
            var formHtml = '<form id="form{{id}}" target="{{target}}" method="POST" enctype="multipart/form-data" action="api/v1/temporaryFiles/?alt=html">' +
                '</form><iframe style="display:none" name="{{target}}"></iframe>';
            $(Mustache.render(formHtml || "", {id: inputId, target: fileTarget})).insertAfter(inputFile);

            var container = '<div class="dcpFile__form"/>';

            $(container).insertAfter(inputText).append(inputText);

            this.element.find("form").append(inputFile).css("display", "inline");

            this.element.find(".dcpFile__form").append(this.element.find("form"));

            inputFile.show();
            this.element.addClass("dcpFile--old-browser");

            inputFile.attr("title", inputText.attr("data-original-title"));
            inputFile.tooltip({
                trigger: "hover",
                placement: "bottom",
                container: ".dcpDocument"
            });
            this.element.find("iframe").on("load", function wFileLoad()
            {
                if ($(this).contents().find("textarea").length === 0) {
                    // fake load when insert iframe
                    return;
                }
                var response = JSON.parse($(this).contents().find("textarea").val());

                if (response.exceptionMessage) {
                    _.each(response.messages, function wFileMessages(errorMessage)
                    {

                        $('body').trigger("notification", {
                            htmlMessage: errorMessage.contentHtml,
                            message: errorMessage.contentText,

                            type: errorMessage.type
                        });

                    });
                } else {
                    var dataFile = response.data.file;
                    scope.setValue({
                        value: dataFile.reference,
                        size: dataFile.size,
                        fileName: dataFile.fileName,
                        displayValue: dataFile.fileName,
                        creationDate: dataFile.cdate,
                        thumbnail: dataFile.thumbnailUrl,
                        url: dataFile.downloadUrl,
                        icon: dataFile.iconUrl
                    });
                }

                inputText.val(originalText);
                inputText.removeClass("dcpAttribute__value--transferring");
                scope._setVisibilitySavingMenu("visible");
            });

        },

        /**
         * Condition before upload file
         * @param file
         * @returns {boolean}
         */
        uploadCondition: function wFileUploadCondition(file)
        {
            return true;
        },

        uploadFileForm: function wFileUploadFileForm()
        {
            var inputText = this.element.find(".dcpAttribute__value");

            var inputFile = this.element.find("input[type=file]");
            this._setVisibilitySavingMenu("disabled");

            inputText.addClass("dcpAttribute__value--transferring");
            inputText.val(this.options.labels.transferring + ' ' + inputFile.val().split(/[\\/]/).pop());
            this.element.find("form").submit();
        },

        uploadFile: function wFileUploadFile(firstFile)
        {
            var inputText = this.element.find(".dcpAttribute__value");
            var formData = new FormData();
            var newFileName = firstFile.name;
            var originalText = inputText.val();
            var originalBgColor = inputText.css("background-color");
            var currentWidget = this;
            var event = {prevent: false};

            if (!this.uploadCondition(firstFile)) {
                return;
            }

            var isNotPrevented = currentWidget._trigger("uploadfile", event, {
                target: event.currentTarget,
                index: currentWidget._getIndex(),
                file: firstFile
            });
            if (!isNotPrevented) {
                return;
            }

            currentWidget.uploadingFiles++;
            this._setVisibilitySavingMenu("disabled");
            formData.append('dcpFile', firstFile);

            inputText.addClass("dcpAttribute__value--transferring");
            var infoBgColor = inputText.css("background-color");
            $.ajax({
                type: 'POST',
                url: "api/v1/temporaryFiles/",
                processData: false,
                contentType: false,
                cache: false,
                data: formData,

                xhr: function wFileXhrAddProgress()
                {
                    var xhrObject = $.ajaxSettings.xhr();
                    if (xhrObject.upload) {
                        xhrObject.upload.addEventListener('progress', function wFileProgress(event)
                        {
                            var percent = 0;
                            var position = event.loaded || event.position;
                            var total = event.total;
                            if (event.lengthComputable) {
                                percent = Math.ceil(position / total * 100);
                            }
                            if (percent >= 100) {
                                inputText.val(currentWidget.options.labels.recording + ' ' + newFileName);
                                inputText.removeClass("dcpAttribute__value--transferring");
                                inputText.addClass("dcpAttribute__value--recording progress-bar active progress-bar-striped");
                                inputText.css("background", "");
                                inputText.css("background-image", "");
                            } else {
                                inputText.addClass("dcpAttribute__value--transferring");
                                inputText.val(currentWidget.options.labels.transferring + ' ' + newFileName);
                                inputText.css("background-color", "red");
                                inputText.css("background", "linear-gradient(to right," +
                                    infoBgColor + " 0%," +
                                    infoBgColor + " " + percent + "%," +
                                    originalBgColor + (percent + 1) + "%," +
                                    originalBgColor + " 100%) ");
                            }
                        }, false);
                    }
                    return xhrObject;
                }

            }).done(function wFileUploadDone(data)
            {
                var dataFile = data.data.file;

                currentWidget.uploadingFiles--;

                currentWidget.setValue({
                    value: dataFile.reference,
                    size: dataFile.size,
                    fileName: dataFile.fileName,
                    displayValue: dataFile.fileName,
                    creationDate: dataFile.cdate,
                    thumbnail: dataFile.thumbnailUrl,
                    url: dataFile.downloadUrl,
                    icon: dataFile.iconUrl
                });

                currentWidget._setVisibilitySavingMenu("visible");

            }).fail(function wFileUploadFail(data)
            {
                currentWidget.uploadingFiles--;
                inputText.css("background-image", "url(" + currentWidget.options.attributeValue.icon + ')');
                var result = JSON.parse(data.responseText);
                if (result) {
                    _.each(result.messages, function wFileErrorMessages(errorMessage)
                    {

                        $('body').trigger("notification", {
                            htmlMessage: errorMessage.contentHtml,
                            message: errorMessage.contentText,

                            type: errorMessage.type
                        });

                    });
                } else {
                    inputText.css("background", "");
                    $('body').trigger("notification", {
                        htmlMessage: 'File <b>' + firstFile.name + '</b> cannot be uploaded',
                        message: event.statusText,
                        type: "error"
                    });
                }

                currentWidget._setVisibilitySavingMenu("visible");
            }).always(function wFileUploadEnd()
            {
                inputText.val(originalText);
                inputText.css("background", "");
                inputText.removeClass("progress-bar active progress-bar-striped dcpAttribute__value--transferring dcpAttribute__value--recording");
            });

        },

        /**
         * Modify value to widget and send notification to the view
         * @param value
         */
        setValue: function wFileSetValue(value)
        {
            var $inputFile = this.element.find("input[type=file]");
            // call wAttribute:::setValue() :send notification
            this._super(value);

            if (this.getMode() === "write" && this.uploadingFiles === 0) {
                this.redraw();
            }

            $inputFile.attr("fileValue", (value) ? value.value : null);
        },

        /**
         * Return the url of common link
         * @returns {*}
         */
        getLink: function wFileGetLink()
        {
            var link = this._super();
            if (this.options.attributeValue.url && (!link || !link.url)) {
                link.url = this.options.attributeValue.url;
            }

            return link;
        },

        getType: function wFileGetType()
        {
            return "file";
        }

    });

    return $.fn.dcpFile;
}));