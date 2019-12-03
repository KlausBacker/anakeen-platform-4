import globalController from "../../../src/vendor/Anakeen/DOCUMENT/IHM/widgets/globalController/index";

export default globalController.then(() => {
  return import("./AnkSmartElement.vue").then(AnkSmartElement => {
    return AnkSmartElement;
  });
});
