window.dcp.document.documentController(
    "addEventListener",
    "actionClick",
    {
        "name": "my.alert",
        "documentCheck": function (documentObject) {
            return (documentObject.family.name === "TST_DDUI_ALLTYPE");
        }
    },
    function(event, documentObject, data) {
        console.log("i am here")
        if (data.eventId === "my") {
            if (data.options.length > 0 && data.options[0] === "myOptions" ) {

                alert("I catched my event");
            }
        }
    }
);
