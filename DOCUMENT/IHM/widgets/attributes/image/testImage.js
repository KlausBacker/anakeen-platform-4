/*global require*/
require([
    'widgets/attributes/image/loaderImage',
    'widgets/attributes/defaultTestAttribute',
    'widgets/attributes/file/fileTestAttribute',
    'widgets/attributes/image/imageTestAttribute'
], function (widget, defaultTestSuite, fileTestSuite, imageTestSuite) {
    "use strict";

    defaultTestSuite("image : read", widget, {}, {
        creationDate: "2015-01-26 16:46:59",
        displayValue: "drakkar.jpeg",
        fileName: "drakkar.jpeg",
        icon: "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
        mime: "image/jpeg",
        size: "21257",
        thumbnail: "CORE/Images/noimage.png",
        url: "file/34757/3908/tst_image/-1/drakkar.jpeg?cache=no&inline=no",
        value: "image/jpeg|3908|drakkar.jpeg"
    });
    defaultTestSuite("image : write", widget, {mode: "write"}, {
        creationDate: "2015-01-26 16:46:59",
        displayValue: "drakkar.jpeg",
        fileName: "drakkar.jpeg",
        icon: "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
        mime: "image/jpeg",
        size: "21257",
        thumbnail: "CORE/Images/noimage.png",
        url: "file/34757/3908/tst_image/-1/drakkar.jpeg?cache=no&inline=no",
        value: "image/jpeg|3908|drakkar.jpeg"
    });

    fileTestSuite("image : spec", widget, {
            mode: "read",
            renderOptions: {
                downloadInline: true
            }
        },
        {
            creationDate: "2015-01-26 16:46:59",
            displayValue: "drakkar.jpeg",
            fileName: "drakkar.jpeg",
            icon: "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
            mime: "image/jpeg",
            size: "21257",
            thumbnail: "FDL/Images/state.png",
            url: "file/34757/3908/tst_image/-1/drakkar.jpeg?cache=no&inline=no",
            value: "image/jpeg|3908|drakkar.jpeg"

        });

    fileTestSuite("image : spec", widget, {
            mode: "read",
            renderOptions: {
                downloadInline: false
            }
        },
        {
            creationDate: "2015-01-26 16:46:59",
            displayValue: "drakkar.jpeg",
            fileName: "drakkar.jpeg",
            icon: "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
            mime: "image/jpeg",
            size: "21257",
            thumbnail: "FDL/Images/state.png",
            url: "file/34757/3908/tst_image/-1/drakkar.jpeg?cache=no&inline=no",
            value: "image/jpeg|3908|drakkar.jpeg"

        });

    imageTestSuite("image : spec", widget, {
            mode: "read",
            renderOptions: {
                downloadInline: false,
                thumbnailWidth : 200
            }
        },
        {
            creationDate: "2015-01-26 16:46:59",
            displayValue: "drakkar.jpeg",
            fileName: "drakkar.jpeg",
            icon: "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
            mime: "image/jpeg",
            size: "21257",
            thumbnail: "resizeimg.php?img=FDL%2FImages%2Fcreatedoc.png",
            url: "file/34757/3908/tst_image/-1/drakkar.jpeg?cache=no&inline=no",
            value: "image/jpeg|3908|drakkar.jpeg"

        });
    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});