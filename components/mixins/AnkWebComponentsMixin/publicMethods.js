// jscs:disable disallowFunctionDeclarations
const PUBLIC_METHODS_FIELD = "publicMethods";
// const IGNORED_METHODS_TOKEN = ["_", "$"];

const ERROR_CODES = {
  UICOMPONENT1001: {
    code: "UICOMPONENT1001",
    message: "The component has no Vue instance available"
  },
  UICOMPONENT1002: {
    code: "UICOMPONENT1002",
    message: 'Public method "%s" failed'
  },
  UICOMPONENT1003: {
    code: "UICOMPONENT1003",
    message: "No DOM Element found for the web component"
  }
};

const error = (errorCode, ...args) => {
  const displayErrorMsg = `Ank Component Mixin error (${
    ERROR_CODES[errorCode].code
  }) : ${sprintf(ERROR_CODES[errorCode].message, args)}`;
  console.error(displayErrorMsg);
  throw displayErrorMsg;
};

const sprintf = (str, ...substitutes) => {
  let counter = 0;
  return str.replace(/%s/g, () => substitutes[counter++]);
};

const attachPublicMethods = (domElement, vueInst = null) => {
  if (!domElement) {
    error("UICOMPONENT1003");
  }

  const vueInstance = getVueInstance(domElement, vueInst);

  if (!vueInstance) {
    error("UICOMPONENT1001");
  }

  domElement[PUBLIC_METHODS_FIELD] = vueInstance;
};

const getVueInstance = (domElement, vueInst = null) => {
  let vueInstance = vueInst;
  if (!vueInstance && typeof domElement.getVueInstance === "function") {
    vueInstance = domElement.getVueInstance();
  }
  if (!vueInstance && domElement.__vue_custom_element__) {
    // Most of the time, the method getVueInstance is binded too late
    vueInstance = domElement.__vue_custom_element__.$children[0];
  }
  return vueInstance;
};

/**
 * Expose public methods (from method sections) in DOM props
 * @param domElement - The custom element
 * @param vueInst - The vue instance
 */
export default (domElement, vueInst = null) => {
  if (!domElement) {
    error("UICOMPONENT1003");
  }
  const vueInstance = getVueInstance(domElement, vueInst);
  if (!vueInstance) {
    const observer = new MutationObserver(mutationsList => {
      for (let i = 0; i < mutationsList.length; i++) {
        const mutation = mutationsList[i];
        if (mutation.type === "attributes") {
          if (mutation.attributeName === "vce-ready") {
            attachPublicMethods(mutation.target);
            observer.disconnect();
          }
        }
      }
    });
    observer.observe(domElement, { attributes: true });
  } else {
    attachPublicMethods(domElement, vueInstance);
  }
};
