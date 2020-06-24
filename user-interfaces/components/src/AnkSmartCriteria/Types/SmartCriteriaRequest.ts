export default class SmartCriteriaRequest {
  fail(err: JQuery.jqXHR<any>) {
    throw new Error("Method not implemented.");
  }
  done(response: any) {
    throw new Error("Method not implemented.");
  }
  url: string;
  data?: any;
}
