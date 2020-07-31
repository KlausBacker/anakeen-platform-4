import "./RenderDescriptionEdit.scss";
import AnkInitGlobalController from "@anakeen/user-interfaces/components/lib/AnkInitController.esm";
//import $ from "jquery";

export default AnkInitGlobalController.then(globalController => {
  globalController.registerFunction("renderDescriptionEdit", () /*controller*/ => {});
});
