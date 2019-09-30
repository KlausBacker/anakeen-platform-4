interface IRole {
  id: string;
}

interface IGroup {
  id: string;
}

export default class Account {
  protected id!: string | number;
  protected login!: string;
  protected data: any;
  protected type!: string;
  protected roles!: IRole[];
  protected groups!: IGroup[];

  public logAs(login: string): Account {
    console.log("Login", login);
    return this;
  }

  public associate(account: Account): Account {
    console.log("Associate", account);
    return this;
  }

  public dissociate(account: Account): Account {
    console.log("Dissociate", account);
    return this;
  }

  public destroy(): void {
    console.log("Destroy");
  }
}
