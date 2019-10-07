import fetch from "node-fetch";

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

  private fetchApi: any = null;

  constructor(data: AccountProps, fetch?: any) {
    this.id = data.id;
    this.login = data.login;
    this.data = data.data;
    this.type = data.type;
    this.roles = data.roles;
    this.groups = data.groups;

    this.fetchApi = fetch;
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
  public async addToAGroup(group: string | Account) {
    let login: string;
    if (typeof group === "string") {
      login = group;
    } else {
      login = group.login;
    }
    const requestData: { [key: string]: any } = {accountlogin: this.login};
    const response = await this.fetchApi(
      `/api/v2/test-tools/groups/${login}/`,
      {
        body: JSON.stringify(requestData),
        method: "put",
        headers: {
          "Content-Type": "application/json"
        }
      }
    );  
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data) {
      return new Account(responseJson.data, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }

  }
  public async removeFromAGroup(group: string | Account) {
    let login: string;
    if (typeof group === "string") {
      login = group;
    } else {
      login = group.login;
    }
    const requestData: { [key: string]: any } = {accountlogin: this.login};
    const response = await this.fetchApi(
      `/api/v2/test-tools/groups/${login}/`,
      {
        body: JSON.stringify(requestData),
        method: "delete",
        headers: {
          "Content-Type": "application/json"
        }
      }
    );
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data) {
      return new Account(responseJson.data, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }
  public async addRole(role: string | Account) {
    let login: string;
    if (typeof role === "string") {
      login = role;
    } else {
      login = role.login;
    }
    const requestData: { [key: string]: any } = {accountlogin: this.login};
    const response = await this.fetchApi(
      `/api/v2/test-tools/roles/${login}/`,
      {
        body: JSON.stringify(requestData),
        method: "put",
        headers: {
          "Content-Type": "application/json"
        }
      }
    );
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data) {
      return new Account(responseJson.data, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }
  public async removeRole(role: string | Account) {
    let login: string;
    if (typeof role === "string") {
      login = role;
    } else {
      login = role.login;
    }
    const requestData: { [key: string]: any } = {accountlogin: this.login};
    const response = await this.fetchApi(
      `/api/v2/test-tools/roles/${login}/`,
      {
        body: JSON.stringify(requestData),
        method: "delete",
        headers: {
          "Content-Type": "application/json"
        }
      }
    );
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data) {
      return new Account(responseJson.data, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }
}
