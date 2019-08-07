window.ank.smartElement.globalController.registerFunction("iuser", controller => {
  controller.addEventListener(
    "ready",
    {
      name: "addmenu",
      check: () => {
        const serverData = controller.getCustomServerData();
        return serverData && serverData["EDIT_GROUP"] && serverData["defaultGroup"];
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
