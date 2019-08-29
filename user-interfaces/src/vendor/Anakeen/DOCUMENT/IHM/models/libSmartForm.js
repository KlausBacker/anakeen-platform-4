define(["jquery", "underscore"], function libSmartForm($, _) {
  "use strict";
  let defaultRenderConfig;
  const _flatTheStructure = function _flatTheStructure(structure, parent) {
    let flatStruct = [];
    _.each(structure, function(field) {
      field.id = field.name;
      field.label = field.label || field.name;
      if (field.multiple === true) {
        field.options = field.options || {};
        field.options.multiple = "yes";
      }
      switch (field.display) {
        case "read":
          field.visibility = "S";
          break;
        case "write":
          field.visibility = "W";
          break;
        case "none":
          field.visibility = "H";
          break;
        default:
          field.visibility = "W";
      }
      if (parent) {
        field.parent = parent.id;
        if (!field.display && parent.display) {
          field.visibility = parent.visibility;
        }
        if (parent.type === "array") {
          field.multiple = true;
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
      }

      if (formConfig.renderOptions.common) {
        _.extend(response.data.view.renderOptions.common, formConfig.renderOptions.common);
      }
    }

    response.data.view.menu = formConfig.menu || [];

    response.data.view.documentData.document.properties.icon = formConfig.icon || "";
    response.data.view.documentData.document.properties.title = formConfig.title || "";
    response.data.view.documentData.document.properties.family.title = formConfig.type || "";
  };
  const normalizeSmartFieldValues = function normalizeSmartFieldValues(value) {
    if (_.isObject(value)) {
      return value;
    } else {
      return {
        value: value,
        displayValue: value
      };
    }
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
        if (!item.id) {
          throw new Error("Field as no name: \n" + JSON.stringify(item, null, 2));
        }
        if (values[item.id] !== undefined) {
          if (_.isArray(values[item.id])) {
            item.attributeValue = _.map(values[item.id], subItem => {
              return normalizeSmartFieldValues(subItem);
            });
          } else {
            item.attributeValue = normalizeSmartFieldValues(values[item.id]);
          }
        } else {
          if (item.multiple === true) {
            item.attributeValue = [];
          }
        }
      });
      return fields;
    }
  };
});
