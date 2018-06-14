window.dcp.document.documentController('addEventListener',
    'ready',
    {
        name: 'addmenu',
        documentCheck: (documentObject) => {
            const serverData = window.dcp.document.documentController("getCustomServerData");
            return documentObject.renderMode === 'edit' && serverData && serverData["EDIT_GROUP"] && serverData["defaultGroup"];
        }
    },
    () => {
        const serverData = window.dcp.document.documentController("getCustomServerData");
        window.dcp.document.documentController("addCustomClientData", {setGroup: serverData.defaultGroup});
    });