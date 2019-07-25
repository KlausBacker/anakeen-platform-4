import { checksum } from "../../profile/utils/group";

export const getTreeListData = (fields, invalidItemCb = () => {}) => {
  let data = [];
  if (fields && typeof fields === "object") {
    Object.keys(fields).forEach(fieldId => {
      if (fields[fieldId].id !== undefined) {
        data.push(formatItem(fields[fieldId]));
      } else {
        if (typeof invalidItemCb === "function") {
          invalidItemCb(fieldId, fields[fieldId]);
        }
      }
    });
  }
  return data;
};

const formatItem = item => {
  return {
    fieldId: item.id,
    virtualId: typeof item.id === "string" ? checksum(item.id) : item.id,
    parentId: typeof item.parent === "string" ? checksum(item.parent) : item.parent,
    parent: item.parent,
    type: item.type,
    ...(item.access || {})
  };
};
