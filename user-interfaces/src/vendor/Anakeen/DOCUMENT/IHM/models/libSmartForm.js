define(["jquery", "underscore"], function libSmartForm($, _) {
  "use strict";
  let defaultRenderConfig;
  const _flatTheStructure = function _flatTheStructure(structure, parent) {
    let flatStruct = [];
    _.each(structure, function(field) {
      if (parent) {
        field.parent = parent.id;
        if (!field.visibility && parent.visibility) {
          field.visibility = parent.visibility;
        }
      }
      field.id = field.name;
      flatStruct.push(field);
      if (field.content) {
        flatStruct = _.union(flatStruct, _flatTheStructure(field.content, field));
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
      if (formConfig.renderOptions.types) {
        _.each(response.data.view.renderOptions.types, (item, key) => {
          if (formConfig.renderOptions.types[key]) {
            _.extend(item, formConfig.renderOptions.types[key]);
          }
        });

        if (formConfig.renderOptions.common) {
          _.extend(response.data.view.renderOptions.common, formConfig.renderOptions.common);
        }
      }
    }

    response.data.view.menu = formConfig.menu || [];

    response.data.view.documentData.document.properties.icon = formConfig.icon || "";
    response.data.view.documentData.document.properties.title = formConfig.title || "";
    response.data.view.documentData.document.properties.family.title = formConfig.type || "";
  };

  return {
    smartFormSync: function smartFormSync(method, model, options) {
      if (method === "read") {
        if (!defaultRenderConfig) {
          $.getJSON("/api/v2/smart-forms/0/views/!defaultEdition")
            .then(response => {
              defaultRenderConfig = JSON.stringify(response);
              _completeResponse(response, model);
              //Clone initial response
              options.success(response);
            })
            .fail(response => {
              options.error(response);
            });
        } else {
          let response = JSON.parse(defaultRenderConfig);
          _completeResponse(response, model);
          options.success(response);
        }
      }
    },

    getSmartFields: function getSmartFields(config) {
      let fields;
      const values = config.values || {};
      fields = _flatTheStructure(config.structure);

      fields.forEach(item => {
        item.id = item.name;
        switch (item.visibility) {
          case "read":
            item.visibility = "S";
            break;
          case "write":
            item.visibility = "W";
            break;
          case "hidden":
            item.visibility = "H";
            break;
          default:
            item.visibility = "W";
        }
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
