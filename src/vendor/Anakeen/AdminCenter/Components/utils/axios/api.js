import axios from "axios";

export default axios.create({
  timeout: 10000,
  baseURL: `/api/v2/`,
  withCredentials: false
});
