export interface IAuthent {
  _protected: {
    initResetPassword();
    initForgetElements();
    getSearchArg(arg: string): any;
  },
  authToken: any;
}