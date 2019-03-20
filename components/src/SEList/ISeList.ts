export interface ISeList {
  replaceTopPagerButton();

  propageKendoDataSourceEvent(eventName: string, eventType?: string);

  initKendo();

  onPagerChange(event): void;

  sendGetRequest(url: string, conf);

  onSelectPageSize(event): void;

  onSelectSe(event): void;
}
