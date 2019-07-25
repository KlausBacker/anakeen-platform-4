define(["jquery", "underscore"], function libSmartForm($, _) {
  "use strict";
  let defaultRenderConfig;
  const _flatTheStructure = function _flatTheStructure(structure, parent) {
    let flatStruct = [];
    _.each(structure, function(field) {
      if (parent) {
        field.parent = parent;
      }
      field.id = field.name;
      flatStruct.push(field);
      if (field.content) {
        flatStruct = _.union(flatStruct, _flatTheStructure(field.content, field.name));
      }
    });

    return flatStruct;
  };

  const _completeResponse = function _completeResponse(response, model) {
    const formConfig = model._formConfiguration;

    response.data.view.documentData.document.properties.initid = model.get("initid");
    response.data.view.documentData.document.properties.id = model.get("initid");
    response.data.view.documentData.document.attributes = {};
    response.data.view.renderOptions.attributes = {};
    if (formConfig.renderOptions) {
      if (formConfig.renderOptions.fields) {
        response.data.view.renderOptions.attributes = formConfig.renderOptions.fields;
      }
    }

    response.data.view.menu = formConfig.menu || [];

    response.data.view.documentData.document.properties.title = formConfig.title || "";
    response.data.view.documentData.document.properties.family.title = formConfig.type || "";
  };

  return {
    smartFormSync: function smartFormSync(method, model, options) {
      if (method === "read") {
        if (!defaultRenderConfig) {
          $.getJSON("/api/v2/smart-forms/0/views/!defaultEdition")
            .then(response => {
              _completeResponse(response, model);

              defaultRenderConfig = response;
              options.success(response);
            })
            .fail(response => {
              options.error(response);
            });
        } else {
          _completeResponse(defaultRenderConfig, model);
          options.success(defaultRenderConfig);
        }
      }
    },

    getSmartFields: function getSmartFields(config) {
      let fields;
      const values = config.values || {};
      fields = _flatTheStructure(config.structure);

      fields.forEach(item => {
        item.visibility = item.visibility || "W";
        item.id = item.name;
        item.label = item.label || item.name;
        if (!item.id) {
          throw new Error("Field as no name: \n" + JSON.stringify(item, null, 2));
        }
        if (values[item.id] !== undefined) {
          if (_.isObject(values[item.id])) {
            item.attributeValue = values[item.id];
          } else {
            item.attributeValue = {
              value: values[item.id],
              displayValue: values[item.id]
            };
          }
        }
      });
      return fields;
    }
  };
});
