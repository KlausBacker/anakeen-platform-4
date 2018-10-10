import * as mutations from "./mutation-types";

export const helloWorld = ({ commit }) => {
  commit(mutations.APP_HELLO_WORLD);
};
