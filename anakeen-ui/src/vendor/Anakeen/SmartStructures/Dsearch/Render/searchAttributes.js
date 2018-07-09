/*global define*/

/*
Return attributes list information from a family which can be used as criteria
 */

const searchAttributes = {};

export default familyIdentifier => {
  if (!searchAttributes[familyIdentifier]) {
    searchAttributes[familyIdentifier] = new Promise(
      (xhrResolve, xhrReject) => {
        $.getJSON(
          "api/v2/smartstructures/dsearch/attributes/" + familyIdentifier
        )
          .done(xhrResolve)
          .fail(xhrReject);
      }
    );
  }
  return searchAttributes[familyIdentifier];
};
