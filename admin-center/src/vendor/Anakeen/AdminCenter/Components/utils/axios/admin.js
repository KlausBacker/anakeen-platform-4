import axios from "axios";

export default axios.create({
  timeout: 10000,
  baseURL: `/admin/`,
  withCredentials: false
});
