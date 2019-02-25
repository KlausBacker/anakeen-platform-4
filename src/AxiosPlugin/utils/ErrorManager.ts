// tslint:disable: no-console
import { AxiosInstance, AxiosResponse } from "axios";
import AxiosErrorEvent from "./ErrorEvent";

interface IEventCallback {
  (): any;
  onceCallback: boolean;
}
interface IErrorManagerEvent {
  [eventName: string]: IEventCallback[];
}

interface IErrorManagerEvents {
  emit(eventName: string, ...args: any[]): void;
  on(eventName: string, cb: IEventCallback);
  once(eventName: string, cb: IEventCallback);
  off(eventName: string, cb?: IEventCallback);
}

// Manage App Errors
export default class ErrorManager implements IErrorManagerEvents {
  protected events: IErrorManagerEvent = {};
  protected axiosInstance: AxiosInstance;

  constructor(axios: AxiosInstance) {
    this.axiosInstance = axios;
    if (!this.axiosInstance) {
      throw new Error(
        "[AxiosErrorManager] : Unable to retrieve the instance of axios"
      );
    }
    this.bindNetworkCommonsErrors();
  }

  public off(eventName: string, cb?: IEventCallback) {
    if (this.events[eventName]) {
      if (!cb) {
        delete this.events[eventName];
      } else {
        const index = this.events[eventName].findIndex(c => c === cb);
        if (index > -1) {
          this.events[eventName].splice(index, 1);
        }
      }
    }
  }

  public on(eventName: string, cb: IEventCallback) {
    if (this.events[eventName]) {
      this.events[eventName].push(cb);
    } else {
      this.events[eventName] = [cb];
    }
  }

  public once(eventName: string, cb: IEventCallback) {
    cb.onceCallback = true;
    this.on(eventName, cb);
  }

  public emit(eventName: string, ...args: any[]): void {
    if (this.events[eventName]) {
      const cbs = this.events[eventName];
      let onceIndex = -1;
      cbs.forEach((callback, index) => {
        // @ts-ignore
        callback.call(this, ...args);
        if (callback.onceCallback) {
          onceIndex = index;
        }
      });
      if (onceIndex !== -1) {
        cbs.splice(onceIndex, 1);
      }
    }
  }

  // Intercept network errors from axios instance
  protected bindNetworkCommonsErrors() {
    this.axiosInstance.interceptors.response.use(
      (response: AxiosResponse) => {
        if (response.headers) {
          if (
            response.headers["content-type"].indexOf("application/json") > -1 &&
            response.request &&
            response.request.responseText
          ) {
            try {
              JSON.parse(response.request.responseText);
            } catch (err) {
              console.error(
                `JSON parsing response error for request : ${response.request.toString()}`
              );
              return Promise.reject(response) as Promise<AxiosResponse>;
            }
          }
        }
        return response;
      },
      error => {
        if (error.response) {
          // The request was made and the server responded with a status code
          // that falls out of the range of 2xx
          if (
            error.response.data.message ||
            error.response.data.exceptionMessage
          ) {
            this.emit(
              "error",
              new AxiosErrorEvent(
                error.response.data.message ||
                  error.response.data.exceptionMessage
              )
            );
          }
          if (error.response.data.error) {
            this.emit(
              "displayError",
              new AxiosErrorEvent(error.response.data.error)
            );
          }
          console.error(error.response);
        } else if (error.request) {
          // The request was made but no response was received
          // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
          // http.ClientRequest in node.js
          this.emit(
            "displayError",
            new AxiosErrorEvent(
              "Looks like there are some network troubles",
              "Network Error"
            )
          );
          console.error(error.request);
        } else {
          // Something happened in setting up the request that triggered an Error
          console.error("Error", error.message);
        }
        return Promise.reject(error);
      }
    );
  }
}
