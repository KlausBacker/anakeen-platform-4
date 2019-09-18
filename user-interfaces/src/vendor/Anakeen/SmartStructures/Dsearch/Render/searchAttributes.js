/*
Return attributes list information from a family which can be used as criteria
 */

const searchAttributes = {};

export default familyIdentifier => {
  if (!searchAttributes[familyIdentifier]) {
    searchAttributes[familyIdentifier] = new Promise((xhrResolve, xhrReject) => {
      const famId = familyIdentifier ? familyIdentifier : "";
      $.getJSON("/api/v2/smartstructures/dsearch/attributes/" + famId)
        .done(xhrResolve)
        .fail(xhrReject);
    });
  }
  return searchAttributes[familyIdentifier];
};
