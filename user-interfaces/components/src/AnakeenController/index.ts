class ControllerNotInitializedError extends Error {}

let result = null;
if (!(window.ank && window.ank.smartElement && window.ank.smartElement.globalController)) {
  throw new ControllerNotInitializedError(
    "The Anakeen Controller must be initialized before the consumption of the npm Anakeen Controller module"
  );
} else {
  result = window.ank.smartElement.globalController;
}

export default result;
