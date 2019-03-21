import Vue from "vue";
import { AxiosInstance, AxiosStatic } from "axios";
import "vue-gettext/types/vue";

declare module "vue/types/vue" {
  interface Vue {
    _uid: number;
    $http: AxiosInstance;
    $axios: AxiosStatic;
  }
}
