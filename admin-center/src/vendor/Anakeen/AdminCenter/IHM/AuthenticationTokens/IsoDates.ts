export default class IsoDates {
  public static getIsoData(dateObject: Date): string {
    return (
      dateObject.getFullYear() +
      "-" +
      IsoDates.padNumber(dateObject.getMonth() + 1) +
      "-" +
      IsoDates.padNumber(dateObject.getDate()) +
      "T" +
      IsoDates.padNumber(dateObject.getHours()) +
      ":" +
      IsoDates.padNumber(dateObject.getMinutes()) +
      ":" +
      IsoDates.padNumber(dateObject.getSeconds())
    );
  }

  protected static padNumber(n: number): string {
    if (n < 10) {
      return "0" + n;
    }
    return n.toString();
  }
}
