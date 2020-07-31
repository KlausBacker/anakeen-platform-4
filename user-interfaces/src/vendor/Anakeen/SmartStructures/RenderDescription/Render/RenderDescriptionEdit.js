import "./RenderDescriptionEdit.scss";
import $ from "jquery";
import AnkInitGlobalController from "@anakeen/user-interfaces/components/lib/AnkInitController.esm";

export default AnkInitGlobalController.then(globalController => {
  globalController.registerFunction("renderDescriptionEdit", controller => {});
});
