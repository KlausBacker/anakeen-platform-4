export default class VueEventBus {
    protected events: {
        [key: string]: Array<(...args: any[]) => void>;
    };
    emit(eventName: any, ...args: any[]): void;
    on(eventName: string, callback: (...args: any[]) => void): void;
    off(eventName: string): void;
}
