export const checksum = (str, seed) => {
  /*jshint bitwise:false */
  let i,
    l,
    hval = seed === undefined ? 0x811c9dc5 : seed;

  for (i = 0, l = str.length; i < l; i++) {
    hval ^= str.charCodeAt(i);
    hval +=
      (hval << 1) + (hval << 4) + (hval << 7) + (hval << 8) + (hval << 24);
  }
  return hval >>> 0;
};

const convertAclArrayToIndexedAcl = acls => {
  return acls.reduce((acc, currentAcl) => {
    acc[currentAcl.id] = currentAcl;
    return acc;
  }, {});
};

const addChildrenToParent = indexedAcls => {
  const internalAcls = JSON.parse(JSON.stringify(indexedAcls));
  Object.values(internalAcls).forEach(currentAcl => {
    currentAcl.parents = currentAcl.parents || [];
    currentAcl.children = currentAcl.children || [];
    currentAcl.parents.forEach(currentParentId => {
      internalAcls[currentParentId].children =
        internalAcls[currentParentId].children || [];
      internalAcls[currentParentId].children.push(currentAcl);
    });
  });
  return internalAcls;
};

const deduplicateRefs = indexedAcls => {
  return JSON.parse(JSON.stringify(indexedAcls));
};

const flattenList = aclList => {
  const firstLevel = "fl";
  const resultList = Object.values(aclList).map(currentElement => {
    currentElement.hierarchicalId = firstLevel;
    return currentElement;
  });
  const customDuplicator = (aclList, currentId = "") => {
    aclList.forEach(currentAcl => {
      currentAcl.children.forEach(currentChildren => {
        const namedChildren = {
          ...currentChildren,
          ...{
            parentId: currentId,
            hierarchicalId: currentId + "-" + currentChildren.id
          }
        };
        customDuplicator(namedChildren.children, namedChildren.hierarchicalId);
        resultList.push(namedChildren);
      });
    });
  };
  customDuplicator(aclList, firstLevel);
  return resultList;
};

const getAncestors = indexedList => {
  return Object.values(indexedList).filter(currentElement => {
    return currentElement.parents.length === 0;
  });
};

const reindexAndCleanList = flatList => {
  return flatList.map(currentElement => {
    currentElement.accountId = currentElement.id;
    currentElement.id = checksum(currentElement.hierarchicalId);
    if (currentElement.parentId) {
      currentElement.parentId = checksum(currentElement.parentId);
    }
    delete currentElement.children;
    return currentElement;
  });
};

export const convertAclToKendoStyle = acls => {
  const indexedAcls = convertAclArrayToIndexedAcl(acls);
  const childrenAcls = addChildrenToParent(indexedAcls);
  const uniqueChildrenRefs = deduplicateRefs(childrenAcls);
  const parentOnly = getAncestors(uniqueChildrenRefs);
  const flatList = flattenList(parentOnly);
  return reindexAndCleanList(flatList);
};
