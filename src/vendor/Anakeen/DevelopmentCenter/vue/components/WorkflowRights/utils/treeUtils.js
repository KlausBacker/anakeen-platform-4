import { checksum } from "../../profile/utils/group";

export const getTreeListData = steps => {
  const data = organizeData(steps);
  const treeResult = {};
  data.forEach((datum, index, allDatum) => {
    insertInTree(datum, treeResult, allDatum);
  });
  return Object.values(treeResult);
};

const organizeData = steps => {
  const organizedData = {};
  if (steps && steps.length) {
    steps.forEach(step => {
      if (step.profilAccess && step.profilAccess.length) {
        step.profilAccess.forEach(access => {
          if (!organizedData[access.id]) {
            organizedData[access.id] = Object.assign({ columns: {} }, access);
          }
          organizedData[access.id].columns[step.id] = access.acls;
        });
      }
    });
  }
  return Object.values(organizedData);
};

const formatItem = (item, id, pId) => ({
  ankId: item.id,
  virtualId: id,
  parentId: pId,
  accountLabel: item.account.reference,
  account: item.account,
  ...(item.columns || {})
});

const createRootNode = (accountType, resultTree) => {
  const nodeId = checksum(accountType);
  if (!resultTree[nodeId]) {
    resultTree[nodeId] = {
      ankId: nodeId,
      virtualId: nodeId,
      accountType: true,
      parentId: null,
      accountLabel: accountType,
      account: { type: accountType }
    };
  }
  return nodeId;
};

const cantorId = (x, y) => ((x + y + 1) * (x + y)) / 2 + y;

const insertInTree = (item, resultTree, allData) => {
  let treeItem;
  if (item.parents && item.parents.length) {
    item.parents.forEach(p => {
      const virtualId = cantorId(item.id, p);
      if (!resultTree[virtualId]) {
        const parentData = allData.find(d => d.id === p);
        const parentItem = insertInTree(parentData, resultTree, allData);
        treeItem = formatItem(item, virtualId, parentItem.virtualId);
        resultTree[virtualId] = treeItem;
      } else {
        treeItem = resultTree[virtualId];
      }
    });
  } else {
    const accountType = item.account.type;
    const rootTypeId = createRootNode(accountType, resultTree);
    if (!resultTree[item.id]) {
      treeItem = formatItem(item, item.id, rootTypeId);
      resultTree[item.id] = treeItem;
    } else {
      treeItem = resultTree[item.id];
    }
  }
  return treeItem;
};
