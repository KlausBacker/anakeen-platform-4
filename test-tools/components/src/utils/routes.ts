export interface ITestOptions {
  login?: string;
  dryRun?: boolean;
  searchParams?: object;
}

export interface StateInfos {
  transition: string;
  askValues?: object;
}

export interface SmartField {
  [fieldId: string]: { value: string };
}

export const searchParams = function(options?: ITestOptions) {
  const searchParams = new URLSearchParams();
  if (options && options.login) {
    searchParams.set("login", options.login);
  }
  if (options && options.dryRun) {
    searchParams.set("dry-run", options.dryRun.toString());
  }
  if (options && options.searchParams) {
    for (let searchParam in options.searchParams) {
      searchParams.set(searchParam, options.searchParams[searchParam]);
    }
  }
  return searchParams;
};
