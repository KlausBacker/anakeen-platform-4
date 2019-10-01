export default interface IContext {
  getAccount(login: string | { login: string; password: string; type: string });

  getSmartElement(smartElement: string);

  getSmartElement(properties: { [key: string]: any }, values: { [key: string]: any });

  clean();
};