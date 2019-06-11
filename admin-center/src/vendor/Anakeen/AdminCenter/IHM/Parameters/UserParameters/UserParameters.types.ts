interface IUserParametersDataSource {
  read: () => Promise<any>;
  filter: (x) => object[];
}
