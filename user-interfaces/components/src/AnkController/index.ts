class ControllerNotInitializedError extends Error {}

if (!(window.ank && window.ank.smartElement && window.ank.smartElement.globalController)) {
  throw new ControllerNotInitializedError(
    "The Anakeen Controller must be initialized before the consumption of the npm Anakeen Controller module"
  );
}

export default window.ank.smartElement.globalController;
