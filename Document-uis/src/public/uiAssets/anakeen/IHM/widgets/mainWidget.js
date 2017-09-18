(function packWidget() {

    "use strict";

    if (typeof define === 'function' && define.amd) {
        require([
            'dcpDocument/i18n/getTranslator',
            'dcpDocument/widgets/attributes/password/wPassword',
            'dcpDocument/widgets/attributes/text/wText',
            'dcpDocument/widgets/attributes/money/wMoney',
            'dcpDocument/widgets/attributes/double/wDouble',
            'dcpDocument/widgets/attributes/color/wColor',
            'dcpDocument/widgets/attributes/date/wDate',
            'dcpDocument/widgets/attributes/timestamp/wTimestamp',
            'dcpDocument/widgets/attributes/file/wFile',
            'dcpDocument/widgets/attributes/label/wLabel',
            'dcpDocument/widgets/attributes/longtext/wLongtext',
            'dcpDocument/widgets/attributes/enum/wEnum',
            'dcpDocument/widgets/attributes/int/wInt',
            'dcpDocument/widgets/attributes/wAttribute',
            'dcpDocument/widgets/attributes/array/wArray',
            'dcpDocument/widgets/attributes/image/wImage',
            'dcpDocument/widgets/attributes/time/wTime',
            'dcpDocument/widgets/attributes/htmltext/wHtmltext',
            'dcpDocument/widgets/attributes/docid/wDocid',
            'dcpDocument/widgets/attributes/docid/wCreateDocument'
        ], function require_widget()
        {
        });
    }
})();

