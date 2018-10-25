export const startsWithRoute = (route, routeStart) => {
  const routePath = route.path.split("/").filter(p => !!p);
  const routeStartPath = routeStart.path.split("/").filter(p => !!p);
  let result = true;
  routeStartPath.forEach((p, index) => {
    result = result && p === routePath[index];
  });
  return result;
};

// Find the default child route
export const findDefaultRoute = (routeDef, fromRouteDef = null) => {
  // stop the recursion if the route contain a variable parameter
  if (fromRouteDef && routeDef.path.indexOf(":") !== -1) {
    return fromRouteDef;
  }
  if (!(routeDef.children && routeDef.children.length)) {
    return routeDef;
  }
  return findDefaultRoute(routeDef.children[0], routeDef);
};

export const findRouteDef = to => routes => {
  const found = routes.find(r => r.name === to.name);
  if (found) {
    return found;
  }
  let i = 0;
  let result = null;
  while (i < routes.length && !result) {
    const childRoute = routes[i];
    if (childRoute.children && childRoute.children.length) {
      result = findRouteDef(to)(childRoute.children);
    }
    i++;
  }
  return result;
};
