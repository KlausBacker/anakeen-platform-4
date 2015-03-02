/*global require*/
require([
    'dcpDocument/widgets/attributes/file/loaderFile',
    'dcpDocument/widgets/attributes/defaultTestAttribute',
    'dcpDocument/widgets/attributes/file/fileTestAttribute'
], function (widget, defaultTestSuite, fileTestSuite) {
    "use strict";

    defaultTestSuite("file : read", widget, {}, {
            creationDate: "2015-01-26 17:38:59",
            displayValue: "TestFile.txt",
            fileName: "TestFile.txt",
            icon: "resizeimg.php?img=CORE%2FImages%2Fmime-txt.png&size=20",
            mime: "text/plain",
            size: "2",
            url: "file/34757/3910/tst_file/-1/TestFile.txt?cache=no&inline=no",
            value: "text/plain|3910|TestFile.txt"
        }
    );
    defaultTestSuite("file : write", widget, {mode: "write"}, {
        creationDate: "2015-01-26 17:38:59",
        displayValue: "TestFile.txt",
        fileName: "TestFile.txt",
        icon: "resizeimg.php?img=CORE%2FImages%2Fmime-txt.png&size=20",
        mime: "text/plain",
        size: "2",
        url: "file/34757/3910/tst_file/-1/TestFile.txt?cache=no&inline=no",
        value: "text/plain|3910|TestFile.txt"
    });

    fileTestSuite("file : spec", widget, {
            mode: "read",
            renderOptions: {
                downloadInline: true
            }
        },
        {
            creationDate: "2015-01-26 17:38:59",
            displayValue: "InlineSpecFile.txt",
            fileName: "InlineSpecFile.txt",
            icon: "resizeimg.php?img=CORE%2FImages%2Fmime-txt.png&size=20",
            mime: "text/plain",
            size: "2",
            url: "file/34757/3910/tst_file/-1/InlineSpecFile.txt?cache=no&inline=no",
            value: "text/plain|3910|InlineSpecFile.txt"

        });
    fileTestSuite("file : spec", widget, {
            mode: "read",
            renderOptions: {
                downloadInline: false
            }
        },
        {
            creationDate: "2015-01-26 17:38:59",
            displayValue: "SpecFile.txt",
            fileName: "SpecFile.txt",
            icon: "resizeimg.php?img=CORE%2FImages%2Fmime-txt.png&size=20",
            mime: "text/plain",
            size: "2",
            url: "file/34757/3910/tst_file/-1/SpecFile.txt?cache=no&inline=no",
            value: "text/plain|3910|SpecFile.txt"

        });
    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});