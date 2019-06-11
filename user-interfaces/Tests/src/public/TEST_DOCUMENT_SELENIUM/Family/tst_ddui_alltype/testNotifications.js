define(['underscore'], function enumOtherCustom(_)
{
    'use strict';
    /**
     * Add custom class for enum which are other value
     */
    window.dcp.document.documentController("addEventListener", "ready", {
        "name": "tstddui.notifs", "documentCheck": function checkDduiEnumReady(document)
        {
            return true;
        }
    }, function testDduiEnumReady(event, documentObject)
    {
        var $this = $(this);
        $(this).documentController("showMessage", {
            "type": "info", "message": "Une information de la plus haute importance"
        });
        _.delay(function ()
        {
            $this.documentController("showMessage", {
                "type": "error", "message": "Une erreur très importante", "htmlMessage": "<b>Attention</b>"
            });
        }, 1000);

        _.delay(function ()
        {
            $this.documentController("showMessage", {
                "type": "success", "message": "Vous avez réussi le test", "htmlMessage": "<b>Félicitation</b>"
            });
        }, 3000);


        _.delay(function ()
        {
            $this.documentController("showMessage", {
                "type": "warning", "message": "Attention au mur", "htmlMessage": "<i>Il est devant</i>"
            });
        }, 5000);


        _.delay(function ()
        {
            $this.documentController("showMessage", {
                "type": "notice", "message": "Rien de spécial", "htmlMessage": "<h1>Voilà c'est tout</h1>"
            });
        }, 8000);
    });
});