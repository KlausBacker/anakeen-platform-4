export interface IAuthent {
  initResetPassword();

  initForgetElements();

  getSearchArg(arg: string): any;

  authToken: any;
}