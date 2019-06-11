export default class ErrorEvent {
  protected message: string;
  protected title: string;
  protected errorCode: number;

  constructor(
    message: string = "Something went wrong",
    title: string = "Server error",
    errorCode: number = 500
  ) {
    this.message = message;
    this.title = title;
    this.errorCode = errorCode;
  }
}
