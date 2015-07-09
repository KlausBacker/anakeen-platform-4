/*global define*/
define([
    'underscore',
    "mustache",
    'dcpDocument/widgets/attributes/text/wText'
], function (_, Mustache)
{
    'use strict';

    $.widget("dcp.dcpFile", $.dcp.dcpText, {

        options: {
            type: "file",
            renderOptions: {
                downloadInline: false,
                htmlLink: {}
            },
            labels: {
                dropFileHere: "Drop file here",
                placeHolder: "Click to upload file",
                tooltipLabel: "Choose file",
                downloadLabel: "Download file",
                recording: "Recording",
                transferring: "Transferring",
                kiloByte: "kB",
                byte: "B"
            }
        },

        _initDom: function ()
        {
            var visibleInput;
            if (this.getMode() === "read") {
                var urlSep = '?';
                if (this.options.attributeValue.url) {
                    if (!this.options.renderOptions.htmlLink.url) {
                        if (this.options.attributeValue.url && this.options.renderOptions.downloadInline) {
                            urlSep = (this.options.attributeValue.url.indexOf('?') >= 0) ? "&" : "?";
                            this.options.attributeValue.url = this.options.attributeValue.url.replace('&inline=no', '');
                            this.options.attributeValue.url += urlSep + 'inline=yes';
                        }
                        this.options.renderOptions.htmlLink.url = this.options.attributeValue.url;

                        if (!this.options.renderOptions.htmlLink.title) {
                            this.options.renderOptions.htmlLink.title = this.options.attributeValue.displayValue;
                            if (this.options.attributeValue.size >= 1024) {
                                this.options.renderOptions.htmlLink.title += ' (' + (Math.round(this.options.attributeValue.size / 1024)) + ' ' +
                                this.options.labels.kiloByte + ')';
                            } else {
                                this.options.renderOptions.htmlLink.title += ' (' + this.options.attributeValue.size + ' ' +
                                this.options.labels.byte + ')';
                            }
                        }
                    }
                }
            }

            this._super();
            if (this.getMode() === "write") {
                visibleInput = this.element.find("input[type=text]");
                visibleInput.attr("title", this.options.labels.tooltipLabel);
                visibleInput.attr("placeholder", this.options.labels.placeHolder);
                this.element.find(".dcpAttribute__content__button--file").attr("title", this.options.labels.downloadLabel);

                visibleInput.tooltip({
                    trigger: "hover",
                    placement: "bottom",
                    container: "body"
                });

            }
        },

        _initEvent: function wFileInitEvent()
        {
            var scope=this;
            if (this.getMode() === "write") {
                this._initUploadEvent();
            }

            // Add trigger when try to download file
            this.element.on("click." + this.eventNamespace, '.dcpAttribute__content__link', function (event)
                {
                    scope._trigger("downloadfile", event, {
                        target: event.currentTarget,
                        index:scope._getIndex()
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
            var scope = this;
            var inputFile = this.element.find("input[type=file]");
            var inputText = this.element.find(".dcpAttribute__value");
            var fileUrl = this.options.attributeValue.url;

            if (fileUrl) {
                this.element.on("click" + this.eventNamespace, ".dcpAttribute__content__button--file", function wFileOnButtonClickr(event)
                {
                    scope._trigger("downloadfile", event, {
                        target: event.currentTarget,
                        index:scope._getIndex()
                    });
                    if (!event.isDefaultPrevented()) {
                        window.location.href = fileUrl + "&inline=no";
                    }
                });
            } else {
                this.element.find(".dcpAttribute__content__button--file").attr("disabled", "disabled");
            }

            if (!_.isUndefined(window.FormData)) {
                this.element.on("dragenter" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnDragEnter(event)
                {
                    inputText.val(scope.options.attributeValue.displayValue);
                    event.stopPropagation();
                    event.preventDefault();
                });
                this.element.on("dragover" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnDragOver(event)
                {
                    inputText.val(scope.options.labels.dropFileHere);
                    event.stopPropagation();
                    event.preventDefault();
                    scope.element.addClass("dcpAttribute__value--dropzone");
                });
                this.element.on("dragleave" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnLeave(event)
                {
                    inputText.val(scope.options.attributeValue.displayValue);
                    event.stopPropagation();
                    event.preventDefault();
                    scope.element.removeClass("dcpAttribute__value--dropzone");
                });

                this.element.on("drop" + this.eventNamespace, ".dcpAttribute__dragTarget", function wFileOnDrop(event)
                {
                    inputText.val(scope.options.attributeValue.displayValue);
                    scope.element.removeClass("dcpAttribute__value--dropzone");
                    event.stopPropagation();
                    event.preventDefault();

                    var dt = event.originalEvent.dataTransfer;
                    var files = dt.files;
                    if (files.length > 0) {
                        scope.uploadFile(files[0]);
                    }
                });

                this.element.on("click" + this.eventNamespace, ".dcpAttribute__value", function wFileOnClick()
                {
                    inputFile.trigger("click");
                });

                this.element.on("change" + this.eventNamespace, "input[type=file]", function (event)
                {
                    if (this.files && this.files.length > 0) {
                        scope.uploadFile(this.files[0]);
                    }
                });

            } else {
                this.addOldBrowserForm();

                this.element.on("change" + this.eventNamespace, "input[type=file]", function (event)
                {
                    scope.uploadFileForm();
                });

            }


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
            $(Mustache.render(formHtml, {id: inputId, target: fileTarget})).insertAfter(inputFile);

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
                container: "body"
            });
            this.element.find("iframe").on("load", function ()
            {
                if ($(this).contents().find("textarea").length === 0) {
                    // fake load when insert iframe
                    return;
                }
                var response = JSON.parse($(this).contents().find("textarea").val());

                if (response.exceptionMessage) {
                    _.each(response.messages, function (errorMessage)
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
                scope.setVisibilitySavingMenu("visible");
            });

        },

        /**
         * Condition before upload file
         * @returns {boolean}
         */
        uploadCondition: function wFileUploadCondition()
        {
            return true;
        },

        uploadFileForm: function wFileUploadFileForm()
        {
            var inputText = this.element.find(".dcpAttribute__value");

            var inputFile = this.element.find("input[type=file]");
            this.setVisibilitySavingMenu("disabled");

            inputText.addClass("dcpAttribute__value--transferring");
            inputText.val(this.options.labels.transferring + ' ' + inputFile.val().split(/[\\/]/).pop());
            this.element.find("form").submit();
        },


        uploadFile: function wFileUploadFile(firstFile)
        {
            var inputText = this.element.find(".dcpAttribute__value");
            var fd = new FormData();
            var newFileName = firstFile.name;
            var originalText = inputText.val();
            var originalBgColor = inputText.css("background-color");
            var scope = this;

            if (!this.uploadCondition(firstFile)) {
                return;
            }


            this.setVisibilitySavingMenu("disabled");
            fd.append('dcpFile', firstFile);

            inputText.addClass("dcpAttribute__value--transferring");
            var infoBgColor = inputText.css("background-color");
            $.ajax({
                type: 'POST',
                url: "api/v1/temporaryFiles/",
                processData: false,
                contentType: false,
                cache: false,
                data: fd,

                xhr: function wFileXhrAddProgress()
                {
                    var xhrobj = $.ajaxSettings.xhr();
                    if (xhrobj.upload) {
                        xhrobj.upload.addEventListener('progress', function (event)
                        {
                            var percent = 0;
                            var position = event.loaded || event.position;
                            var total = event.total;
                            if (event.lengthComputable) {
                                percent = Math.ceil(position / total * 100);
                            }
                            if (percent >= 100) {
                                inputText.val(scope.options.labels.recording + ' ' + newFileName);
                                inputText.removeClass("dcpAttribute__value--transferring");
                                inputText.addClass("dcpAttribute__value--recording progress-bar active progress-bar-striped");
                                inputText.css("background", "");
                                inputText.css("background-image", "");
                            } else {
                                inputText.addClass("dcpAttribute__value--transferring");
                                inputText.val(scope.options.labels.transferring + ' ' + newFileName);
                                inputText.css("background-color", "red");
                                inputText.css("background", "linear-gradient(to right," +
                                infoBgColor + " 0%," +
                                infoBgColor + " " + percent + "%," +
                                originalBgColor + (percent + 1) + "%," +
                                originalBgColor + " 100%) ");
                            }
                        }, false);
                    }
                    return xhrobj;
                }


            }).done(function (data)
            {
                var dataFile = data.data.file;
                inputText.val(originalText);
                inputText.css("background", "");
                inputText.removeClass("progress-bar active progress-bar-striped dcpAttribute__value--transferring dcpAttribute__value--recording");
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


                scope.setVisibilitySavingMenu("visible");

            }).fail(function (data)
            {

                inputText.val(originalText);
                inputText.removeClass("progress-bar active progress-bar-striped dcpAttribute__value--transferring dcpAttribute__value--recording");
                inputText.css("background", "");
                inputText.css("background-image", "url(" + scope.options.attributeValue.icon + ')');
                var result = JSON.parse(data.responseText);
                if (result) {
                    _.each(result.messages, function (errorMessage)
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


                scope.setVisibilitySavingMenu("visible");
            });

        },

        /**
         * Modify value to widget and send notification to the view
         * @param value
         */
        setValue: function wFileSetValue(value)
        {
            // call wAttribute:::setValue() :send notification
            this._super(value);

            if (this.getMode() === "write") {
                this.redraw();
            }
        },

        /**
         * Return the url of common link
         * @returns {*}
         */
        getLink: function getLink()
        {
            var link = this._super();
            if (this.options.attributeValue.url && (!link || !link.url)) {
                link.url = this.options.attributeValue.url;
            }

            return link;
        },

        getType: function ()
        {
            return "file";
        }

    });

    return $.fn.dcpFile;
});