interface IRole {
  id: string;
  login: string;
}

interface AccountProps {
  id: string | number;
  login: string;
  data: any;
  type: string;
  roles: IRole[];
  groups: AccountProps[];
}

export default class Account {
  protected id!: string | number;
  protected login!: string;
  protected data: any;
  protected type!: string;
  protected roles!: IRole[];
  protected groups!: AccountProps[];

  constructor(data: AccountProps) {
    this.id = data.id;
    this.login = data.login;
    this.data = data.data;
    this.type = data.type;
    this.roles = data.roles;
    this.groups = data.groups;
  } 

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
