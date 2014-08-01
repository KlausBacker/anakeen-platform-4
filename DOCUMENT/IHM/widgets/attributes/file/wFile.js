define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpFile", $.dcp.dcpText, {

        options: {
            id: "",
            type: "file"
        },

        _initDom: function () {
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
                    }
                }
            }
            this._super();
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
            this.element.find(".dcpAttribute__content__button--file").on("click", function (event) {
                inputFile.trigger("click");
            });

            this.element.on("dragenter", function (event) {
                inputText.val(scope.options.value.displayValue);
                event.stopPropagation();
                event.preventDefault();
            });
            this.element.on("dragover", function (event) {
                inputText.val("Drop file here");
                event.stopPropagation();
                event.preventDefault();
                scope.element.addClass("dcpAttribute__content--dropzone");
            });
            this.element.on("dragleave", function (event) {
                inputText.val(scope.options.value.displayValue);
                event.stopPropagation();
                event.preventDefault();
                scope.element.removeClass("dcpAttribute__content--dropzone");
            });

            this.element.on("drop", function (event) {
                inputText.val(scope.options.value.displayValue);
                scope.element.removeClass("dcpAttribute__content--dropzone");
                event.stopPropagation();
                event.preventDefault();
                console.log("DROP", event);

                var dt = event.originalEvent.dataTransfer;
                var files = dt.files;
                if (files.length > 0) {
                    scope.uploadFile(files[0]);
                }
            });
            inputText.on("click", function () {
                inputFile.trigger("click");
            });
            inputFile.on("change", function (event) {
                console.log("change", event);
                console.log("change file", this.files);
                if (this.files.length > 0) {
                    scope.uploadFile(this.files[0]);
                }
            });

        },


        uploadFile: function wFileUploadFile(firstFile) {
            var inputText = this.element.find(".dcpAttribute__content");
            var fd = new FormData();
            var newFileName = firstFile.name;
            var originalText = inputText.val();
            var originalBgColor = inputText.css("background-color");
            var scope = this;
            fd.append('dcpFile', firstFile);

            inputText.addClass("dcpAttribute__content--transferring");
            var infoBgColor = inputText.css("background-color");
            console.log("color", originalBgColor, infoBgColor);
            $.ajax({
                type: 'POST',
                url: "api/v1/files/",
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
                                inputText.val('Recording ' + newFileName);
                                inputText.removeClass("dcpAttribute__content--transferring");
                                inputText.addClass("dcpAttribute__content--recording progress-bar active progress-bar-striped");
                                inputText.css("background", "");
                                inputText.css("background-image", "");
                            } else {
                                inputText.addClass("dcpAttribute__content--transferring");
                                inputText.val('Transferring ' + newFileName);
                                inputText.css("background-color", "red");
                                inputText.css("background", "linear-gradient(to right," +
                                    infoBgColor + " 0%," +
                                    infoBgColor + " " + percent + "%," +
                                    originalBgColor + (percent + 5) + "%," +
                                    originalBgColor + " 100%) ");
                                console.log('progress(3)', percent);
                            }
                        }, false);
                    }
                    return xhrobj;
                }



            }).done(function (data) {
                var dataFile = data.data.file;
                console.log("upload", data);
                inputText.val(originalText);
                inputText.css("background", "");
                inputText.removeClass("progress-bar active progress-bar-striped dcpAttribute__content--transferring dcpAttribute__content--recording");
                scope.setValue({
                    value: dataFile.reference,
                    size: dataFile.size,
                    fileName: dataFile.fileName,
                    displayValue: dataFile.fileName,
                    creationDate: dataFile.cdate,
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

            console.log("set file value", value);
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
            if (!link || !link.url) {
                link.url = this.options.value.url;
            }

            return link;
        },


        getType: function () {
            return "file";
        }

    });
});