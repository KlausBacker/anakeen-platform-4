/*global require*/
require([
    'dcpDocumentTest/defaultTestAttribute'
], function (defaultTestSuite) {
    "use strict";

    defaultTestSuite("date : read", "date", {value : "2012-12-14"}, {}, {value : "1985-05-12"});
    defaultTestSuite("date : write", "date", {value : "2012-12-14"}, {renderMode : "edit"}, {value : "1985-05-12"});

    defaultTestSuite("docid : read", "docid", {value : "212", displayValue : "toto"}, {}, {value : "325", displayValue : "titi"});
    defaultTestSuite("docid : write", "docid", {value : "212", displayValue : "toto"}, {renderMode : "edit"}, {value : "325", displayValue : "titi"});

    defaultTestSuite("double : read", "double", {value : 3.14}, {}, {value : 1.2222});
    defaultTestSuite("double : write", "double", {value : 3.14}, {renderMode : "edit"}, {value : 1.2222});

    defaultTestSuite("enum : read", "enum", {value : "g", displayValue : "Sol"}, {}, {value : "U", displayValue : "Sel"});
    defaultTestSuite("enum : write", "enum", {value : "g", displayValue : "Sol"}, {renderMode : "edit"}, {value : "U", displayValue : "Sel"});

    defaultTestSuite("file : read", "file", {value : "g", displayValue : "Sol"}, {}, {value : "U", displayValue : "Sel"});
    defaultTestSuite("file : write", "file", {value : "g", displayValue : "Sol"}, {renderMode : "edit"}, {value : "U", displayValue : "Sel"});

    defaultTestSuite("htmltext : read", "htmltext", {value : "Éric <strong>Brison</strong>"}, {}, {value : "SecondeValue"});
    defaultTestSuite("htmltext : write", "htmltext", {value : "Charles Bonnissent"}, {renderMode : "edit"}, {value : "SecondeValue"});

    defaultTestSuite("image : read", "image", {value : "g", displayValue : "Sol"}, {}, {value : "U", displayValue : "Sel"});
    defaultTestSuite("image : write", "image", {value : "g", displayValue : "Sol"}, {renderMode : "edit"}, {value : "U", displayValue : "Sel"});

    defaultTestSuite("int : read", "int", {value : 42}, {}, {value : 256});
    defaultTestSuite("int : write", "int", {value : 512}, {renderMode : "edit"}, {value : 1024});

    defaultTestSuite("longtext : read", "longtext", {value : "Éric <strong>Brison</strong>"}, {}, {value : "SecondeValue"});
    defaultTestSuite("longtext : write", "longtext", {value : "Charles Bonnissent"}, {renderMode : "edit"}, {value : "SecondeValue"});

    defaultTestSuite("money : read", "money", {value : 42}, {}, {value : 256});
    defaultTestSuite("money : write", "money", {value : 512}, {renderMode : "edit"}, {value : 1024});

    defaultTestSuite("text : read", "text", {value : "Éric <strong>Brison</strong>"}, {}, {value : "SecondeValue"});
    defaultTestSuite("text : write", "text", {value : "Charles Bonnissent"}, {renderMode : "edit"}, {value : "SecondeValue"});

    defaultTestSuite("time : read", "time", {value : "03:00"}, {}, {value : "12:00"});
    defaultTestSuite("time : write", "time", {value : "03:00"}, {renderMode : "edit"}, {value : "12:00"});

    defaultTestSuite("timestamp : read", "timestamp", {value : "2025-05-12 18:00:05"}, {}, {value : "1985-05-12 18:00:05"});
    defaultTestSuite("timestamp : write", "timestamp", {value : "2025-05-12 18:00:05"}, {renderMode : "edit"}, {value : "1985-05-12 18:00:05"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});