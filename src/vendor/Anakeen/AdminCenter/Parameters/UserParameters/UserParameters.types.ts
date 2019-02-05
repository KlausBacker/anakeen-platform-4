interface userParametersDataSource {
    read: () => Promise<any>;
    filter: (x) => Array<object>;
}
