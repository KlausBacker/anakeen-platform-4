/* tslint:disable:object-literal-sort-keys */
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";

import VJsoneditor from "v-jsoneditor/src/index";

import Vue from "vue";
import Component from "vue-class-component";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    VJsoneditor,
    "ank-smart-form": AnkSmartForm,
    "ank-splitter": AnkSplitter
  }
})
export default class TestSmartFormController extends Vue {
  public json: object = {
    title:"Mon formulaire préféré",
    type:"Invitation",
    renderOptions: {
      fields: {
        my_title: {
          description: {
            collapsed: false,
            htmlContent: "<p>Description du titre</p>",
            htmlTitle: "<h3>Ceci est le titre</h3>",
            position: "top"
          }
        },
        my_firstframe: {
          responsiveColumns: [
            { number: 2, minWidth: "70rem", maxWidth: "100rem", grow: false },
            { number: 3, minWidth: "100rem", maxWidth: "130rem", grow: false },
            { number: 4, minWidth: "130rem", maxWidth: null, grow: false }
          ]
        }
      }
    },
    structure: [
      {
        content: [
          {
            label: "Title",
            name: "my_title",
            type: "text"
          },
          {
            label: "Date de rédaction",
            name: "my_date",
            type: "date"
          },
          {
            label: "Remarques",
            name: "my_comment",
            type: "htmltext"
          }
        ],
        label: "My first frame",
        name: "my_firstframe",
        type: "frame"
      }
    ],
    values: {
      my_date: "2019-07-24",
      my_title: "Hello world"
    }
  };
  public options: object = {
    mode: "text"
  };

  public panes: object[] = [
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "30%"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "70%"
    }
  ];

  // noinspection JSMethodCanBeStatic
  public onError(e) {
    console.error(e);
  }

  public mounted() {
    // @ts-ignore
    this.$refs.smartFormSplitter.disableEmptyContent();
  }
}
