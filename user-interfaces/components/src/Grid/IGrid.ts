export interface IGrid {
  initGrid(savedColsOpts?);
  getQueryParamsData(colums, kendoPagerInfo);
  getGridConfig();
  notifyChange();
}
