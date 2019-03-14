export interface IGrid {
  privateScope: {
    initGrid(...args: any[]),
    getQueryParamsData(...args: any[]),
    getGridConfig(...args: any[]),
    bindGridEvents(),
    notifyChange(),
  }
}