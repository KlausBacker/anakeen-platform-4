window.ank.smartElement.globalController.registerFunction("iuser", controller => {
  controller.addEventListener(
    "ready",
    {
      name: "addmenu",
      check: documentObject => {
        const serverData = controller.getCustomServerData();
        return (
          documentObject.renderMode === "edit" && serverData && serverData["EDIT_GROUP"] && serverData["defaultGroup"]
        );
      }
    },
    () => {
      const serverData = controller.getCustomServerData();
      controller.addCustomClientData({
        setGroup: serverData.defaultGroup
      });
    }
  );
});
