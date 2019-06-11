define([
  "underscore",
  "dcpDocument/models/mAttribute",
  "dcpDocument/controllerObjects/constraintHandler"
], function require_mAttributeData(_, AttributeModel, ConstraintHandler) {
  "use strict";

  return AttributeModel.extend({
    typeModel: "ddui:attributeData",
    defaults: {
      isValueAttribute: true,
      multiple: false,
      attributeValue: null
    },

    initialize: function mAttributeinitialize() {
      this.listenTo(this, "change:attributeValue", this.checkConstraint);
      this._initialAttributeValue = this.get("attributeValue") || {
        value: null
      };
      AttributeModel.prototype.initialize.apply(this, arguments);
    },

    getValue: function mAttributegetValue() {
      return this.get("attributeValue");
    },
    setValue: function mAttributesetValue(value, index) {
      var currentValue, idx;
      if (this.get("multiple") && !_.isNumber(index) && !_.isArray(value)) {
        throw new Error(
          "You need to add an index to set value for a multiple id " + this.id
        );
      }
      if (this.get("multiple") && index >= 0) {
        currentValue = _.clone(this.get("attributeValue"));
        if (currentValue.length < index) {
          for (idx = currentValue.length; idx < index; idx++) {
            currentValue.push({ value: null, displayValue: "" });
          }
        }
        currentValue[index] = value;
        this.set("attributeValue", currentValue);
      } else {
        this.set("attributeValue", value);
      }
    },

    addValue: function mAttributeaddValue(value, index, options) {
      var currentValue;
      if (this.hasMultipleOption() && !_.isNumber(index) && !_.isArray(value)) {
        throw new Error(
          "You need to add an index or use array to set value for a multiple id " +
            this.id
        );
      }
      // clone array references
      currentValue = _.toArray(_.map(this.get("attributeValue"), _.clone));

      if (this.hasMultipleOption() && index >= 0) {
        //Init the multiple value if void
        if (!currentValue[index]) {
          currentValue[index] = [];
        }
        currentValue[index].push(value);
        this.set("attributeValue", currentValue, options);
      } else {
        currentValue.push(value);
        this.set("attributeValue", _.clone(currentValue), options);
      }
    },

    removeIndexValue: function mAttributeremoveIndexValue(index) {
      var currentValue, oldValue;
      if (!this.get("multiple") || !_.isNumber(index)) {
        throw new Error(
          "You need to add an index to set value for a multiple id " + this.id
        );
      }
      oldValue = this.get("attributeValue");
      currentValue = _.clone(this.get("attributeValue"));
      _.each(currentValue, function mAttributeRemoveIndex(value, currentIndex) {
        currentIndex = parseInt(currentIndex, 10);
        if (currentIndex === index) {
          delete currentValue[index];
        }
        if (currentIndex > index && oldValue[currentIndex]) {
          delete currentValue[currentIndex];
          currentValue[currentIndex - 1] = oldValue[currentIndex];
        }
      });
      currentValue = _.filter(currentValue, function removeUndefined(
        currentValue
      ) {
        return !_.isUndefined(currentValue);
      });
      this.set("attributeValue", currentValue, { notUpdateArray: true });
    },

    /**
     * Add an indexed value with or without default value
     * Used by attributes in array to add new line or duplicate line
     *
     * @param index
     * @param copy
     * @param updateArray set to true to resize array widget
     */
    createIndexedValue: function mAttributeCreateIndexedValue(
      index,
      copy,
      updateArray
    ) {
      var currentValue, defaultValue;
      var newValue;
      if (!this.get("multiple") || !_.isNumber(index)) {
        throw new Error(
          "You need to add an index to set value for a multiple id " + this.id
        );
      }
      currentValue = _.toArray(_.map(this.get("attributeValue"), _.clone));
      defaultValue = this.attributes.defaultValue;
      if (copy) {
        newValue = _.clone(currentValue[index]);
      } else if (defaultValue) {
        newValue = defaultValue;
      } else if (this.hasMultipleOption()) {
        newValue = [];
      } else {
        newValue = { value: null, displayValue: "" };
      }

      if (index > currentValue.length) {
        currentValue.push(newValue);
      } else {
        currentValue.splice(index, 0, newValue);
      }
      this.set("attributeValue", currentValue, {
        notUpdateArray: !updateArray
      });
    },

    /**
     * Add values to indexed element
     * Used by attributes in array to add new line or duplicate line
     *
     * @param newValue
     * @param index
     */
    addIndexedValue: function mAttributeAddIndexedValue(newValue, index) {
      var currentValue;
      if (!_.isNumber(index)) {
        throw new Error(
          "You need to add an index to set value indexed value " + this.id
        );
      }
      currentValue = _.toArray(_.map(this.get("attributeValue"), _.clone));

      if (index > currentValue.length) {
        currentValue.push(newValue);
      } else {
        currentValue.splice(index, 0, newValue);
      }
      this.set("attributeValue", currentValue);
    },

    /**
     * move a value in multiple value attribute
     * @param fromIndex
     * @param toIndex
     */
    moveIndexValue: function mAttributemoveIndexValue(fromIndex, toIndex) {
      var currentValue, fromValue;
      if (!this.get("multiple")) {
        throw new Error("Move only multiple attribute : " + this.id);
      }
      currentValue = _.toArray(this.get("attributeValue"));
      fromValue = _.clone(currentValue[fromIndex]);

      currentValue.splice(fromIndex, 1);
      currentValue.splice(toIndex, 0, fromValue);

      this.set("attributeValue", currentValue);
      this.trigger("moved", { from: fromIndex, to: toIndex });
    },

    getNbLines: function mAttributegetNbLines() {
      var nbLines = 0;
      if (!this.get("multiple")) {
        return -1;
      }
      _.each(this.get("attributeValue"), function mAttributeGetMaxLine(
        value,
        index
      ) {
        if (index > nbLines) {
          nbLines = index;
        }
      });
      return nbLines;
    },

    isInArray: function mAttributeisInArray() {
      var aparent = this.getParent();
      return (
        aparent && aparent.attributes && aparent.attributes.type === "array"
      );
    },

    checkConstraint: function mAttributecheckConstraint(config) {
      var response = new ConstraintHandler(),
        responseText = {};
      var scope = this;
      config = _.extend(
        { clearError: this.hasInternalError, displayError: true },
        config
      );
      this.trigger("constraint", {
        model: this,
        response: response,
        value: this.get("attributeValue")
      });
      if (response.hasConstraintMessages()) {
        _.each(
          response.getConstraintMessages(),
          function mAttributeData_checkConstraintElement(currentResponse) {
            responseText[currentResponse.index] =
              responseText[currentResponse.index] || "";
            responseText[currentResponse.index] +=
              currentResponse.message + " ";
          }
        );
        if (config.displayError) {
          this.hasInternalError = true;
          // Force redraw
          scope.setErrorMessage(null); // jshint ignore:line

          _.each(responseText, function mAttributeData_checkErrorMessage(
            text,
            index
          ) {
            index = parseInt(index);
            scope.setErrorMessage(text, index);
          });
        }
        return false;
      } else {
        if (config.clearError) {
          this.setErrorMessage(null);
          this.hasInternalError = false;
        }
        return true;
      }
    }
  });
});
