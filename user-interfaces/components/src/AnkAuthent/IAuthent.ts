export interface IAuthent {
  initResetPassword();

  initForgetElements();

  getSearchArg(arg: string): string;

  authToken: string | null;
}
