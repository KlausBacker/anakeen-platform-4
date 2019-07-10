export function storeCatalog(catalogData, key = "ank.i18n") {
  if (window.sessionStorage) {
    window.sessionStorage.setItem(key, JSON.stringify(catalogData));
  } else {
    key.split(".").reduce((prev, curr, index, theArray) => {
      prev[curr] = prev[curr] || {};
      if (index === theArray.length - 1) {
        prev[curr] = catalogData;
      }
      return prev[curr];
    }, window);
  }
}

export function loadCatalog(key = "ank.i18n") {
  if (window.sessionStorage) {
    return JSON.parse(window.sessionStorage.getItem(key));
  } else {
    return key.split(".").reduce((prev, curr) => {
      return prev[curr];
    }, window);
  }
}
