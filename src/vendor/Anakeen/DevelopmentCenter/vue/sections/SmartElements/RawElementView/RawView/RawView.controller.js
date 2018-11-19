import hljs from "highlight.js/lib/highlight";
import xml from "highlight.js/lib/languages/xml";
import json from "highlight.js/lib/languages/json";

export default {
  props: ["type", "content", "parsed"],
  created() {
    this.hljs.registerLanguage("xml", xml);
    this.hljs.registerLanguage("json", json);
  },
  data() {
    return {
      hljs
    };
  },
  computed: {
    formattedContent() {
      let stringContent = this.content;
      switch (this.type) {
        case "json":
          if (this.parsed) {
            stringContent = JSON.stringify(this.content, null, 2);
          }
          return this.hljs.highlightAuto(stringContent).value;
        case "xml":
          if (this.parsed) {
            stringContent = "";
          }
          return this.hljs.highlightAuto(stringContent).value;
        default:
          return this.content;
      }
    }
  }
};
