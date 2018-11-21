import PropertiesView from "devComponents/PropertiesView/PropertiesView.vue";
import Component from "./SmartElements.vue";
import ElementView from "./ElementView/ElementView.vue";
import RawElementView from "./RawElementView/RawElementView.vue";
import ProfileGrid from "../../components/profile/profile.vue";

export default {
  name: "SmartElements",
  path: "smartElements",
  meta: {
    label: "Smart Elements"
  },
  order: 4,
  component: Component,
  children: [
    {
      name: "SmartElements::ElementView",
      path: ":seIdentifier/view",
      meta: {
        label: ":seIdentifier"
      },
      component: ElementView,
      props: route => ({
        initid: route.params.seIdentifier.toString(),
        viewId: "!defaultConsultation"
      })
    },
    {
      name: "SmartElements::CreationView",
      path: ":seIdentifier/creation",
      meta: {
        label: ":seIdentifier"
      },
      component: ElementView,
      props: route => ({
        initid: route.params.seIdentifier.toString(),
        viewId: "!defaultCreation"
      })
    },
    {
      name: "SmartElements::PropertiesView",
      path: ":seIdentifier/properties",
      meta: {
        label: ":seIdentifier properties"
      },
      component: PropertiesView,
      props: route => ({
        elementId: route.params.seIdentifier
      })
    },
    {
      name: "SmartElements::ProfilView",
      path: ":seIdentifier/security",
      meta: {
        label: ":seIdentifier profil"
      },
      component: ProfileGrid,
      props: route => ({
        profileId: route.query.profileId,
        detachable: true
      })
    },
    {
      name: "SmartElements::RawElementView",
      path: ":seIdentifier/:seType",
      meta: {
        label: route => `:seIdentifier ${route.query.formatType} :seType`
      },
      component: RawElementView,
      props: route => ({
        elementId: route.params.seIdentifier,
        formatType: route.query.formatType,
        elementType: route.params.seType
      })
    }
  ]
};
