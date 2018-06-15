window.dcp.document.documentController('addEventListener',
    'ready',
    {
        name: 'displayMessage.iuser',
        documentCheck: (documentObject) => {
            const serverData = window.dcp.document.documentController("getCustomServerData");
            return serverData && serverData["messages"];
        }
    },
    () => {
        const serverData = window.dcp.document.documentController("getCustomServerData");
        if (serverData && serverData["messages"]) {
            serverData["messages"].map((currentMessage) => {
                window.dcp.document.documentController("showMessage", {"type": "error", "message": currentMessage});
            })
        }
    });