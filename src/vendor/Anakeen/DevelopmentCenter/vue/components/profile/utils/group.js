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

const cleanNonGroupParent = indexedAcls => {
  return Object.values(indexedAcls).reduce((acc, currentValue) => {
    currentValue.parents = currentValue.parents || [];
    acc[currentValue.id] = {
      ...currentValue,
      ...{
        parents: currentValue.parents.filter(currentParentId => {
          //Keep only parent of group type
          return (
            indexedAcls[currentParentId] &&
            indexedAcls[currentParentId].account.type === "group"
          );
        })
      }
    };
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

const flattenList = ancestorList => {
  const resultList = [];
  const customDuplicator = (aclList, parentId) => {
    aclList.forEach(currentAcl => {
      const namedChildren = {
        ...currentAcl,
        ...{
          parentId,
          hierarchicalId: `${parentId || ""} ${currentAcl.id}`
        }
      };
      customDuplicator(namedChildren.children, namedChildren.hierarchicalId);
      resultList.push(namedChildren);
    });
  };
  customDuplicator(ancestorList, false);
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
    currentElement.id = checksum(
      currentElement.hierarchicalId || currentElement.id
    );
    if (currentElement.parentId) {
      currentElement.parentId = checksum(currentElement.parentId);
    }
    delete currentElement.children;
    return currentElement;
  });
};

export const convertAclToKendoStyle = acls => {
  const indexedAcls = convertAclArrayToIndexedAcl(acls);
  const suppressUselessParents = cleanNonGroupParent(indexedAcls);
  const childrenAcls = addChildrenToParent(suppressUselessParents);
  const uniqueChildrenRefs = deduplicateRefs(childrenAcls);
  const parentOnly = getAncestors(uniqueChildrenRefs);
  const flatList = flattenList(parentOnly);
  return reindexAndCleanList(flatList);
};
