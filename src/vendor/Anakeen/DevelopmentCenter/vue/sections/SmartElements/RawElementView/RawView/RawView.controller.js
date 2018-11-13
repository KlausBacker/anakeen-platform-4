import hljs from "highlight.js/lib/highlight";
import xml from "highlight.js/lib/languages/xml";
import json from "highlight.js/lib/languages/json";

const prettifyXml = sourceXml => {
  const xmlDoc = new DOMParser().parseFromString(sourceXml, "application/xml");
  const xsltDoc = new DOMParser().parseFromString(
    [
      // describes how we want to modify the XML - indent everything
      '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform">',
      '  <xsl:strip-space elements="*"/>',
      '  <xsl:template match="para[content-style][not(text())]">', // change to just text() to strip space in text nodes
      '    <xsl:value-of select="normalize-space(.)"/>',
      "  </xsl:template>",
      '  <xsl:template match="node()|@*">',
      '    <xsl:copy><xsl:apply-templates select="node()|@*"/></xsl:copy>',
      "  </xsl:template>",
      '  <xsl:output indent="yes"/>',
      "</xsl:stylesheet>"
    ].join("\n"),
    "application/xml"
  );

  const xsltProcessor = new XSLTProcessor();
  xsltProcessor.importStylesheet(xsltDoc);
  const resultDoc = xsltProcessor.transformToDocument(xmlDoc);
  const resultXml = new XMLSerializer().serializeToString(resultDoc);
  return resultXml;
};

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
          return this.hljs.highlightAuto(prettifyXml(stringContent)).value;
        default:
          return this.content;
      }
    }
  }
};
