/*global define, _super*/
define([
    'underscore',
    'widgets/attributes/text/wText'
], function (_) {
    'use strict';

    $.widget("dcp.dcpFile", $.dcp.dcpText, {

        options: {
            type: "file",
            labels: {
                dropFileHere: "Drop file here",
                placeHolder: "Click to upload file",
                tooltipLabel: "Choose file",
                downloadLabel: "Download file",
                recording: "Recording",
                transferring : "Transferring",
                kiloByte : "kB"
            }
        },

        _initDom: function () {
            var visibleInput;
            if (this.getMode() === "read") {
                var urlSep = '?';
                if (this.options.value.url) {
                    if (!this.options.renderOptions.htmlLink.url) {
                        if (this.options.value.url && this.options.renderOptions.downloadInline) {
                            urlSep = (this.options.value.url.indexOf('?') >= 0) ? "&" : "?";
                            this.options.value.url += urlSep + 'inline=yes';
                        }
                        this.options.renderOptions.htmlLink.url = this.options.value.url;

                        if (!this.options.renderOptions.htmlLink.title) {
                            this.options.renderOptions.htmlLink.title = this.options.value.displayValue;
                            this.options.renderOptions.htmlLink.title += ' ('+ (Math.round(this.options.value.size/1024)) + ' '+
                                this.options.labels.kiloByte+')';
                        }
                    }
                }
            }

            this._super();
            if (this.getMode() === "write") {
                visibleInput=this.element.find("input[type=text]");
                visibleInput.attr("title", this.options.labels.tooltipLabel);
                visibleInput.attr("placeholder", this.options.labels.placeHolder);
                this.element.find(".dcpAttribute__content__button--file").attr("title", this.options.labels.downloadLabel);

                visibleInput.tooltip({
                    trigger: "hover",
                    placement: "bottom"

                });

            }
        },

        _initEvent: function wFileInitEvent() {
            if (this.getMode() === "write") {
                this._initUploadEvent();
            }

            this._super();
        },

        _initChangeEvent: function wFileInitChangeEvent() {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },
        _initUploadEvent: function wFileInitUploadEvent() {
            var scope = this;
            var inputFile = this.element.find("input[type=file]");
            var inputText = this.element.find(".dcpAttribute__content");
            var fileUrl = this.options.value.url;

            if (fileUrl) {
                this.element.find(".dcpAttribute__content__button--file").on("click"+ this.eventNamespace, function (event) {
                    window.location.href = fileUrl + "&inline=no";
                });
            } else {
                this.element.find(".dcpAttribute__content__button--file").attr("disabled", "disabled");
            }
            this.element.on("dragenter" + this.eventNamespace, function (event) {
                inputText.val(scope.options.value.displayValue);
                event.stopPropagation();
                event.preventDefault();
            });
            this.element.on("dragover" + this.eventNamespace, function (event) {
                inputText.val(scope.options.labels.dropFileHere);
                event.stopPropagation();
                event.preventDefault();
                scope.element.addClass("dcpAttribute__content--dropzone");
            });
            this.element.on("dragleave" + this.eventNamespace, function (event) {
                inputText.val(scope.options.value.displayValue);
                event.stopPropagation();
                event.preventDefault();
                scope.element.removeClass("dcpAttribute__content--dropzone");
            });

            this.element.on("drop" + this.eventNamespace, function (event) {
                inputText.val(scope.options.value.displayValue);
                scope.element.removeClass("dcpAttribute__content--dropzone");
                event.stopPropagation();
                event.preventDefault();

                var dt = event.originalEvent.dataTransfer;
                var files = dt.files;
                if (files.length > 0) {
                    scope.uploadFile(files[0]);
                }
            });
            inputText.on("click" + this.eventNamespace, function () {
                inputFile.trigger("click");
            });
            inputFile.on("change" + this.eventNamespace, function (event) {
                if (this.files.length > 0) {
                    scope.uploadFile(this.files[0]);
                }
            });

        },

        /**
         * Condition before upload file
         * @returns {boolean}
         */
        uploadCondition: function wFileUploadCondition() {
            return true;
        },

        uploadFile: function wFileUploadFile(firstFile) {
            var inputText = this.element.find(".dcpAttribute__content");
            var fd = new FormData();
            var newFileName = firstFile.name;
            var originalText = inputText.val();
            var originalBgColor = inputText.css("background-color");
            var scope = this;

            if (!this.uploadCondition(firstFile)) {
                return;
            }

            fd.append('dcpFile', firstFile);

            inputText.addClass("dcpAttribute__content--transferring");
            var infoBgColor = inputText.css("background-color");
            $.ajax({
                type: 'POST',
                url: "api/v1/temporaryFiles/",
                processData: false,
                contentType: false,
                cache: false,
                data: fd,

                xhr: function wFileXhrAddProgress() {
                    var xhrobj = $.ajaxSettings.xhr();
                    if (xhrobj.upload) {
                        xhrobj.upload.addEventListener('progress', function (event) {
                            var percent = 0;
                            var position = event.loaded || event.position;
                            var total = event.total;
                            if (event.lengthComputable) {
                                percent = Math.ceil(position / total * 100);
                            }
                            if (percent >= 100) {
                                inputText.val(scope.options.labels.recording+' ' + newFileName);
                                inputText.removeClass("dcpAttribute__content--transferring");
                                inputText.addClass("dcpAttribute__content--recording progress-bar active progress-bar-striped");
                                inputText.css("background", "");
                                inputText.css("background-image", "");
                            } else {
                                inputText.addClass("dcpAttribute__content--transferring");
                                inputText.val(scope.options.labels.transferring+' '  + newFileName);
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



            }).done(function (data) {
                var dataFile = data.data.file;
                inputText.val(originalText);
                inputText.css("background", "");
                inputText.removeClass("progress-bar active progress-bar-striped dcpAttribute__content--transferring dcpAttribute__content--recording");
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

            }).fail(function (data) {

                inputText.val(originalText);
                inputText.removeClass("progress-bar active progress-bar-striped dcpAttribute__content--transferring dcpAttribute__content--recording");
                inputText.css("background", "");
                inputText.css("background-image", "url(" + scope.options.value.icon + ')');
                var result = JSON.parse(data.responseText);
                if (result) {
                    _.each(result.messages, function (errorMessage) {

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
            });

        },

        /**
         * Modify value to widget and send notification to the view
         * @param value
         */
        setValue: function wFileSetValue(value) {
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
        getLink: function getLink() {
            var link = this._super();
            if (this.options.value.url && (!link || !link.url)) {
                link.url = this.options.value.url;
            }

            return link;
        },

        getType: function () {
            return "file";
        }

    });

    return $.fn.dcpFile;
});