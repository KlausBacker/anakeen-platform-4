window.dcp.document.documentController('addEventListener',
    'ready',
    {
        name: 'addmenu',
        documentCheck: (documentObject) => {
            const serverData = window.dcp.document.documentController("getCustomServerData");
            return documentObject.renderMode === 'view' && serverData && serverData["ADD_CUSTOM_MENU"];
        }
    },
    () => {
        window.dcp.document.documentController(
            "addEventListener",
            "actionClick",
            {
                "name": "addEventMenu.iuser",
                "documentCheck": function (documentObject) {
                    const serverData = window.dcp.document.documentController("getCustomServerData");
                    return documentObject.renderMode === 'view' && serverData["ADD_CUSTOM_MENU"];
                }
            },
            function(event, documentObject, data) {
                if (data.eventId === "customIuserMenu") {
                    if (data.options && data.options[0] ) {
                        let action = null;
                        switch (data.options[0]) {
                            case "deactivateAccount":
                                action = "disable";
                                break;
                            case "resetLoginFailure":
                                action = "resetLogin";
                                break;
                            case "activateAccount":
                                action = "enable";
                                break;
                        }
                        fetch(`/api/v2/admin/account/users/${documentObject.id}/${action}`, {
                            credentials: "same-origin",
                            method: "POST"
                        }).then((response) => {
                            if (response.status !== 200) {
                                throw response;
                            }
                            window.dcp.document.documentController("reinitDocument");
                        }).catch((err) => {
                            window.dcp.document.documentController("showMessage", {"type": "error", "message": err.data});
                        })
                    }
                }
            }
        );
    });