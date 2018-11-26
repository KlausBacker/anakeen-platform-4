import axios from "axios";

export const DEFAULT_AXIOS = {};

export default axios.create(
  Object.assign({}, DEFAULT_AXIOS, {
    timeout: 10000,
    withCredentials: false
  })
);
