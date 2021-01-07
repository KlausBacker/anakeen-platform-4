export default class SmartFormTestData {
  constructor(title) {
    this.title = title;
    this.readme = "";
    this.formConfig = {};
    this.listeners = {};

    this.automaticTests = [];
    this.smartController = null;
  }
}
