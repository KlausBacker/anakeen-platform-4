// jscs:disable disallowFunctionDeclarations
import AnkEventUtils from "./EventUtilsMixin";
import AnkReadyMixins from "./ReadyMixin";

export const AnkEventMixin = AnkEventUtils;
export const AnkReadyMixin = AnkReadyMixins;

export default [AnkEventMixin, AnkReadyMixin];
