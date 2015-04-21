define([], function ()
{
    "use strict";

    return {
        "1081!coreConsultation": {
            "success": true,
            "messages": [],
            "data": {
                "uri": "\/dynacase\/api\/v1\/documents\/1081\/views\/!coreConsultation",
                "view": {
                    "renderLabel": "Vue de consultation par d\u00e9fault",
                    "menu": [{
                        "id": "modify",
                        "type": "itemMenu",
                        "label": "Modifier",
                        "htmlLabel": "",
                        "tooltipLabel": "Afficher le formulaire de modification",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "visible",
                        "beforeContent": "<div class=\"fa fa-pencil\" \/>",
                        "iconUrl": "",
                        "url": "#event\/document:edit",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": null,
                        "confirmationOptions": null
                    }, {
                        "id": "delete",
                        "type": "itemMenu",
                        "label": "Supprimer",
                        "htmlLabel": "",
                        "tooltipLabel": "Mettre le document \u00e0 la poubelle",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "visible",
                        "beforeContent": "<div class=\"fa fa-trash-o\" \/>",
                        "iconUrl": "",
                        "url": "#event\/document:delete",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": "\u00cates-vous s\u00fbr de vouloir supprimer \"Document de test contenant tous les attributs de Dynacase\" ?",
                        "confirmationOptions": {
                            "confirmButton": "Confirmer la suppression",
                            "cancelButton": "Annuler",
                            "title": "Confirmer la suppression de \"{{{document.properties.title}}}\"",
                            "windowWidth": "350px",
                            "windowHeight": "150px",
                            "modal": false
                        }
                    }, {
                        "id": "restore",
                        "type": "itemMenu",
                        "label": "Restaurer",
                        "htmlLabel": "",
                        "tooltipLabel": "Retirer le document de la poubelle",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "hidden",
                        "beforeContent": "",
                        "iconUrl": "",
                        "url": "#restore\/{{document.properties.id}}",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": null,
                        "confirmationOptions": null
                    }, {
                        "id": "historic",
                        "type": "itemMenu",
                        "label": "Historique",
                        "htmlLabel": "",
                        "tooltipLabel": "",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "visible",
                        "beforeContent": "<div class=\"fa fa-history\" \/>",
                        "iconUrl": "",
                        "url": "#event\/document:history",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": null,
                        "confirmationOptions": null
                    }, {
                        "id": "advanced",
                        "type": "listMenu",
                        "label": "Autres",
                        "htmlLabel": "",
                        "tooltipLabel": "",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "visible",
                        "beforeContent": "",
                        "iconUrl": "",
                        "content": [{
                            "id": "properties",
                            "type": "itemMenu",
                            "label": "Propri\u00e9t\u00e9s",
                            "htmlLabel": "",
                            "tooltipLabel": "",
                            "tooltipPlacement": "",
                            "htmlAttributes": "",
                            "visibility": "visible",
                            "beforeContent": "",
                            "iconUrl": "",
                            "url": "#event\/document:properties",
                            "target": "_self",
                            "targetOptions": null,
                            "confirmationText": null,
                            "confirmationOptions": null
                        }, {
                            "id": "security",
                            "type": "listMenu",
                            "label": "S\u00e9curit\u00e9",
                            "htmlLabel": "",
                            "tooltipLabel": "",
                            "tooltipPlacement": "",
                            "htmlAttributes": "",
                            "visibility": "visible",
                            "beforeContent": "",
                            "iconUrl": "",
                            "content": [{
                                "id": "lock",
                                "type": "itemMenu",
                                "label": "Verrouiller",
                                "htmlLabel": "",
                                "tooltipLabel": "Verrouiller le document",
                                "tooltipPlacement": "",
                                "htmlAttributes": "",
                                "visibility": "visible",
                                "beforeContent": "",
                                "iconUrl": "",
                                "url": "#event\/document:lock",
                                "target": "_self",
                                "targetOptions": null,
                                "confirmationText": null,
                                "confirmationOptions": null
                            }, {
                                "id": "unlock",
                                "type": "itemMenu",
                                "label": "D\u00e9verrouiller",
                                "htmlLabel": "",
                                "tooltipLabel": "D\u00e9verrouiller le document",
                                "tooltipPlacement": "",
                                "htmlAttributes": "",
                                "visibility": "hidden",
                                "beforeContent": "",
                                "iconUrl": "",
                                "url": "#event\/document:unlock",
                                "target": "_self",
                                "targetOptions": null,
                                "confirmationText": null,
                                "confirmationOptions": null
                            }]
                        }]
                    }],
                    "templates": {
                        "body": "{{> header}}\n\n{{> menu}}\n\n{{> content}}\n\n{{> footer}}\n",
                        "sections": {
                            "header": "<header class=\"dcpDocument__header {{#document.properties.security.readOnly}} dcpDocument__header--readonly {{\/document.properties.security.readOnly}}\">\n    <img class=\"dcpDocument__header__icon\" src=\"{{document.properties.icon}}\" alt=\"Document icon\"\/>\n    <a class=\"dcpDocument__header__title\" href=\"{{#document.properties.initid}}?app=DOCUMENT&id={{document.properties.id}}{{\/document.properties.initid}}{{^document.properties.initid}}?app=DOCUMENT&id={{document.properties.family.name}}&mode=create{{\/document.properties.initid}}\">{{document.properties.title}}<\/a>\n    <i style=\"display:none\" title=\"Formulaire en cours de modification\" class=\"dcpDocument__header__modified fa fa-asterisk\"><\/i>\n   {{#document.properties.security.lock.lockedBy.id}} <i title=\"Verrouill\u00e9 par <b>{{document.properties.security.lock.lockedBy.title}}<\/b>\" class=\"dcpDocument__header__lock {{#document.properties.security.lock.temporary}} dcpDocument__header__lock--temporary {{\/document.properties.security.lock.temporary}}fa fa-lock\"><\/i>{{\/document.properties.security.lock.lockedBy.id}}\n{{#document.properties.security.readOnly}}\n<span title=\"Lecture seule\" class=\"dcpDocument__header__readonly  fa-stack  text-danger\">\n  <i class=\"fa fa-pencil fa-stack-1x\"><\/i>\n  <i class=\"fa fa-ban fa-stack-1x fa-rotate-90\"><\/i>\n<\/span>\n\n {{\/document.properties.security.readOnly}}\n    <div class=\"dcpDocument__header__family\">{{document.properties.family.title}}<\/div>\n<\/header>\n",
                            "menu": "<nav class=\"dcpDocument__menu\"><\/nav>",
                            "content": "<section class=\"dcpDocument__body\"\/>",
                            "footer": "<footer class=\"dcpDocument__footer\"\/>"
                        },
                        "menu": {
                            "menu": "<div class=\"menu__content container-fluid\">\n   <div class=\"menu__header navbar-header\">\n\n    <\/div>\n    <div class=\"\" id=\"menu_{{uuid}}\">\n        <ul class=\"menu__content\">\n\n        <\/ul>\n    <\/div>\n<\/div>",
                            "itemMenu": "<li data-menu-id=\"{{id}}\"{{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    class=\"menu__element menu__element--item {{cssClass}}\"\n    {{# disabled}}disabled=\"disabled\"{{\/ disabled}}\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}}\n    >\n    <a {{# confirmationText}}class=\"menu--confirm\" data-confirm-message=\"{{confirmationText}}\"{{\/ confirmationText}}\n       {{# target}}target=\"{{target}}\"{{\/ target}}\n       data-url=\"{{url}}\" >\n        {{#iconUrl}}\n            <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n        {{\/iconUrl}}\n        {{#beforeContent}}\n            <span class=\"menu__before-content k-image\">\n                {{{beforeContent}}}\n            <\/span>\n        {{\/beforeContent}}\n        {{{htmlLabel}}}{{label}}\n    <\/a>\n<\/li>\n",
                            "listMenu": "<li data-menu-id=\"{{id}}\"\n    class=\"menu__element menu__element--list {{cssClass}}\"\n    {{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}}\n    {{# disabled}}disabled=\"disabled\"{{\/ disabled}} >\n    {{#iconUrl}}\n    <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n    {{\/iconUrl}}\n    {{#beforeContent}}\n    <span class=\"menu__before-content k-image\">\n        {{{beforeContent}}}\n    <\/span>\n    {{\/beforeContent}}\n    {{{htmlLabel}}}{{label}}\n    <ul class=\"listmenu__content\">\n    <\/ul>\n<\/li>",
                            "dynamicMenu": "<li data-menu-id=\"{{id}}\" data-menu-url=\"{{url}}\"\n    {{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    {{# disabled}}disabled=\"disabled\"{{\/ disabled}}\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}}\n    class=\"menu__element menu__element--dynamic {{cssClass}}\">\n    {{#iconUrl}}\n    <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n    {{\/iconUrl}}\n    {{#beforeContent}}\n    <span class=\"menu__before-content k-image\">\n        {{{beforeContent}}}\n    <\/span>\n    {{\/beforeContent}}\n    {{{htmlLabel}}}{{label}}\n    <ul class=\"listmenu__content\">\n    <\/ul>\n<\/li>",
                            "separatorMenu": "<li data-menu-id=\"{{id}}\" {{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    class=\"menu__element menu--separator {{cssClass}}\"\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}} >\n    {{#iconUrl}}\n    <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n    {{\/iconUrl}}\n    {{#beforeContent}}\n    <span class=\"menu__before-content k-image\">\n        {{{beforeContent}}}\n    <\/span>\n    {{\/beforeContent}}\n    {{{htmlLabel}}}\n    {{label}}\n    {{^label}}\n        {{^htmlLabel}}\n        <div class=\"menu__empty_separator\"><\/div>\n        {{\/htmlLabel}}\n    {{\/label}}\n<\/li>"
                        },
                        "attribute": {
                            "simpleWrapper": "<label class=\"dcpAttribute__left control-label dcpAttribute__label dcpAttribute__label--{{type}}\" data-attrid=\"{{id}}\" for=\"{{viewCid}}\"\/>\n<div class=\"dcpAttribute__right dcpAttribute__content dcpAttribute__content--{{type}}\" data-attrid=\"{{id}}\"\/>\n",
                            "default": {
                                "write": "<div class=\"{{#hadButtons}}input-group{{\/hadButtons}} margin-bottom-sm\">\n    {{#hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n                {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <input type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# renderOptions.maxLength}}maxlength=\"{{renderOptions.maxLength}}\"{{\/ renderOptions.maxLength}}\n           {{# renderOptions.placeHolder}}placeHolder=\"{{renderOptions.placeHolder}}\"{{\/ renderOptions.placeHolder}}\n           class=\"{{#hadButtons}}form-control{{\/hadButtons}} dcpAttribute__value dcpAttribute__value--edit\"\n           value=\"{{attributeValue.value}}\"\/>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n                class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" data-attrid=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{#attributeValue.formatValue}}{{{attributeValue.formatValue}}}{{\/attributeValue.formatValue}}{{^attributeValue.formatValue}}{{attributeValue.displayValue}}{{\/attributeValue.formatValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "label": "{{label}}",
                            "longtext": {
                                "write": "<div class=\"input-group margin-bottom-sm\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n              {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <textarea type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# renderOptions.maxLength}}maxlength=\"{{renderOptions.maxLength}}\"{{\/ renderOptions.maxLength}}\n           {{# renderOptions.placeHolder}}placeHolder=\"{{renderOptions.placeHolder}}\"{{\/ renderOptions.placeHolder}}\n          class=\"form-control dcpAttribute__value dcpAttribute__value--edit k-textbox\">{{attributeValue.value}}<\/textarea>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n            class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" data-attrid=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{#attributeValue.formatValue}}{{{attributeValue.formatValue}}}{{\/attributeValue.formatValue}}{{^attributeValue.formatValue}}{{attributeValue.displayValue}}{{\/attributeValue.formatValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "file": {
                                "write": "<div class=\"input-group margin-bottom-sm dcpAttribute__dragTarget\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n                {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <input type=\"file\" name=\"{{id}}\" id=\"{{viewCid}}\" style=\"display:none;\"\n           class=\"dcpAttribute__value--file\"\n           value=\"{{attributeValue.value}}\"\/>\n\n    <input {{#attributeValue.icon}}style=\"background-image:url({{attributeValue.icon}}){{\/attributeValue.icon}}\"\n           type=\"text\"\n           class=\"form-control dcpAttribute__value dcpAttribute__value--edit {{#attributeValue.icon}}dcpAttribute__value--fileicon{{\/attributeValue.icon}}\"\n           value=\"{{attributeValue.displayValue}}\"\/>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        <button type=\"button\"  data-index=\"{{index}}\"\n                class=\"dcpAttribute__content__button--file btn btn-default btn-xs\">\n            <span class=\"fa fa-download fa-fw\"><\/span>\n        <\/button>\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n                class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" name=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}\n        title=\"{{renderOptions.htmlLink.title}}\"\n        {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n    {{#attributeValue.icon}}\n        <img src=\"{{attributeValue.icon}}\"\/>\n    {{\/attributeValue.icon}}\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "enum": {
                                "write": "<div class=\"{{#hadButtons}}input-group{{\/hadButtons}} margin-bottom-sm\">\n    <input type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# options.size}}style=\"width : {{options.size}}em;\"{{\/ options.size}}\n           class=\"{{#hadButtons}}form-control{{\/hadButtons}} dcpAttribute__value dcpAttribute__value--edit\"\n           value=\"{{attributeValue.value}}\"\/>\n    {{#hadButtons}}\n        <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n            <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                    class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n            {{#deleteButton}}\n                <button type=\"button\" title=\"Suppression de : \"\n                        class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n                <\/button>\n            {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "writeRadio": "<div class=\"{{#hadButtons}}input-group{{\/hadButtons}} margin-bottom-sm\">\n    <div class=\"input-group k-textbox dcpAttribute__value--enumbuttons orientation-{{renderOptions.editDisplay}}\">\n    {{#enumValues}}\n        <label class=\"dcpAttribute__value--enumlabel{{#selected}} selected{{\/selected}}\">\n            <input name=\"{{id}}\"\n                   {{^isMultiple}}type=\"radio\"{{\/isMultiple}}{{#isMultiple}}type=\"checkbox\"{{\/isMultiple}}\n                   class=\"{{#hadButtons}}form-control{{\/hadButtons}} dcpAttribute__value dcpAttribute__value--edit\"\n                   value=\"{{value}}\" {{#selected}}checked=\"checked\"{{\/selected}}\/>\n            <div class=\"dcpAttribute__value--enumlabel--text\">{{displayValue}}<\/div>\n        <\/label>\n    {{\/enumValues}}\n    <\/div>\n    {{#hadButtons}}\n        <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n            <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                    class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n            {{#deleteButton}}\n                <button type=\"button\" title=\"Suppression de : \"\n                        class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n                <\/button>\n            {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}\"\n      name=\"{{id}}\">\n    {{#attributeValues}}\n        {{#renderOptions.htmlLink.url}}\n        <a  class=\"dcpAttribute__content__link\"\n            target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n            href=\"{{renderOptions.htmlLink.url}}\">\n        {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderOptions.htmlLink.url}}\n        <\/a>\n        {{\/renderOptions.htmlLink.url}}\n    {{\/attributeValues}}\n    {{{emptyValue}}}\n<\/span>\n\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "htmltext": {
                                "write": "<div class=\"input-group margin-bottom-sm\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n              {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <textarea type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# renderOptions.maxLength}}maxlength=\"{{renderOptions.maxLength}}\"{{\/ renderOptions.maxLength}}\n           {{# renderOptions.placeHolder}}placeHolder=\"{{renderOptions.placeHolder}}\"{{\/ renderOptions.placeHolder}}\n          class=\"form-control dcpAttribute__value dcpAttribute__value--edit k-textbox\">{{attributeValue.value}}<\/textarea>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n            class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" name=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{#attributeValue.formatValue}}{{{attributeValue.formatValue}}}{{\/attributeValue.formatValue}}{{^attributeValue.formatValue}}{{{attributeValue.displayValue}}}{{\/attributeValue.formatValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "docid": {
                                "write": "<div class=\"input-group margin-bottom-sm {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}{{^isMultiple}}dcpAttribute__value--single{{\/isMultiple}}\">\n    <span class=\"input-group-addon\">\n        <button\n            {{#renderOptions.autoCompleteHtmlLabel}}\n            title=\"{{renderOptions.autoCompleteHtmlLabel}}\"\n            {{\/renderOptions.autoCompleteHtmlLabel}}\n            class=\"dcpAttribute__value--docid--button btn btn-default btn-xs\">\n                {{^isMultiple}}\n                <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n                {{\/isMultiple}}\n                {{#isMultiple}}\n                <i class=\"fa fa-plus fa-fw\"><\/i>\n                {{\/isMultiple}}\n        <\/button>\n    <\/span>\n    <select name=\"{{id}}\" id=\"{{viewCid}}\"\n            class=\"form-control dcpAttribute__value dcpAttribute__value--docid\"\n            >\n    <\/select>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" data-index=\"{{index}}\" title=\"{{title}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">\n         {{{htmlContent}}}\n        <\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \" class=\"dcpAttribute__content__button--delete btn btn-default btn-xs\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value dcpAttribute__value--read dcpAttribute__value--docid\" name=\"{{id}}\">\n    {{#attributeValue.value}}\n        {{#renderOptions.htmlLink.renderUrl}}\n        <a class=\"dcpAttribute__content__link\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                        target=\"{{renderOptions.htmlLink.target}}\"\n                        href=\"{{renderOptions.htmlLink.renderUrl}}\">\n        {{\/renderOptions.htmlLink.renderUrl}}\n        <img class=\"dcpAttribute__value--icon\" src=\"{{attributeValue.icon}}\" \/>\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n        {{#renderOptions.htmlLink.renderUrl}}\n        <\/a>\n        {{\/renderOptions.htmlLink.renderUrl}}\n    {{\/attributeValue.value}}\n    {{{emptyValue}}}\n    {{#attributeValues}}\n        {{#renderUrl}}\n        <a class=\"dcpAttribute__content__link dcpAttribute__value--multiple\" data-index=\"{{index}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                  target=\"{{renderOptions.htmlLink.target}}\"\n                   href=\"{{renderUrl}}\">\n            {{\/renderUrl}}\n            <img class=\"dcpAttribute__value--icon\" src=\"{{icon}}\" \/>\n            <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderUrl}}\n        <\/a>\n        {{\/renderUrl}}\n    {{\/attributeValues}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "account": {
                                "write": "<div class=\"input-group margin-bottom-sm {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}{{^isMultiple}}dcpAttribute__value--single{{\/isMultiple}}\">\n    <span class=\"input-group-addon\">\n        <button\n            {{#renderOptions.autoCompleteHtmlLabel}}\n            title=\"{{renderOptions.autoCompleteHtmlLabel}}\"\n            {{\/renderOptions.autoCompleteHtmlLabel}}\n            class=\"dcpAttribute__value--docid--button btn btn-default btn-xs\">\n                {{^isMultiple}}\n                <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n                {{\/isMultiple}}\n                {{#isMultiple}}\n                <i class=\"fa fa-plus fa-fw\"><\/i>\n                {{\/isMultiple}}\n        <\/button>\n    <\/span>\n    <select name=\"{{id}}\" id=\"{{viewCid}}\"\n            class=\"form-control dcpAttribute__value dcpAttribute__value--docid\"\n            >\n    <\/select>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" data-index=\"{{index}}\" title=\"{{title}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">\n         {{{htmlContent}}}\n        <\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \" class=\"dcpAttribute__content__button--delete btn btn-default btn-xs\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value dcpAttribute__value--read dcpAttribute__value--docid\" name=\"{{id}}\">\n    {{#attributeValue.value}}\n        {{#renderOptions.htmlLink.renderUrl}}\n        <a class=\"dcpAttribute__content__link\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                        target=\"{{renderOptions.htmlLink.target}}\"\n                        href=\"{{renderOptions.htmlLink.renderUrl}}\">\n        {{\/renderOptions.htmlLink.renderUrl}}\n        <img class=\"dcpAttribute__value--icon\" src=\"{{attributeValue.icon}}\" \/>\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n        {{#renderOptions.htmlLink.renderUrl}}\n        <\/a>\n        {{\/renderOptions.htmlLink.renderUrl}}\n    {{\/attributeValue.value}}\n    {{{emptyValue}}}\n    {{#attributeValues}}\n        {{#renderUrl}}\n        <a class=\"dcpAttribute__content__link dcpAttribute__value--multiple\" data-index=\"{{index}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                  target=\"{{renderOptions.htmlLink.target}}\"\n                   href=\"{{renderUrl}}\">\n            {{\/renderUrl}}\n            <img class=\"dcpAttribute__value--icon\" src=\"{{icon}}\" \/>\n            <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderUrl}}\n        <\/a>\n        {{\/renderUrl}}\n    {{\/attributeValues}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "thesaurus": {
                                "write": "<div class=\"input-group margin-bottom-sm {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}{{^isMultiple}}dcpAttribute__value--single{{\/isMultiple}}\">\n    <span class=\"input-group-addon\">\n        <button\n            {{#renderOptions.autoCompleteHtmlLabel}}\n            title=\"{{renderOptions.autoCompleteHtmlLabel}}\"\n            {{\/renderOptions.autoCompleteHtmlLabel}}\n            class=\"dcpAttribute__value--docid--button btn btn-default btn-xs\">\n                {{^isMultiple}}\n                <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n                {{\/isMultiple}}\n                {{#isMultiple}}\n                <i class=\"fa fa-plus fa-fw\"><\/i>\n                {{\/isMultiple}}\n        <\/button>\n    <\/span>\n    <select name=\"{{id}}\" id=\"{{viewCid}}\"\n            class=\"form-control dcpAttribute__value dcpAttribute__value--docid\"\n            >\n    <\/select>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" data-index=\"{{index}}\" title=\"{{title}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">\n         {{{htmlContent}}}\n        <\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \" class=\"dcpAttribute__content__button--delete btn btn-default btn-xs\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value dcpAttribute__value--read dcpAttribute__value--docid\" name=\"{{id}}\">\n    {{#attributeValue.value}}\n        {{#renderOptions.htmlLink.renderUrl}}\n        <a class=\"dcpAttribute__content__link\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                        target=\"{{renderOptions.htmlLink.target}}\"\n                        href=\"{{renderOptions.htmlLink.renderUrl}}\">\n        {{\/renderOptions.htmlLink.renderUrl}}\n        <img class=\"dcpAttribute__value--icon\" src=\"{{attributeValue.icon}}\" \/>\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n        {{#renderOptions.htmlLink.renderUrl}}\n        <\/a>\n        {{\/renderOptions.htmlLink.renderUrl}}\n    {{\/attributeValue.value}}\n    {{{emptyValue}}}\n    {{#attributeValues}}\n        {{#renderUrl}}\n        <a class=\"dcpAttribute__content__link dcpAttribute__value--multiple\" data-index=\"{{index}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                  target=\"{{renderOptions.htmlLink.target}}\"\n                   href=\"{{renderUrl}}\">\n            {{\/renderUrl}}\n            <img class=\"dcpAttribute__value--icon\" src=\"{{icon}}\" \/>\n            <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderUrl}}\n        <\/a>\n        {{\/renderUrl}}\n    {{\/attributeValues}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "image": {
                                "write": "<div class=\"input-group margin-bottom-sm dcpAttribute__dragTarget\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n              {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <input type=\"file\" name=\"{{id}}\" id=\"{{viewCid}}\" style=\"display:none\" accept=\"image\/*\"\n           class=\"dcpAttribute__value--file\"\n          value=\"{{attributeValue.value}}\"\/>\n    <input {{#attributeValue.thumbnail}}style=\"background-image:url({{attributeValue.thumbnail}}){{\/attributeValue.thumbnail}}\"  type=\"text\"\n           class=\"form-control dcpAttribute__value dcpAttribute__value--edit {{#attributeValue.thumbnail}}dcpAttribute__value--filethumb{{\/attributeValue.thumbnail}}\"\n           value=\"{{attributeValue.displayValue}}\"\/>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        <button type=\"button\"  data-index=\"{{index}}\" class=\"dcpAttribute__content__button--file btn btn-default btn-xs\">\n            <span class=\"fa fa-download fa-fw\"><\/span>\n        <\/button>\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n                class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" name=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n    {{^attributeValue.thumbnail}}\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n    {{\/attributeValue.thumbnail}}\n    {{#attributeValue.thumbnail}}\n        <img src=\"{{attributeValue.thumbnail}}\"\/>\n    {{\/attributeValue.thumbnail}}\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "frame": {
                                "label": "<div class=\"panel-heading css-frame-head dcpFrame__label dcp__frame--open dcpLabel\" data-id=\"{{id}}\">\n    <span class=\"dcp__frame__caret fa fa-caret-down fa-lg\"><\/span>\n    {{label}}\n<\/div>",
                                "content": "<div class=\"panel-body dcpFrame__content dcpFrame__content--open\" data-attrid=\"{{id}}\">\n<\/div>"
                            },
                            "array": {
                                "label": "<div class=\"panel-heading dcpArray__label dcpLabel\" data-attrid=\"{{id}}\">\n    <span class=\"dcp__array__caret fa fa-caret-down fa-lg\"><\/span>\n    {{label}}\n    {{#displayCount}}<span class=\"dcpArray__label--count\">{{nbLines}}<\/span>{{\/displayCount}}\n<\/div>",
                                "content": "<div class=\"panel-body dcpArray__content dcpArray__content--open\" data-attrid=\"{{id}}\">\n    <table class=\"table table-condensed table-hover table-bordered responsive\">\n        <thead data-attrid=\"{{id}}\">\n        <tr class=\"dcpArray__head\">\n            {{#tools}}\n                <th class=\"dcpArray__head__toolCell\"><\/th>\n            {{\/tools}}\n            {{#content}}\n                <th class=\"dcpArray__head__cell dcpLabel dcpArray__head__cell--{{type}} {{#needed}}dcpAttribute--needed{{\/needed}}\" data-attrid=\"{{id}}\">{{label}}<\/th>\n            {{\/content}}\n        <\/tr>\n        <\/thead>\n        <tbody class=\"dcpArray__body\" data-attrid=\"{{id}}\">\n\n        <\/tbody>\n    <\/table>\n    <div class=\"dcpArray__tools\">\n        {{#tools}}\n        <div class=\"dcpArray__button dcpArray__button--add\" title=\"Ajouter une nouvelle ligne\">\n        <button type=\"button\" class=\"btn btn-default dcpArray__add\">\n            <span class=\"glyphicon glyphicon-plus-sign\"><\/span>\n        <\/button><\/div>\n        <div class=\"dcpArray__button dcpArray__button--copy\" title=\"Dupliquer la ligne s\u00e9lectionn\u00e9e\">\n        <button disabled=\"disabled\" type=\"button\"\n                class=\"btn btn-default dcpArray__copy\">\n            <span class=\"glyphicon glyphicon-sound-dolby\"><\/span>\n        <\/button><\/div>\n        {{\/tools}}\n    <\/div>\n<\/div>",
                                "line": "<tr class=\"dcpArray__content__line\" data-attrid=\"{{id}}\" data-line=\"{{lineNumber}}\">\n    {{#tools}}\n    <td class=\"dcpArray__content__toolCell\">\n        <span title=\"Cliquer pour d\u00e9placer la ligne\" class=\"dcpArray__content__toolCell__dragDrop\">\n            <button class=\"btn btn-default btn-xs\"><span class=\"fa fa-ellipsis-v\"><\/span><\/button>\n        <\/span>\n        <span title=\"S\u00e9lectionner la ligne\" class=\"dcpArray__content__toolCell__check\">\n            <input name=\"check_{{id}}\" type=\"radio\" \/>\n\n        <\/span>\n        <span title=\"Supprimer la ligne\" class=\"dcpArray__content__toolCell__delete\">\n            <button class=\"btn btn-default btn-xs\">\n                <span class=\"fa fa-trash-o\"><\/span>\n            <\/button>\n        <\/span>\n    <\/td>\n    {{\/tools}}\n{{#content}}\n    <td class=\"dcpAttribute__content dcpAttribute__content--{{type}} dcpArray__content__cell dcpArray__content__cell--{{type}}\" data-attrid=\"{{id}}\"><\/td>\n{{\/content}}\n<\/tr>"
                            }
                        },
                        "window": {"confirm": "<div class=\"confirm--body\">\n    <div class=\"confirm--content\">\n        <div>{{messages.textMessage}}<\/div>\n        <div>{{{messages.htmlMessage}}}<\/div>\n\n    <\/div>\n    <div class=\"confirm--buttons\">\n        <button class=\"button--cancel\" type=\"button\">{{messages.cancelMessage}}<\/button>\n        <button class=\"button--ok k-primary\" type=\"button\">{{messages.okMessage}}<\/button>\n    <\/div>\n<\/div>\n\n"}
                    },
                    "renderOptions": {
                        "common": {
                            "showEmptyContent": null,
                            "labelPosition": "auto",
                            "autoCompleteHtmlLabel": "",
                            "inputHtmlTooltip": "",
                            "htmlLink": {
                                "target": "_self",
                                "windowWidth": "300px",
                                "windowHeight": "200px",
                                "windowTitle": "",
                                "title": "",
                                "url": ""
                            },
                            "labels": {"closeErrorMessage": "Fermer le message"}
                        },
                        "types": {
                            "account": {
                                "noAccessText": "Acc\u00e8s interdit au compte",
                                "htmlLink": {
                                    "target": "_render",
                                    "windowWidth": "300px",
                                    "windowHeight": "200px",
                                    "windowTitle": "",
                                    "title": "Voir {{{displayValue}}}",
                                    "url": "?app=DOCUMENT&id={{value}}"
                                }
                            },
                            "date": {"labels": {"invalidDate": "Date invalide"}},
                            "docid": {
                                "noAccessText": "Information non accessible",
                                "htmlLink": {
                                    "target": "_render",
                                    "windowWidth": "300px",
                                    "windowHeight": "200px",
                                    "windowTitle": "",
                                    "title": "Voir {{{displayValue}}}",
                                    "url": "?app=DOCUMENT&id={{value}}"
                                }
                            },
                            "enum": {
                                "boolColor": "",
                                "editDisplay": "list",
                                "useFirstChoice": false,
                                "useSourceUri": false,
                                "labels": {
                                    "chooseMessage": "Choisissez",
                                    "invalidEntry": "Entr\u00e9e invalide",
                                    "invertSelection": "Cliquer pour r\u00e9pondre \"{{displayValue}}\"",
                                    "selectMessage": "S\u00e9lectionner",
                                    "unselectMessage": "D\u00e9s\u00e9lectionner"
                                }
                            },
                            "file": {
                                "downloadInline": false,
                                "labels": {
                                    "dropFileHere": "D\u00e9poser le fichier ici",
                                    "placeHolder": "Cliquez pour choisir un fichier",
                                    "tooltipLabel": "Choisissez un fichier",
                                    "downloadLabel": "T\u00e9l\u00e9charger le fichier",
                                    "kiloByte": "ko",
                                    "byte": "octets",
                                    "recording": "Enregistrement",
                                    "transferring": "T\u00e9l\u00e9versement de"
                                }
                            },
                            "image": {
                                "htmlLink": {
                                    "target": "_dialog",
                                    "windowWidth": "400px",
                                    "windowHeight": "300px",
                                    "windowTitle": "",
                                    "title": "",
                                    "url": ""
                                },
                                "downloadInline": true,
                                "thumbnailWidth": 48,
                                "labels": {
                                    "dropFileHere": "D\u00e9poser l'image ici",
                                    "placeHolder": "Cliquez pour choisir une image",
                                    "tooltipLabel": "Choisissez une image",
                                    "downloadLabel": "T\u00e9l\u00e9charger l'image",
                                    "kiloByte": "ko",
                                    "recording": "Enregistrement",
                                    "transferring": "T\u00e9l\u00e9versement de"
                                }
                            },
                            "htmltext": {"toolbar": "Simple", "toolbarStartupExpanded": true, "height": "120px"},
                            "longtext": {"displayedLineNumber": 0},
                            "int": {"max": 2147483647, "min": -2147483647},
                            "double": {"max": null, "min": null, "decimalPrecision": null},
                            "money": {"max": null, "min": null, "decimalPrecision": 2, "currency": "\u20ac"},
                            "text": {"maxLength": null, "format": "{{displayValue}}"},
                            "array": {
                                "rowCountThreshold": -1,
                                "labels": {
                                    "limitMaxMessage": "Le nombre maximum de rang\u00e9e est de {{limit}}",
                                    "limitMinMessage": "Le nombre de rang\u00e9es minimum est de {{limit}}"
                                }
                            },
                            "time": [],
                            "timestamp": [],
                            "thesaurus": {
                                "htmlLink": {
                                    "target": "_render",
                                    "windowWidth": "300px",
                                    "windowHeight": "200px",
                                    "windowTitle": "",
                                    "title": "Voir {{{displayValue}}}",
                                    "url": "?app=DOCUMENT&id={{value}}"
                                }
                            }
                        },
                        "mode": "view",
                        "attributes": {"zoo_t_tab": {"openFirst": true}},
                        "visibilities": {
                            "zoo_frame_relation": "W",
                            "zoo_t_tab_numbers": "W",
                            "zoo_t_tab_relations": "W",
                            "zoo_frame_date": "W",
                            "zoo_t_tab_date": "W",
                            "zoo_frame_numbers": "W",
                            "zoo_t_tab_misc": "W",
                            "zoo_t_tab_texts": "W",
                            "zoo_frame_texts": "W",
                            "zoo_frame_files": "W",
                            "zoo_t_tab_files": "W",
                            "zoo_fr_enumservermultiple": "W",
                            "zoo_frame_misc": "W",
                            "zoo_fr_date": "W",
                            "zoo_fr_number": "W",
                            "zoo_fr_rels": "W",
                            "zoo_t_tab": "W",
                            "zoo_f_title": "W",
                            "zoo_fr_enummultiple": "W",
                            "zoo_fr_misc": "W",
                            "zoo_fr_enumserversimple": "W",
                            "zoo_fr_file": "W",
                            "zoo_fr_enumsimple": "W",
                            "zoo_t_tab_enums": "W",
                            "zoo_fr_text": "W",
                            "zoo_title": "W",
                            "zoo_account": "W",
                            "zoo_account_multiple": "W",
                            "zoo_docid": "W",
                            "zoo_docid_multiple": "W",
                            "zoo_date": "W",
                            "zoo_time": "W",
                            "zoo_timestamp": "W",
                            "zoo_integer": "W",
                            "zoo_double": "W",
                            "zoo_money": "W",
                            "zoo_password": "W",
                            "zoo_color": "W",
                            "zoo_file": "W",
                            "zoo_image": "W",
                            "zoo_htmltext": "W",
                            "zoo_longtext": "W",
                            "zoo_text": "W",
                            "zoo_enumlist": "W",
                            "zoo_enumauto": "W",
                            "zoo_enumvertical": "W",
                            "zoo_enumhorizontal": "W",
                            "zoo_enumbool": "W",
                            "zoo_enumserverlist": "W",
                            "zoo_enumserverauto": "W",
                            "zoo_enumserververtical": "W",
                            "zoo_enumserverhorizontal": "W",
                            "zoo_enumserverbool": "W",
                            "zoo_enumslist": "W",
                            "zoo_enumsauto": "W",
                            "zoo_enumsvertical": "W",
                            "zoo_enumshorizontal": "W",
                            "zoo_enumsserverlist": "W",
                            "zoo_enumsserverauto": "W",
                            "zoo_enumsserververtical": "W",
                            "zoo_enumsserverhorizontal": "W",
                            "zoo_array_dates": "W",
                            "zoo_date_array": "W",
                            "zoo_time_array": "W",
                            "zoo_timestamp_array": "W",
                            "zoo_array_docid": "W",
                            "zoo_docid_array": "W",
                            "zoo_docid_multiple_array": "W",
                            "zoo_array_account": "W",
                            "zoo_account_array": "W",
                            "zoo_account_multiple_array": "W",
                            "zoo_array_numbers": "W",
                            "zoo_double_array": "W",
                            "zoo_integer_array": "W",
                            "zoo_money_array": "W",
                            "zoo_array_misc": "W",
                            "zoo_enum_array": "W",
                            "zoo_enums_array": "W",
                            "zoo_color_array": "W",
                            "zoo_password_array": "W",
                            "zoo_array_files": "W",
                            "zoo_file_array": "W",
                            "zoo_image_array": "W",
                            "zoo_array_texts": "W",
                            "zoo_text_array": "W",
                            "zoo_longtext_array": "W",
                            "zoo_array_html": "W",
                            "zoo_htmltext_array": "W"
                        },
                        "needed": []
                    },
                    "documentData": {
                        "document": {
                            "properties": {
                                "id": 1081,
                                "title": "Document de test contenant tous les attributs de Dynacase",
                                "family": {
                                    "title": "Test tout type",
                                    "name": "ZOO_ALLTYPE",
                                    "id": 1067,
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=24"
                                },
                                "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=24",
                                "revision": 0,
                                "security": {
                                    "lock": {"id": 0},
                                    "readOnly": false,
                                    "fixed": false,
                                    "profil": {"id": 0, "title": ""},
                                    "confidentiality": "public"
                                },
                                "status": "alive",
                                "initid": 1081
                            },
                            "attributes": {
                                "zoo_title": {
                                    "value": "Document de test contenant tous les attributs de Dynacase",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                },
                                "zoo_account": {"value": null, "displayValue": null},
                                "zoo_account_multiple": [],
                                "zoo_docid": {
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                },
                                "zoo_docid_multiple": [{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }],
                                "zoo_date": {"value": "2015-04-06", "displayValue": "06\/04\/2015"},
                                "zoo_time": {"value": "12:00:00", "displayValue": "12:00:00"},
                                "zoo_timestamp": {"value": "2015-04-22 15:13:00", "displayValue": "22\/04\/2015 15:13"},
                                "zoo_integer": {"value": 42, "displayValue": "42"},
                                "zoo_double": {"value": 12.15, "displayValue": "12.15"},
                                "zoo_money": {"value": 12000, "displayValue": "12000"},
                                "zoo_password": {"value": "p@ssw0rd", "displayValue": "p@ssw0rd"},
                                "zoo_color": {"value": "#804AFF", "displayValue": "#804AFF"},
                                "zoo_file": {
                                    "size": "21501",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "1511__880.jpg",
                                    "url": "file\/1081\/14\/zoo_file\/-1\/1511__880.jpg?cache=no&inline=yes",
                                    "mime": "image\/jpeg",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=20",
                                    "value": "image\/jpeg; charset=binary|14|1511__880.jpg",
                                    "displayValue": "1511__880.jpg"
                                },
                                "zoo_image": {
                                    "thumbnail": "file\/1081\/15\/zoo_image\/-1\/eiffel.jpg?cache=no&inline=yes&width=48",
                                    "size": "60052",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "eiffel.jpg",
                                    "url": "file\/1081\/15\/zoo_image\/-1\/eiffel.jpg?cache=no&inline=yes",
                                    "mime": "image\/jpeg",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
                                    "value": "image\/jpeg; charset=binary|15|eiffel.jpg",
                                    "displayValue": "eiffel.jpg"
                                },
                                "zoo_htmltext": {
                                    "value": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p> <p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p> <p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p> <p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p> <p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>",
                                    "displayValue": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p> <p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p> <p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p> <p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p> <p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>"
                                },
                                "zoo_longtext": {
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                },
                                "zoo_text": {
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                },
                                "zoo_enumlist": {"value": "AE", "displayValue": "Emirats Arabes unis"},
                                "zoo_enumauto": {"value": "AG", "displayValue": "Antigua et Barbade"},
                                "zoo_enumvertical": {"value": "70", "displayValue": "70 %"},
                                "zoo_enumhorizontal": {"value": "yellow", "displayValue": "Jaune"},
                                "zoo_enumbool": {"value": "C", "displayValue": "Critique"},
                                "zoo_enumserverlist": {"value": "AG", "displayValue": "Antigua et Barbade"},
                                "zoo_enumserverauto": {"value": "BR", "displayValue": "Br\u00e9sil"},
                                "zoo_enumserververtical": {"value": "lightblue", "displayValue": "Bleu\/Bleu ciel"},
                                "zoo_enumserverhorizontal": {"value": "100", "displayValue": "100 %"},
                                "zoo_enumserverbool": {"value": "C", "displayValue": "Critique"},
                                "zoo_enumslist": [{"value": "AD", "displayValue": "Andorre"}, {
                                    "value": "BO",
                                    "displayValue": "Bolivie"
                                }, {"value": "BR", "displayValue": "Br\u00e9sil"}],
                                "zoo_enumsauto": [{"value": "AF", "displayValue": "Afghanistan"}],
                                "zoo_enumsvertical": [{"value": "30", "displayValue": "30 %"}],
                                "zoo_enumshorizontal": [{"value": "red", "displayValue": "Rouge"}],
                                "zoo_enumsserverlist": [{"value": "BG", "displayValue": "Bulgarie"}, {
                                    "value": "BW",
                                    "displayValue": "Botswana"
                                }],
                                "zoo_enumsserverauto": [{"value": "BT", "displayValue": "Bhoutan"}, {
                                    "value": "GP",
                                    "displayValue": "Guadeloupe"
                                }],
                                "zoo_enumsserververtical": [{"value": "100", "displayValue": "100 %"}],
                                "zoo_enumsserverhorizontal": [{"value": "blue", "displayValue": "Bleu"}],
                                "zoo_date_array": [{
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }],
                                "zoo_time_array": [{"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}],
                                "zoo_timestamp_array": [{
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {"value": "2015-04-12 15:13", "displayValue": "12\/04\/2015 15:13"}],
                                "zoo_docid_array": [{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }, {
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": null,
                                    "icon": null,
                                    "value": null,
                                    "displayValue": null
                                }],
                                "zoo_docid_multiple_array": [[{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }], [{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }]],
                                "zoo_account_array": [],
                                "zoo_account_multiple_array": [],
                                "zoo_double_array": [{"value": 12, "displayValue": "12"}],
                                "zoo_integer_array": [{"value": 12, "displayValue": "12"}],
                                "zoo_money_array": [{"value": 12, "displayValue": "12"}],
                                "zoo_enum_array": [{"value": "70", "displayValue": "70 %"}],
                                "zoo_enums_array": [[{"value": "70", "displayValue": "70 %"}]],
                                "zoo_color_array": [{"value": "#DBFF9C", "displayValue": "#DBFF9C"}],
                                "zoo_password_array": [{"value": "p@ssw0rd", "displayValue": "p@ssw0rd"}],
                                "zoo_file_array": [{
                                    "size": 0,
                                    "creationDate": "",
                                    "fileName": "",
                                    "url": "",
                                    "mime": "",
                                    "icon": "",
                                    "value": null,
                                    "displayValue": null
                                }, {
                                    "size": 0,
                                    "creationDate": "",
                                    "fileName": "",
                                    "url": "",
                                    "mime": "",
                                    "icon": "",
                                    "value": null,
                                    "displayValue": null
                                }],
                                "zoo_image_array": [{
                                    "thumbnail": "file\/1081\/16\/zoo_image_array\/0\/vintage-rainbow-color-badge.png?cache=no&inline=yes&width=48",
                                    "size": "60330",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "vintage-rainbow-color-badge.png",
                                    "url": "file\/1081\/16\/zoo_image_array\/0\/vintage-rainbow-color-badge.png?cache=no&inline=yes",
                                    "mime": "image\/png",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image2.png&size=24",
                                    "value": "image\/png; charset=binary|16|vintage-rainbow-color-badge.png",
                                    "displayValue": "vintage-rainbow-color-badge.png"
                                }, {
                                    "thumbnail": "file\/1081\/17\/zoo_image_array\/1\/retour%20vers%20le%20futur.jpg?cache=no&inline=yes&width=48",
                                    "size": "73490",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "retour vers le futur.jpg",
                                    "url": "file\/1081\/17\/zoo_image_array\/1\/retour%20vers%20le%20futur.jpg?cache=no&inline=yes",
                                    "mime": "image\/jpeg",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
                                    "value": "image\/jpeg; charset=binary|17|retour vers le futur.jpg",
                                    "displayValue": "retour vers le futur.jpg"
                                }],
                                "zoo_text_array": [{
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                }],
                                "zoo_longtext_array": [{
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                }],
                                "zoo_htmltext_array": [{
                                    "value": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>",
                                    "displayValue": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>"
                                }, {
                                    "value": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>",
                                    "displayValue": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>"
                                }]
                            },
                            "uri": "\/dynacase\/api\/v1\/documents\/1081.json"
                        },
                        "family": {
                            "structure": {
                                "zoo_f_title": {
                                    "id": "zoo_f_title",
                                    "visibility": "W",
                                    "label": "Titre",
                                    "type": "frame",
                                    "logicalOrder": 0,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_title": {
                                            "id": "zoo_title",
                                            "visibility": "W",
                                            "label": "Le titre",
                                            "type": "text",
                                            "logicalOrder": 1,
                                            "multiple": false,
                                            "options": [],
                                            "needed": false
                                        }
                                    }
                                },
                                "zoo_t_tab": {
                                    "id": "zoo_t_tab",
                                    "visibility": "W",
                                    "label": "Basiques",
                                    "type": "tab",
                                    "logicalOrder": 2,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_fr_rels": {
                                            "id": "zoo_fr_rels",
                                            "visibility": "W",
                                            "label": "Relations",
                                            "type": "frame",
                                            "logicalOrder": 3,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_account": {
                                                    "id": "zoo_account",
                                                    "visibility": "W",
                                                    "label": "Un compte",
                                                    "type": "account",
                                                    "logicalOrder": 4,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "helpOutputs": ["zoo_account"]
                                                },
                                                "zoo_account_multiple": {
                                                    "id": "zoo_account_multiple",
                                                    "visibility": "W",
                                                    "label": "Des comptes",
                                                    "type": "account",
                                                    "logicalOrder": 5,
                                                    "multiple": true,
                                                    "options": {"multiple": "yes"},
                                                    "needed": false,
                                                    "helpOutputs": ["zoo_account_multiple"]
                                                },
                                                "zoo_docid": {
                                                    "id": "zoo_docid",
                                                    "visibility": "W",
                                                    "label": "Un document",
                                                    "type": "docid",
                                                    "logicalOrder": 6,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_docid_multiple": {
                                                    "id": "zoo_docid_multiple",
                                                    "visibility": "W",
                                                    "label": "Des documents",
                                                    "type": "docid",
                                                    "logicalOrder": 7,
                                                    "multiple": true,
                                                    "options": {"multiple": "yes"},
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_date": {
                                            "id": "zoo_fr_date",
                                            "visibility": "W",
                                            "label": "Le temps",
                                            "type": "frame",
                                            "logicalOrder": 8,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_date": {
                                                    "id": "zoo_date",
                                                    "visibility": "W",
                                                    "label": "Une date",
                                                    "type": "date",
                                                    "logicalOrder": 9,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_time": {
                                                    "id": "zoo_time",
                                                    "visibility": "W",
                                                    "label": "Une heure",
                                                    "type": "time",
                                                    "logicalOrder": 10,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_timestamp": {
                                                    "id": "zoo_timestamp",
                                                    "visibility": "W",
                                                    "label": "Une date avec  une heure",
                                                    "type": "timestamp",
                                                    "logicalOrder": 11,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_number": {
                                            "id": "zoo_fr_number",
                                            "visibility": "W",
                                            "label": "Les nombres",
                                            "type": "frame",
                                            "logicalOrder": 12,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_integer": {
                                                    "id": "zoo_integer",
                                                    "visibility": "W",
                                                    "label": "Un entier",
                                                    "type": "int",
                                                    "logicalOrder": 13,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_double": {
                                                    "id": "zoo_double",
                                                    "visibility": "W",
                                                    "label": "Un d\u00e9cimal",
                                                    "type": "double",
                                                    "logicalOrder": 14,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_money": {
                                                    "id": "zoo_money",
                                                    "visibility": "W",
                                                    "label": "Un sous",
                                                    "type": "money",
                                                    "logicalOrder": 15,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_misc": {
                                            "id": "zoo_fr_misc",
                                            "visibility": "W",
                                            "label": "Divers",
                                            "type": "frame",
                                            "logicalOrder": 16,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_password": {
                                                    "id": "zoo_password",
                                                    "visibility": "W",
                                                    "label": "Un mot de passe",
                                                    "type": "password",
                                                    "logicalOrder": 17,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_color": {
                                                    "id": "zoo_color",
                                                    "visibility": "W",
                                                    "label": "Une couleur",
                                                    "type": "color",
                                                    "logicalOrder": 18,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_file": {
                                            "id": "zoo_fr_file",
                                            "visibility": "W",
                                            "label": "Fichiers & images",
                                            "type": "frame",
                                            "logicalOrder": 19,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_file": {
                                                    "id": "zoo_file",
                                                    "visibility": "W",
                                                    "label": "Un fichier",
                                                    "type": "file",
                                                    "logicalOrder": 20,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_image": {
                                                    "id": "zoo_image",
                                                    "visibility": "W",
                                                    "label": "Une image",
                                                    "type": "image",
                                                    "logicalOrder": 21,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_text": {
                                            "id": "zoo_fr_text",
                                            "visibility": "W",
                                            "label": "Les textes",
                                            "type": "frame",
                                            "logicalOrder": 22,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_htmltext": {
                                                    "id": "zoo_htmltext",
                                                    "visibility": "W",
                                                    "label": "Un texte formart\u00e9",
                                                    "type": "htmltext",
                                                    "logicalOrder": 23,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_longtext": {
                                                    "id": "zoo_longtext",
                                                    "visibility": "W",
                                                    "label": "Un texte multiligne",
                                                    "type": "longtext",
                                                    "logicalOrder": 24,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_text": {
                                                    "id": "zoo_text",
                                                    "visibility": "W",
                                                    "label": "Un texte simple",
                                                    "type": "text",
                                                    "logicalOrder": 25,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_enums": {
                                    "id": "zoo_t_tab_enums",
                                    "visibility": "W",
                                    "label": "Les \u00e9num\u00e9r\u00e9s",
                                    "type": "tab",
                                    "logicalOrder": 26,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_fr_enumsimple": {
                                            "id": "zoo_fr_enumsimple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s directs simple",
                                            "type": "frame",
                                            "logicalOrder": 27,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumlist": {
                                                    "id": "zoo_enumlist",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 liste",
                                                    "type": "enum",
                                                    "logicalOrder": 28,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumlist"
                                                },
                                                "zoo_enumauto": {
                                                    "id": "zoo_enumauto",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 auto",
                                                    "type": "enum",
                                                    "logicalOrder": 29,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumauto"
                                                },
                                                "zoo_enumvertical": {
                                                    "id": "zoo_enumvertical",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 30,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "0", "label": "0 %"}, {
                                                        "key": "30",
                                                        "label": "30 %"
                                                    }, {"key": "70", "label": "70 %"}, {
                                                        "key": "100",
                                                        "label": "100 %"
                                                    }],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumvertical"
                                                },
                                                "zoo_enumhorizontal": {
                                                    "id": "zoo_enumhorizontal",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 31,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "red", "label": "Rouge"}, {
                                                        "key": "yellow",
                                                        "label": "Jaune"
                                                    }, {"key": "green", "label": "Vert"}, {
                                                        "key": "blue",
                                                        "label": "Bleu"
                                                    }, {
                                                        "key": "lightblue",
                                                        "label": "Bleu\/Bleu ciel"
                                                    }, {"key": "navyblue", "label": "Bleu\/Bleu marine"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumhorizontal"
                                                },
                                                "zoo_enumbool": {
                                                    "id": "zoo_enumbool",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 bool\u00e9en",
                                                    "type": "enum",
                                                    "logicalOrder": 32,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "N", "label": "Normal"}, {
                                                        "key": "C",
                                                        "label": "Critique"
                                                    }],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumbool"
                                                }
                                            }
                                        },
                                        "zoo_fr_enumserversimple": {
                                            "id": "zoo_fr_enumserversimple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s server simple",
                                            "type": "frame",
                                            "logicalOrder": 33,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumserverlist": {
                                                    "id": "zoo_enumserverlist",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 liste",
                                                    "type": "enum",
                                                    "logicalOrder": 34,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverlist"
                                                },
                                                "zoo_enumserverauto": {
                                                    "id": "zoo_enumserverauto",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 auto",
                                                    "type": "enum",
                                                    "logicalOrder": 35,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverauto"
                                                },
                                                "zoo_enumserververtical": {
                                                    "id": "zoo_enumserververtical",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 36,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserververtical"
                                                },
                                                "zoo_enumserverhorizontal": {
                                                    "id": "zoo_enumserverhorizontal",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 37,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverhorizontal"
                                                },
                                                "zoo_enumserverbool": {
                                                    "id": "zoo_enumserverbool",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 bool\u00e9en",
                                                    "type": "enum",
                                                    "logicalOrder": 38,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverbool"
                                                }
                                            }
                                        },
                                        "zoo_fr_enummultiple": {
                                            "id": "zoo_fr_enummultiple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s directs simple",
                                            "type": "frame",
                                            "logicalOrder": 39,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumslist": {
                                                    "id": "zoo_enumslist",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s liste",
                                                    "type": "enum",
                                                    "logicalOrder": 40,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumslist"
                                                },
                                                "zoo_enumsauto": {
                                                    "id": "zoo_enumsauto",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s auto",
                                                    "type": "enum",
                                                    "logicalOrder": 41,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsauto"
                                                },
                                                "zoo_enumsvertical": {
                                                    "id": "zoo_enumsvertical",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 42,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "0", "label": "0 %"}, {
                                                        "key": "30",
                                                        "label": "30 %"
                                                    }, {"key": "70", "label": "70 %"}, {
                                                        "key": "100",
                                                        "label": "100 %"
                                                    }],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsvertical"
                                                },
                                                "zoo_enumshorizontal": {
                                                    "id": "zoo_enumshorizontal",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 43,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "red", "label": "Rouge"}, {
                                                        "key": "yellow",
                                                        "label": "Jaune"
                                                    }, {"key": "green", "label": "Vert"}, {
                                                        "key": "blue",
                                                        "label": "Bleu"
                                                    }, {
                                                        "key": "lightblue",
                                                        "label": "Bleu\/Bleu ciel"
                                                    }, {"key": "navyblue", "label": "Bleu\/Bleu marine"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumshorizontal"
                                                }
                                            }
                                        },
                                        "zoo_fr_enumservermultiple": {
                                            "id": "zoo_fr_enumservermultiple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s server simple",
                                            "type": "frame",
                                            "logicalOrder": 44,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumsserverlist": {
                                                    "id": "zoo_enumsserverlist",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s liste",
                                                    "type": "enum",
                                                    "logicalOrder": 45,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserverlist"
                                                },
                                                "zoo_enumsserverauto": {
                                                    "id": "zoo_enumsserverauto",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s auto",
                                                    "type": "enum",
                                                    "logicalOrder": 46,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserverauto"
                                                },
                                                "zoo_enumsserververtical": {
                                                    "id": "zoo_enumsserververtical",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 47,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserververtical"
                                                },
                                                "zoo_enumsserverhorizontal": {
                                                    "id": "zoo_enumsserverhorizontal",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 48,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserverhorizontal"
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_date": {
                                    "id": "zoo_t_tab_date",
                                    "visibility": "W",
                                    "label": "Les dates",
                                    "type": "tab",
                                    "logicalOrder": 49,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_date": {
                                            "id": "zoo_frame_date",
                                            "visibility": "W",
                                            "label": "Date, heures & date avec l'heure",
                                            "type": "frame",
                                            "logicalOrder": 50,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_dates": {
                                                    "id": "zoo_array_dates",
                                                    "visibility": "W",
                                                    "label": "Le temps",
                                                    "type": "array",
                                                    "logicalOrder": 51,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_date_array": {
                                                            "id": "zoo_date_array",
                                                            "visibility": "W",
                                                            "label": "Des dates",
                                                            "type": "date",
                                                            "logicalOrder": 52,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_time_array": {
                                                            "id": "zoo_time_array",
                                                            "visibility": "W",
                                                            "label": "Des heures",
                                                            "type": "time",
                                                            "logicalOrder": 53,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_timestamp_array": {
                                                            "id": "zoo_timestamp_array",
                                                            "visibility": "W",
                                                            "label": "Des dates avec l'heure",
                                                            "type": "timestamp",
                                                            "logicalOrder": 54,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_relations": {
                                    "id": "zoo_t_tab_relations",
                                    "visibility": "W",
                                    "label": "Les relations",
                                    "type": "tab",
                                    "logicalOrder": 55,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_relation": {
                                            "id": "zoo_frame_relation",
                                            "visibility": "W",
                                            "label": "Relations \u00e0 entretenir",
                                            "type": "frame",
                                            "logicalOrder": 56,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_docid": {
                                                    "id": "zoo_array_docid",
                                                    "visibility": "W",
                                                    "label": "Les documents",
                                                    "type": "array",
                                                    "logicalOrder": 57,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_docid_array": {
                                                            "id": "zoo_docid_array",
                                                            "visibility": "W",
                                                            "label": "Des documents",
                                                            "type": "docid",
                                                            "logicalOrder": 58,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_docid_multiple_array": {
                                                            "id": "zoo_docid_multiple_array",
                                                            "visibility": "W",
                                                            "label": "Encore plus de documents",
                                                            "type": "docid",
                                                            "logicalOrder": 59,
                                                            "multiple": true,
                                                            "options": {"multiple": "yes"},
                                                            "needed": false
                                                        }
                                                    }
                                                },
                                                "zoo_array_account": {
                                                    "id": "zoo_array_account",
                                                    "visibility": "W",
                                                    "label": "Les comptes",
                                                    "type": "array",
                                                    "logicalOrder": 60,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_account_array": {
                                                            "id": "zoo_account_array",
                                                            "visibility": "W",
                                                            "label": "Des comptes",
                                                            "type": "account",
                                                            "logicalOrder": 61,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false,
                                                            "helpOutputs": ["zoo_account_array"]
                                                        },
                                                        "zoo_account_multiple_array": {
                                                            "id": "zoo_account_multiple_array",
                                                            "visibility": "W",
                                                            "label": "Encore plus de comptes",
                                                            "type": "account",
                                                            "logicalOrder": 62,
                                                            "multiple": true,
                                                            "options": {"multiple": "yes"},
                                                            "needed": false,
                                                            "helpOutputs": ["zoo_account_multiple_array"]
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_numbers": {
                                    "id": "zoo_t_tab_numbers",
                                    "visibility": "W",
                                    "label": "Les nombres",
                                    "type": "tab",
                                    "logicalOrder": 63,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_numbers": {
                                            "id": "zoo_frame_numbers",
                                            "visibility": "W",
                                            "label": "Entier, d\u00e9cimaux et monnaie",
                                            "type": "frame",
                                            "logicalOrder": 64,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_numbers": {
                                                    "id": "zoo_array_numbers",
                                                    "visibility": "W",
                                                    "label": "Quelques nombres",
                                                    "type": "array",
                                                    "logicalOrder": 65,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_double_array": {
                                                            "id": "zoo_double_array",
                                                            "visibility": "W",
                                                            "label": "Des d\u00e9cimaux",
                                                            "type": "double",
                                                            "logicalOrder": 66,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_integer_array": {
                                                            "id": "zoo_integer_array",
                                                            "visibility": "W",
                                                            "label": "Des entiers",
                                                            "type": "int",
                                                            "logicalOrder": 67,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_money_array": {
                                                            "id": "zoo_money_array",
                                                            "visibility": "W",
                                                            "label": "Des sous",
                                                            "type": "money",
                                                            "logicalOrder": 68,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_misc": {
                                    "id": "zoo_t_tab_misc",
                                    "visibility": "W",
                                    "label": "Divers",
                                    "type": "tab",
                                    "logicalOrder": 69,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_misc": {
                                            "id": "zoo_frame_misc",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9, couleur et mot de passe",
                                            "type": "frame",
                                            "logicalOrder": 70,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_misc": {
                                                    "id": "zoo_array_misc",
                                                    "visibility": "W",
                                                    "label": "Quelques diverses donn\u00e9es",
                                                    "type": "array",
                                                    "logicalOrder": 71,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_enum_array": {
                                                            "id": "zoo_enum_array",
                                                            "visibility": "W",
                                                            "label": "Des \u00e9num\u00e9r\u00e9s",
                                                            "type": "enum",
                                                            "logicalOrder": 72,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false,
                                                            "enumItems": [{"key": "0", "label": "0 %"}, {
                                                                "key": "30",
                                                                "label": "30 %"
                                                            }, {"key": "70", "label": "70 %"}, {
                                                                "key": "100",
                                                                "label": "100 %"
                                                            }],
                                                            "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enum_array"
                                                        },
                                                        "zoo_enums_array": {
                                                            "id": "zoo_enums_array",
                                                            "visibility": "W",
                                                            "label": "Encore plus d'\u00e9num\u00e9r\u00e9s",
                                                            "type": "enum",
                                                            "logicalOrder": 73,
                                                            "multiple": true,
                                                            "options": {"multiple": "yes"},
                                                            "needed": false,
                                                            "enumItems": [{"key": "0", "label": "0 %"}, {
                                                                "key": "30",
                                                                "label": "30 %"
                                                            }, {"key": "70", "label": "70 %"}, {
                                                                "key": "100",
                                                                "label": "100 %"
                                                            }],
                                                            "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enums_array"
                                                        },
                                                        "zoo_color_array": {
                                                            "id": "zoo_color_array",
                                                            "visibility": "W",
                                                            "label": "Des couleurs",
                                                            "type": "color",
                                                            "logicalOrder": 74,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_password_array": {
                                                            "id": "zoo_password_array",
                                                            "visibility": "W",
                                                            "label": "Des mots de passe",
                                                            "type": "password",
                                                            "logicalOrder": 75,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_files": {
                                    "id": "zoo_t_tab_files",
                                    "visibility": "W",
                                    "label": "Les fichiers",
                                    "type": "tab",
                                    "logicalOrder": 76,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_files": {
                                            "id": "zoo_frame_files",
                                            "visibility": "W",
                                            "label": "Fichiers & images",
                                            "type": "frame",
                                            "logicalOrder": 77,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_files": {
                                                    "id": "zoo_array_files",
                                                    "visibility": "W",
                                                    "label": "Quelques fichiers",
                                                    "type": "array",
                                                    "logicalOrder": 78,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_file_array": {
                                                            "id": "zoo_file_array",
                                                            "visibility": "W",
                                                            "label": "Des fichiers",
                                                            "type": "file",
                                                            "logicalOrder": 79,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_image_array": {
                                                            "id": "zoo_image_array",
                                                            "visibility": "W",
                                                            "label": "Des images",
                                                            "type": "image",
                                                            "logicalOrder": 80,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_texts": {
                                    "id": "zoo_t_tab_texts",
                                    "visibility": "W",
                                    "label": "Les textes",
                                    "type": "tab",
                                    "logicalOrder": 81,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_texts": {
                                            "id": "zoo_frame_texts",
                                            "visibility": "W",
                                            "label": "Les textes non format\u00e9s",
                                            "type": "frame",
                                            "logicalOrder": 82,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_texts": {
                                                    "id": "zoo_array_texts",
                                                    "visibility": "W",
                                                    "label": "Textes simples et multilignes",
                                                    "type": "array",
                                                    "logicalOrder": 83,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_text_array": {
                                                            "id": "zoo_text_array",
                                                            "visibility": "W",
                                                            "label": "Des textes",
                                                            "type": "text",
                                                            "logicalOrder": 84,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_longtext_array": {
                                                            "id": "zoo_longtext_array",
                                                            "visibility": "W",
                                                            "label": "Des textes multiligne",
                                                            "type": "longtext",
                                                            "logicalOrder": 85,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                },
                                                "zoo_array_html": {
                                                    "id": "zoo_array_html",
                                                    "visibility": "W",
                                                    "label": "Les textes HTML",
                                                    "type": "array",
                                                    "logicalOrder": 86,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_htmltext_array": {
                                                            "id": "zoo_htmltext_array",
                                                            "visibility": "W",
                                                            "label": "Des textes format\u00e9s",
                                                            "type": "htmltext",
                                                            "logicalOrder": 87,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "locale": {
                        "label": "Fran\u00e7ais",
                        "localeLabel": "Fran\u00e7ais",
                        "flag": "",
                        "locale": "fr",
                        "culture": "fr-FR",
                        "dateFormat": "%d\/%m\/%Y",
                        "dateTimeFormat": "%d\/%m\/%Y %H:%M",
                        "timeFormat": "%H:%M:%S"
                    },
                    "style": {
                        "css": [{
                            "path": "css\/dcp\/document\/bootstrap.css?ws=3228",
                            "key": "bootstrap"
                        }, {
                            "path": "css\/dcp\/document\/kendo.css?ws=3228",
                            "key": "kendo"
                        }, {
                            "path": "css\/dcp\/document\/document.css?ws=3228",
                            "key": "document"
                        }, {
                            "path": "lib\/jquery-dataTables\/1.10\/bootstrap\/3\/dataTables.bootstrap.css?ws=3228",
                            "key": "datatable"
                        }]
                    },
                    "script": {"js": []}
                },
                "properties": {
                    "requestIdentifier": "!coreConsultation",
                    "uri": "\/dynacase\/api\/v1\/documents\/1081\/views\/!coreConsultation",
                    "identifier": "!coreConsultation",
                    "mode": "consultation",
                    "label": "Vue de consultation core",
                    "isDisplayable": false,
                    "order": 0,
                    "menu": "",
                    "mask": {"id": 0, "title": ""}
                }
            }
        },
        "1081!defaultEdition": {
            "success": true,
            "messages": [],
            "data": {
                "uri": "\/dynacase\/api\/v1\/documents\/1081\/views\/!defaultEdition",
                "view": {
                    "renderLabel": "Vue de modification par d\u00e9faut",
                    "menu": [{
                        "id": "saveAndClose",
                        "type": "itemMenu",
                        "label": "Enregistrer et fermer",
                        "htmlLabel": "",
                        "tooltipLabel": "Enregistrer et fermer le formulaire",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "visible",
                        "beforeContent": "<div class=\"fa fa-save\" \/>",
                        "iconUrl": "",
                        "url": "#event\/document:saveAndClose",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": null,
                        "confirmationOptions": null
                    }, {
                        "id": "save",
                        "type": "itemMenu",
                        "label": "Enregistrer",
                        "htmlLabel": "",
                        "tooltipLabel": "Enregistrer le document et laisser le formulaire ouvert",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "visible",
                        "beforeContent": "<div class=\"fa fa-save\" \/>",
                        "iconUrl": "",
                        "url": "#event\/document:save",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": null,
                        "confirmationOptions": null
                    }, {
                        "id": "save!",
                        "type": "itemMenu",
                        "label": "Enregistrer !",
                        "htmlLabel": "",
                        "tooltipLabel": "Enregistrer le document sans tenir compte des contraintes",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "hidden",
                        "beforeContent": "",
                        "iconUrl": "",
                        "url": "#event\/document:save:force",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": null,
                        "confirmationOptions": null
                    }, {
                        "id": "close",
                        "type": "itemMenu",
                        "label": "Fermer",
                        "htmlLabel": "",
                        "tooltipLabel": "Quitter le formulaire",
                        "tooltipPlacement": "",
                        "htmlAttributes": "",
                        "visibility": "visible",
                        "beforeContent": "<div class=\"fa fa-times\" \/>",
                        "iconUrl": "",
                        "url": "#event\/document:close:!defaultConsultation:unlock",
                        "target": "_self",
                        "targetOptions": null,
                        "confirmationText": null,
                        "confirmationOptions": null
                    }],
                    "templates": {
                        "body": "{{> header}}\n\n{{> menu}}\n\n{{> content}}\n\n{{> footer}}\n",
                        "sections": {
                            "header": "<header class=\"dcpDocument__header {{#document.properties.security.readOnly}} dcpDocument__header--readonly {{\/document.properties.security.readOnly}}\">\n    <img class=\"dcpDocument__header__icon\" src=\"{{document.properties.icon}}\" alt=\"Document icon\"\/>\n    <a class=\"dcpDocument__header__title\" href=\"{{#document.properties.initid}}?app=DOCUMENT&id={{document.properties.id}}{{\/document.properties.initid}}{{^document.properties.initid}}?app=DOCUMENT&id={{document.properties.family.name}}&mode=create{{\/document.properties.initid}}\">{{document.properties.title}}<\/a>\n    <i style=\"display:none\" title=\"Formulaire en cours de modification\" class=\"dcpDocument__header__modified fa fa-asterisk\"><\/i>\n   {{#document.properties.security.lock.lockedBy.id}} <i title=\"Verrouill\u00e9 par <b>{{document.properties.security.lock.lockedBy.title}}<\/b>\" class=\"dcpDocument__header__lock {{#document.properties.security.lock.temporary}} dcpDocument__header__lock--temporary {{\/document.properties.security.lock.temporary}}fa fa-lock\"><\/i>{{\/document.properties.security.lock.lockedBy.id}}\n{{#document.properties.security.readOnly}}\n<span title=\"Lecture seule\" class=\"dcpDocument__header__readonly  fa-stack  text-danger\">\n  <i class=\"fa fa-pencil fa-stack-1x\"><\/i>\n  <i class=\"fa fa-ban fa-stack-1x fa-rotate-90\"><\/i>\n<\/span>\n\n {{\/document.properties.security.readOnly}}\n    <div class=\"dcpDocument__header__family\">{{document.properties.family.title}}<\/div>\n<\/header>\n",
                            "menu": "<nav class=\"dcpDocument__menu\"><\/nav>",
                            "content": "<section class=\"dcpDocument__body\"\/>",
                            "footer": "<footer class=\"dcpDocument__footer\"\/>"
                        },
                        "menu": {
                            "menu": "<div class=\"menu__content container-fluid\">\n   <div class=\"menu__header navbar-header\">\n\n    <\/div>\n    <div class=\"\" id=\"menu_{{uuid}}\">\n        <ul class=\"menu__content\">\n\n        <\/ul>\n    <\/div>\n<\/div>",
                            "itemMenu": "<li data-menu-id=\"{{id}}\"{{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    class=\"menu__element menu__element--item {{cssClass}}\"\n    {{# disabled}}disabled=\"disabled\"{{\/ disabled}}\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}}\n    >\n    <a {{# confirmationText}}class=\"menu--confirm\" data-confirm-message=\"{{confirmationText}}\"{{\/ confirmationText}}\n       {{# target}}target=\"{{target}}\"{{\/ target}}\n       data-url=\"{{url}}\" >\n        {{#iconUrl}}\n            <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n        {{\/iconUrl}}\n        {{#beforeContent}}\n            <span class=\"menu__before-content k-image\">\n                {{{beforeContent}}}\n            <\/span>\n        {{\/beforeContent}}\n        {{{htmlLabel}}}{{label}}\n    <\/a>\n<\/li>\n",
                            "listMenu": "<li data-menu-id=\"{{id}}\"\n    class=\"menu__element menu__element--list {{cssClass}}\"\n    {{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}}\n    {{# disabled}}disabled=\"disabled\"{{\/ disabled}} >\n    {{#iconUrl}}\n    <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n    {{\/iconUrl}}\n    {{#beforeContent}}\n    <span class=\"menu__before-content k-image\">\n        {{{beforeContent}}}\n    <\/span>\n    {{\/beforeContent}}\n    {{{htmlLabel}}}{{label}}\n    <ul class=\"listmenu__content\">\n    <\/ul>\n<\/li>",
                            "dynamicMenu": "<li data-menu-id=\"{{id}}\" data-menu-url=\"{{url}}\"\n    {{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    {{# disabled}}disabled=\"disabled\"{{\/ disabled}}\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}}\n    class=\"menu__element menu__element--dynamic {{cssClass}}\">\n    {{#iconUrl}}\n    <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n    {{\/iconUrl}}\n    {{#beforeContent}}\n    <span class=\"menu__before-content k-image\">\n        {{{beforeContent}}}\n    <\/span>\n    {{\/beforeContent}}\n    {{{htmlLabel}}}{{label}}\n    <ul class=\"listmenu__content\">\n    <\/ul>\n<\/li>",
                            "separatorMenu": "<li data-menu-id=\"{{id}}\" {{# htmlAttr}}{{attrId}}=\"{{attrValue}}\"{{\/ htmlAttr}}\n    class=\"menu__element menu--separator {{cssClass}}\"\n    {{# tooltipLabel}}title=\"{{tooltipLabel}}\"{{\/tooltipLabel}} >\n    {{#iconUrl}}\n    <img src=\"{{iconUrl}}\" class=\"k-image\" \/>\n    {{\/iconUrl}}\n    {{#beforeContent}}\n    <span class=\"menu__before-content k-image\">\n        {{{beforeContent}}}\n    <\/span>\n    {{\/beforeContent}}\n    {{{htmlLabel}}}\n    {{label}}\n    {{^label}}\n        {{^htmlLabel}}\n        <div class=\"menu__empty_separator\"><\/div>\n        {{\/htmlLabel}}\n    {{\/label}}\n<\/li>"
                        },
                        "attribute": {
                            "simpleWrapper": "<label class=\"dcpAttribute__left control-label dcpAttribute__label dcpAttribute__label--{{type}}\" data-attrid=\"{{id}}\" for=\"{{viewCid}}\"\/>\n<div class=\"dcpAttribute__right dcpAttribute__content dcpAttribute__content--{{type}}\" data-attrid=\"{{id}}\"\/>\n",
                            "default": {
                                "write": "<div class=\"{{#hadButtons}}input-group{{\/hadButtons}} margin-bottom-sm\">\n    {{#hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n                {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <input type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# renderOptions.maxLength}}maxlength=\"{{renderOptions.maxLength}}\"{{\/ renderOptions.maxLength}}\n           {{# renderOptions.placeHolder}}placeHolder=\"{{renderOptions.placeHolder}}\"{{\/ renderOptions.placeHolder}}\n           class=\"{{#hadButtons}}form-control{{\/hadButtons}} dcpAttribute__value dcpAttribute__value--edit\"\n           value=\"{{attributeValue.value}}\"\/>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n                class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" data-attrid=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{#attributeValue.formatValue}}{{{attributeValue.formatValue}}}{{\/attributeValue.formatValue}}{{^attributeValue.formatValue}}{{attributeValue.displayValue}}{{\/attributeValue.formatValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "label": "{{label}}",
                            "longtext": {
                                "write": "<div class=\"input-group margin-bottom-sm\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n              {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <textarea type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# renderOptions.maxLength}}maxlength=\"{{renderOptions.maxLength}}\"{{\/ renderOptions.maxLength}}\n           {{# renderOptions.placeHolder}}placeHolder=\"{{renderOptions.placeHolder}}\"{{\/ renderOptions.placeHolder}}\n          class=\"form-control dcpAttribute__value dcpAttribute__value--edit k-textbox\">{{attributeValue.value}}<\/textarea>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n            class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" data-attrid=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{#attributeValue.formatValue}}{{{attributeValue.formatValue}}}{{\/attributeValue.formatValue}}{{^attributeValue.formatValue}}{{attributeValue.displayValue}}{{\/attributeValue.formatValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "file": {
                                "write": "<div class=\"input-group margin-bottom-sm dcpAttribute__dragTarget\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n                {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <input type=\"file\" name=\"{{id}}\" id=\"{{viewCid}}\" style=\"display:none;\"\n           class=\"dcpAttribute__value--file\"\n           value=\"{{attributeValue.value}}\"\/>\n\n    <input {{#attributeValue.icon}}style=\"background-image:url({{attributeValue.icon}}){{\/attributeValue.icon}}\"\n           type=\"text\"\n           class=\"form-control dcpAttribute__value dcpAttribute__value--edit {{#attributeValue.icon}}dcpAttribute__value--fileicon{{\/attributeValue.icon}}\"\n           value=\"{{attributeValue.displayValue}}\"\/>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        <button type=\"button\"  data-index=\"{{index}}\"\n                class=\"dcpAttribute__content__button--file btn btn-default btn-xs\">\n            <span class=\"fa fa-download fa-fw\"><\/span>\n        <\/button>\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n                class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" name=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}\n        title=\"{{renderOptions.htmlLink.title}}\"\n        {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n    {{#attributeValue.icon}}\n        <img src=\"{{attributeValue.icon}}\"\/>\n    {{\/attributeValue.icon}}\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "enum": {
                                "write": "<div class=\"{{#hadButtons}}input-group{{\/hadButtons}} margin-bottom-sm\">\n    <input type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# options.size}}style=\"width : {{options.size}}em;\"{{\/ options.size}}\n           class=\"{{#hadButtons}}form-control{{\/hadButtons}} dcpAttribute__value dcpAttribute__value--edit\"\n           value=\"{{attributeValue.value}}\"\/>\n    {{#hadButtons}}\n        <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n            <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                    class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n            {{#deleteButton}}\n                <button type=\"button\" title=\"Suppression de : \"\n                        class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n                <\/button>\n            {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "writeRadio": "<div class=\"{{#hadButtons}}input-group{{\/hadButtons}} margin-bottom-sm\">\n    <div class=\"input-group k-textbox dcpAttribute__value--enumbuttons orientation-{{renderOptions.editDisplay}}\">\n    {{#enumValues}}\n        <label class=\"dcpAttribute__value--enumlabel{{#selected}} selected{{\/selected}}\">\n            <input name=\"{{id}}\"\n                   {{^isMultiple}}type=\"radio\"{{\/isMultiple}}{{#isMultiple}}type=\"checkbox\"{{\/isMultiple}}\n                   class=\"{{#hadButtons}}form-control{{\/hadButtons}} dcpAttribute__value dcpAttribute__value--edit\"\n                   value=\"{{value}}\" {{#selected}}checked=\"checked\"{{\/selected}}\/>\n            <div class=\"dcpAttribute__value--enumlabel--text\">{{displayValue}}<\/div>\n        <\/label>\n    {{\/enumValues}}\n    <\/div>\n    {{#hadButtons}}\n        <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n            <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n                    class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n            {{#deleteButton}}\n                <button type=\"button\" title=\"Suppression de : \"\n                        class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\"><i class=\"fa fa-times fa-fw\"><\/i>\n                <\/button>\n            {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}\"\n      name=\"{{id}}\">\n    {{#attributeValues}}\n        {{#renderOptions.htmlLink.url}}\n        <a  class=\"dcpAttribute__content__link\"\n            target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n            href=\"{{renderOptions.htmlLink.url}}\">\n        {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderOptions.htmlLink.url}}\n        <\/a>\n        {{\/renderOptions.htmlLink.url}}\n    {{\/attributeValues}}\n    {{{emptyValue}}}\n<\/span>\n\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "htmltext": {
                                "write": "<div class=\"input-group margin-bottom-sm\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n              {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <textarea type=\"text\" name=\"{{id}}\" id=\"{{viewCid}}\"\n           {{# renderOptions.maxLength}}maxlength=\"{{renderOptions.maxLength}}\"{{\/ renderOptions.maxLength}}\n           {{# renderOptions.placeHolder}}placeHolder=\"{{renderOptions.placeHolder}}\"{{\/ renderOptions.placeHolder}}\n          class=\"form-control dcpAttribute__value dcpAttribute__value--edit k-textbox\">{{attributeValue.value}}<\/textarea>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n            class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" name=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n        <span class=\"dcpAttribute__content__value\">{{#attributeValue.formatValue}}{{{attributeValue.formatValue}}}{{\/attributeValue.formatValue}}{{^attributeValue.formatValue}}{{{attributeValue.displayValue}}}{{\/attributeValue.formatValue}}<\/span>\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "docid": {
                                "write": "<div class=\"input-group margin-bottom-sm {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}{{^isMultiple}}dcpAttribute__value--single{{\/isMultiple}}\">\n    <span class=\"input-group-addon\">\n        <button\n            {{#renderOptions.autoCompleteHtmlLabel}}\n            title=\"{{renderOptions.autoCompleteHtmlLabel}}\"\n            {{\/renderOptions.autoCompleteHtmlLabel}}\n            class=\"dcpAttribute__value--docid--button btn btn-default btn-xs\">\n                {{^isMultiple}}\n                <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n                {{\/isMultiple}}\n                {{#isMultiple}}\n                <i class=\"fa fa-plus fa-fw\"><\/i>\n                {{\/isMultiple}}\n        <\/button>\n    <\/span>\n    <select name=\"{{id}}\" id=\"{{viewCid}}\"\n            class=\"form-control dcpAttribute__value dcpAttribute__value--docid\"\n            >\n    <\/select>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" data-index=\"{{index}}\" title=\"{{title}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">\n         {{{htmlContent}}}\n        <\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \" class=\"dcpAttribute__content__button--delete btn btn-default btn-xs\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value dcpAttribute__value--read dcpAttribute__value--docid\" name=\"{{id}}\">\n    {{#attributeValue.value}}\n        {{#renderOptions.htmlLink.renderUrl}}\n        <a class=\"dcpAttribute__content__link\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                        target=\"{{renderOptions.htmlLink.target}}\"\n                        href=\"{{renderOptions.htmlLink.renderUrl}}\">\n        {{\/renderOptions.htmlLink.renderUrl}}\n        <img class=\"dcpAttribute__value--icon\" src=\"{{attributeValue.icon}}\" \/>\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n        {{#renderOptions.htmlLink.renderUrl}}\n        <\/a>\n        {{\/renderOptions.htmlLink.renderUrl}}\n    {{\/attributeValue.value}}\n    {{{emptyValue}}}\n    {{#attributeValues}}\n        {{#renderUrl}}\n        <a class=\"dcpAttribute__content__link dcpAttribute__value--multiple\" data-index=\"{{index}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                  target=\"{{renderOptions.htmlLink.target}}\"\n                   href=\"{{renderUrl}}\">\n            {{\/renderUrl}}\n            <img class=\"dcpAttribute__value--icon\" src=\"{{icon}}\" \/>\n            <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderUrl}}\n        <\/a>\n        {{\/renderUrl}}\n    {{\/attributeValues}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "account": {
                                "write": "<div class=\"input-group margin-bottom-sm {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}{{^isMultiple}}dcpAttribute__value--single{{\/isMultiple}}\">\n    <span class=\"input-group-addon\">\n        <button\n            {{#renderOptions.autoCompleteHtmlLabel}}\n            title=\"{{renderOptions.autoCompleteHtmlLabel}}\"\n            {{\/renderOptions.autoCompleteHtmlLabel}}\n            class=\"dcpAttribute__value--docid--button btn btn-default btn-xs\">\n                {{^isMultiple}}\n                <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n                {{\/isMultiple}}\n                {{#isMultiple}}\n                <i class=\"fa fa-plus fa-fw\"><\/i>\n                {{\/isMultiple}}\n        <\/button>\n    <\/span>\n    <select name=\"{{id}}\" id=\"{{viewCid}}\"\n            class=\"form-control dcpAttribute__value dcpAttribute__value--docid\"\n            >\n    <\/select>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" data-index=\"{{index}}\" title=\"{{title}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">\n         {{{htmlContent}}}\n        <\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \" class=\"dcpAttribute__content__button--delete btn btn-default btn-xs\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value dcpAttribute__value--read dcpAttribute__value--docid\" name=\"{{id}}\">\n    {{#attributeValue.value}}\n        {{#renderOptions.htmlLink.renderUrl}}\n        <a class=\"dcpAttribute__content__link\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                        target=\"{{renderOptions.htmlLink.target}}\"\n                        href=\"{{renderOptions.htmlLink.renderUrl}}\">\n        {{\/renderOptions.htmlLink.renderUrl}}\n        <img class=\"dcpAttribute__value--icon\" src=\"{{attributeValue.icon}}\" \/>\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n        {{#renderOptions.htmlLink.renderUrl}}\n        <\/a>\n        {{\/renderOptions.htmlLink.renderUrl}}\n    {{\/attributeValue.value}}\n    {{{emptyValue}}}\n    {{#attributeValues}}\n        {{#renderUrl}}\n        <a class=\"dcpAttribute__content__link dcpAttribute__value--multiple\" data-index=\"{{index}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                  target=\"{{renderOptions.htmlLink.target}}\"\n                   href=\"{{renderUrl}}\">\n            {{\/renderUrl}}\n            <img class=\"dcpAttribute__value--icon\" src=\"{{icon}}\" \/>\n            <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderUrl}}\n        <\/a>\n        {{\/renderUrl}}\n    {{\/attributeValues}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "thesaurus": {
                                "write": "<div class=\"input-group margin-bottom-sm {{#isMultiple}}dcpAttribute__value--multiple{{\/isMultiple}}{{^isMultiple}}dcpAttribute__value--single{{\/isMultiple}}\">\n    <span class=\"input-group-addon\">\n        <button\n            {{#renderOptions.autoCompleteHtmlLabel}}\n            title=\"{{renderOptions.autoCompleteHtmlLabel}}\"\n            {{\/renderOptions.autoCompleteHtmlLabel}}\n            class=\"dcpAttribute__value--docid--button btn btn-default btn-xs\">\n                {{^isMultiple}}\n                <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n                {{\/isMultiple}}\n                {{#isMultiple}}\n                <i class=\"fa fa-plus fa-fw\"><\/i>\n                {{\/isMultiple}}\n        <\/button>\n    <\/span>\n    <select name=\"{{id}}\" id=\"{{viewCid}}\"\n            class=\"form-control dcpAttribute__value dcpAttribute__value--docid\"\n            >\n    <\/select>\n    {{#hadButtons}}\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        {{#renderOptions.buttons}}\n        <button type=\"button\" data-index=\"{{index}}\" title=\"{{title}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">\n         {{{htmlContent}}}\n        <\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \" class=\"dcpAttribute__content__button--delete btn btn-default btn-xs\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n    {{\/hadButtons}}\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value dcpAttribute__value--read dcpAttribute__value--docid\" name=\"{{id}}\">\n    {{#attributeValue.value}}\n        {{#renderOptions.htmlLink.renderUrl}}\n        <a class=\"dcpAttribute__content__link\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                        target=\"{{renderOptions.htmlLink.target}}\"\n                        href=\"{{renderOptions.htmlLink.renderUrl}}\">\n        {{\/renderOptions.htmlLink.renderUrl}}\n        <img class=\"dcpAttribute__value--icon\" src=\"{{attributeValue.icon}}\" \/>\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n        {{#renderOptions.htmlLink.renderUrl}}\n        <\/a>\n        {{\/renderOptions.htmlLink.renderUrl}}\n    {{\/attributeValue.value}}\n    {{{emptyValue}}}\n    {{#attributeValues}}\n        {{#renderUrl}}\n        <a class=\"dcpAttribute__content__link dcpAttribute__value--multiple\" data-index=\"{{index}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderTitle}}\" {{\/renderOptions.htmlLink.title}}\n                  target=\"{{renderOptions.htmlLink.target}}\"\n                   href=\"{{renderUrl}}\">\n            {{\/renderUrl}}\n            <img class=\"dcpAttribute__value--icon\" src=\"{{icon}}\" \/>\n            <span class=\"dcpAttribute__content__value\">{{displayValue}}<\/span>\n        {{#renderUrl}}\n        <\/a>\n        {{\/renderUrl}}\n    {{\/attributeValues}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "image": {
                                "write": "<div class=\"input-group margin-bottom-sm dcpAttribute__dragTarget\">\n    {{# hasAutocomplete}}\n    <span class=\"input-group-addon\">\n        <button\n              {{#renderOptions.autoCompleteHtmlLabel}}title=\"{{renderOptions.autoCompleteHtmlLabel}}\"{{\/renderOptions.autoCompleteHtmlLabel}}\n                class=\"dcpAttribute__value--autocomplete--button btn btn-default btn-xs\">\n            <i class=\"fa fa-chevron-down fa-fw\"><\/i>\n        <\/button>\n    <\/span>\n    {{\/hasAutocomplete}}\n    <input type=\"file\" name=\"{{id}}\" id=\"{{viewCid}}\" style=\"display:none\" accept=\"image\/*\"\n           class=\"dcpAttribute__value--file\"\n          value=\"{{attributeValue.value}}\"\/>\n    <input {{#attributeValue.thumbnail}}style=\"background-image:url({{attributeValue.thumbnail}}){{\/attributeValue.thumbnail}}\"  type=\"text\"\n           class=\"form-control dcpAttribute__value dcpAttribute__value--edit {{#attributeValue.thumbnail}}dcpAttribute__value--filethumb{{\/attributeValue.thumbnail}}\"\n           value=\"{{attributeValue.displayValue}}\"\/>\n    <span class=\"dcpAttribute__content__buttons input-group-addon\">\n        <button type=\"button\"  data-index=\"{{index}}\" class=\"dcpAttribute__content__button--file btn btn-default btn-xs\">\n            <span class=\"fa fa-download fa-fw\"><\/span>\n        <\/button>\n        {{#renderOptions.buttons}}\n        <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\" class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n        {{\/renderOptions.buttons}}\n        {{#deleteButton}}\n        <button type=\"button\" title=\"Suppression de : \"\n                class=\"btn btn-default btn-xs dcpAttribute__content__button--delete\">\n            <i class=\"fa fa-times fa-fw\"><\/i>\n        <\/button>\n        {{\/deleteButton}}\n    <\/span>\n<\/div>",
                                "read": "<span class=\"dcpAttribute__value dcpAttribute__value--read\" name=\"{{id}}\">\n    {{#renderOptions.htmlLink.url}}\n    <a  class=\"dcpAttribute__content__link\"\n        target=\"{{renderOptions.htmlLink.target}}\"\n        {{#renderOptions.htmlLink.title}}title=\"{{renderOptions.htmlLink.title}}\" {{\/renderOptions.htmlLink.title}}\n        href=\"{{renderOptions.htmlLink.url}}\">\n    {{\/renderOptions.htmlLink.url}}\n    {{^attributeValue.thumbnail}}\n        <span class=\"dcpAttribute__content__value\">{{attributeValue.displayValue}}<\/span>\n    {{\/attributeValue.thumbnail}}\n    {{#attributeValue.thumbnail}}\n        <img src=\"{{attributeValue.thumbnail}}\"\/>\n    {{\/attributeValue.thumbnail}}\n    {{#renderOptions.htmlLink.url}}\n    <\/a>\n    {{\/renderOptions.htmlLink.url}}\n    {{{emptyValue}}}\n<\/span>\n{{#hadButtons}}\n<span class=\"dcpAttribute__content__buttons\">\n    {{#renderOptions.buttons}}\n    <button type=\"button\" title=\"{{title}}\" data-index=\"{{index}}\"\n            class=\"dcpAttribute__content__button--extra btn btn-default btn-xs {{class}}\">{{{htmlContent}}}<\/button>\n    {{\/renderOptions.buttons}}\n<\/span>\n{{\/hadButtons}}"
                            },
                            "frame": {
                                "label": "<div class=\"panel-heading css-frame-head dcpFrame__label dcp__frame--open dcpLabel\" data-id=\"{{id}}\">\n    <span class=\"dcp__frame__caret fa fa-caret-down fa-lg\"><\/span>\n    {{label}}\n<\/div>",
                                "content": "<div class=\"panel-body dcpFrame__content dcpFrame__content--open\" data-attrid=\"{{id}}\">\n<\/div>"
                            },
                            "array": {
                                "label": "<div class=\"panel-heading dcpArray__label dcpLabel\" data-attrid=\"{{id}}\">\n    <span class=\"dcp__array__caret fa fa-caret-down fa-lg\"><\/span>\n    {{label}}\n    {{#displayCount}}<span class=\"dcpArray__label--count\">{{nbLines}}<\/span>{{\/displayCount}}\n<\/div>",
                                "content": "<div class=\"panel-body dcpArray__content dcpArray__content--open\" data-attrid=\"{{id}}\">\n    <table class=\"table table-condensed table-hover table-bordered responsive\">\n        <thead data-attrid=\"{{id}}\">\n        <tr class=\"dcpArray__head\">\n            {{#tools}}\n                <th class=\"dcpArray__head__toolCell\"><\/th>\n            {{\/tools}}\n            {{#content}}\n                <th class=\"dcpArray__head__cell dcpLabel dcpArray__head__cell--{{type}} {{#needed}}dcpAttribute--needed{{\/needed}}\" data-attrid=\"{{id}}\">{{label}}<\/th>\n            {{\/content}}\n        <\/tr>\n        <\/thead>\n        <tbody class=\"dcpArray__body\" data-attrid=\"{{id}}\">\n\n        <\/tbody>\n    <\/table>\n    <div class=\"dcpArray__tools\">\n        {{#tools}}\n        <div class=\"dcpArray__button dcpArray__button--add\" title=\"Ajouter une nouvelle ligne\">\n        <button type=\"button\" class=\"btn btn-default dcpArray__add\">\n            <span class=\"glyphicon glyphicon-plus-sign\"><\/span>\n        <\/button><\/div>\n        <div class=\"dcpArray__button dcpArray__button--copy\" title=\"Dupliquer la ligne s\u00e9lectionn\u00e9e\">\n        <button disabled=\"disabled\" type=\"button\"\n                class=\"btn btn-default dcpArray__copy\">\n            <span class=\"glyphicon glyphicon-sound-dolby\"><\/span>\n        <\/button><\/div>\n        {{\/tools}}\n    <\/div>\n<\/div>",
                                "line": "<tr class=\"dcpArray__content__line\" data-attrid=\"{{id}}\" data-line=\"{{lineNumber}}\">\n    {{#tools}}\n    <td class=\"dcpArray__content__toolCell\">\n        <span title=\"Cliquer pour d\u00e9placer la ligne\" class=\"dcpArray__content__toolCell__dragDrop\">\n            <button class=\"btn btn-default btn-xs\"><span class=\"fa fa-ellipsis-v\"><\/span><\/button>\n        <\/span>\n        <span title=\"S\u00e9lectionner la ligne\" class=\"dcpArray__content__toolCell__check\">\n            <input name=\"check_{{id}}\" type=\"radio\" \/>\n\n        <\/span>\n        <span title=\"Supprimer la ligne\" class=\"dcpArray__content__toolCell__delete\">\n            <button class=\"btn btn-default btn-xs\">\n                <span class=\"fa fa-trash-o\"><\/span>\n            <\/button>\n        <\/span>\n    <\/td>\n    {{\/tools}}\n{{#content}}\n    <td class=\"dcpAttribute__content dcpAttribute__content--{{type}} dcpArray__content__cell dcpArray__content__cell--{{type}}\" data-attrid=\"{{id}}\"><\/td>\n{{\/content}}\n<\/tr>"
                            }
                        },
                        "window": {"confirm": "<div class=\"confirm--body\">\n    <div class=\"confirm--content\">\n        <div>{{messages.textMessage}}<\/div>\n        <div>{{{messages.htmlMessage}}}<\/div>\n\n    <\/div>\n    <div class=\"confirm--buttons\">\n        <button class=\"button--cancel\" type=\"button\">{{messages.cancelMessage}}<\/button>\n        <button class=\"button--ok k-primary\" type=\"button\">{{messages.okMessage}}<\/button>\n    <\/div>\n<\/div>\n\n"}
                    },
                    "renderOptions": {
                        "common": {
                            "showEmptyContent": null,
                            "labelPosition": "auto",
                            "autoCompleteHtmlLabel": "",
                            "inputHtmlTooltip": "",
                            "htmlLink": {
                                "target": "_self",
                                "windowWidth": "300px",
                                "windowHeight": "200px",
                                "windowTitle": "",
                                "title": "",
                                "url": ""
                            },
                            "labels": {"closeErrorMessage": "Fermer le message"}
                        },
                        "types": {
                            "account": {
                                "noAccessText": "Acc\u00e8s interdit au compte",
                                "htmlLink": {
                                    "target": "_render",
                                    "windowWidth": "300px",
                                    "windowHeight": "200px",
                                    "windowTitle": "",
                                    "title": "Voir {{{displayValue}}}",
                                    "url": "?app=DOCUMENT&id={{value}}"
                                }
                            },
                            "date": {"labels": {"invalidDate": "Date invalide"}},
                            "docid": {
                                "noAccessText": "Information non accessible",
                                "htmlLink": {
                                    "target": "_render",
                                    "windowWidth": "300px",
                                    "windowHeight": "200px",
                                    "windowTitle": "",
                                    "title": "Voir {{{displayValue}}}",
                                    "url": "?app=DOCUMENT&id={{value}}"
                                }
                            },
                            "enum": {
                                "boolColor": "",
                                "editDisplay": "list",
                                "useFirstChoice": false,
                                "useSourceUri": false,
                                "labels": {
                                    "chooseMessage": "Choisissez",
                                    "invalidEntry": "Entr\u00e9e invalide",
                                    "invertSelection": "Cliquer pour r\u00e9pondre \"{{displayValue}}\"",
                                    "selectMessage": "S\u00e9lectionner",
                                    "unselectMessage": "D\u00e9s\u00e9lectionner"
                                }
                            },
                            "file": {
                                "downloadInline": false,
                                "labels": {
                                    "dropFileHere": "D\u00e9poser le fichier ici",
                                    "placeHolder": "Cliquez pour choisir un fichier",
                                    "tooltipLabel": "Choisissez un fichier",
                                    "downloadLabel": "T\u00e9l\u00e9charger le fichier",
                                    "kiloByte": "ko",
                                    "byte": "octets",
                                    "recording": "Enregistrement",
                                    "transferring": "T\u00e9l\u00e9versement de"
                                }
                            },
                            "image": {
                                "htmlLink": {
                                    "target": "_dialog",
                                    "windowWidth": "400px",
                                    "windowHeight": "300px",
                                    "windowTitle": "",
                                    "title": "",
                                    "url": ""
                                },
                                "downloadInline": true,
                                "thumbnailWidth": 48,
                                "labels": {
                                    "dropFileHere": "D\u00e9poser l'image ici",
                                    "placeHolder": "Cliquez pour choisir une image",
                                    "tooltipLabel": "Choisissez une image",
                                    "downloadLabel": "T\u00e9l\u00e9charger l'image",
                                    "kiloByte": "ko",
                                    "recording": "Enregistrement",
                                    "transferring": "T\u00e9l\u00e9versement de"
                                }
                            },
                            "htmltext": {"toolbar": "Simple", "toolbarStartupExpanded": true, "height": "120px"},
                            "longtext": {"displayedLineNumber": 0},
                            "int": {"max": 2147483647, "min": -2147483647},
                            "double": {"max": null, "min": null, "decimalPrecision": null},
                            "money": {"max": null, "min": null, "decimalPrecision": 2, "currency": "\u20ac"},
                            "text": {"maxLength": null, "format": "{{displayValue}}"},
                            "array": {
                                "rowCountThreshold": -1,
                                "labels": {
                                    "limitMaxMessage": "Le nombre maximum de rang\u00e9e est de {{limit}}",
                                    "limitMinMessage": "Le nombre de rang\u00e9es minimum est de {{limit}}"
                                }
                            },
                            "time": [],
                            "timestamp": [],
                            "thesaurus": {
                                "htmlLink": {
                                    "target": "_render",
                                    "windowWidth": "300px",
                                    "windowHeight": "200px",
                                    "windowTitle": "",
                                    "title": "Voir {{{displayValue}}}",
                                    "url": "?app=DOCUMENT&id={{value}}"
                                }
                            }
                        },
                        "mode": "edit",
                        "attributes": {"zoo_t_tab": {"openFirst": true}},
                        "visibilities": {
                            "zoo_frame_relation": "W",
                            "zoo_t_tab_numbers": "W",
                            "zoo_t_tab_relations": "W",
                            "zoo_frame_date": "W",
                            "zoo_t_tab_date": "W",
                            "zoo_frame_numbers": "W",
                            "zoo_t_tab_misc": "W",
                            "zoo_t_tab_texts": "W",
                            "zoo_frame_texts": "W",
                            "zoo_frame_files": "W",
                            "zoo_t_tab_files": "W",
                            "zoo_fr_enumservermultiple": "W",
                            "zoo_frame_misc": "W",
                            "zoo_fr_date": "W",
                            "zoo_fr_number": "W",
                            "zoo_fr_rels": "W",
                            "zoo_t_tab": "W",
                            "zoo_f_title": "W",
                            "zoo_fr_enummultiple": "W",
                            "zoo_fr_misc": "W",
                            "zoo_fr_enumserversimple": "W",
                            "zoo_fr_file": "W",
                            "zoo_fr_enumsimple": "W",
                            "zoo_t_tab_enums": "W",
                            "zoo_fr_text": "W",
                            "zoo_title": "W",
                            "zoo_account": "W",
                            "zoo_account_multiple": "W",
                            "zoo_docid": "W",
                            "zoo_docid_multiple": "W",
                            "zoo_date": "W",
                            "zoo_time": "W",
                            "zoo_timestamp": "W",
                            "zoo_integer": "W",
                            "zoo_double": "W",
                            "zoo_money": "W",
                            "zoo_password": "W",
                            "zoo_color": "W",
                            "zoo_file": "W",
                            "zoo_image": "W",
                            "zoo_htmltext": "W",
                            "zoo_longtext": "W",
                            "zoo_text": "W",
                            "zoo_enumlist": "W",
                            "zoo_enumauto": "W",
                            "zoo_enumvertical": "W",
                            "zoo_enumhorizontal": "W",
                            "zoo_enumbool": "W",
                            "zoo_enumserverlist": "W",
                            "zoo_enumserverauto": "W",
                            "zoo_enumserververtical": "W",
                            "zoo_enumserverhorizontal": "W",
                            "zoo_enumserverbool": "W",
                            "zoo_enumslist": "W",
                            "zoo_enumsauto": "W",
                            "zoo_enumsvertical": "W",
                            "zoo_enumshorizontal": "W",
                            "zoo_enumsserverlist": "W",
                            "zoo_enumsserverauto": "W",
                            "zoo_enumsserververtical": "W",
                            "zoo_enumsserverhorizontal": "W",
                            "zoo_array_dates": "W",
                            "zoo_date_array": "W",
                            "zoo_time_array": "W",
                            "zoo_timestamp_array": "W",
                            "zoo_array_docid": "W",
                            "zoo_docid_array": "W",
                            "zoo_docid_multiple_array": "W",
                            "zoo_array_account": "W",
                            "zoo_account_array": "W",
                            "zoo_account_multiple_array": "W",
                            "zoo_array_numbers": "W",
                            "zoo_double_array": "W",
                            "zoo_integer_array": "W",
                            "zoo_money_array": "W",
                            "zoo_array_misc": "W",
                            "zoo_enum_array": "W",
                            "zoo_enums_array": "W",
                            "zoo_color_array": "W",
                            "zoo_password_array": "W",
                            "zoo_array_files": "W",
                            "zoo_file_array": "W",
                            "zoo_image_array": "W",
                            "zoo_array_texts": "W",
                            "zoo_text_array": "W",
                            "zoo_longtext_array": "W",
                            "zoo_array_html": "W",
                            "zoo_htmltext_array": "W"
                        },
                        "needed": []
                    },
                    "documentData": {
                        "document": {
                            "properties": {
                                "id": 1081,
                                "title": "Document de test contenant tous les attributs de Dynacase 11",
                                "family": {
                                    "title": "Test tout type",
                                    "name": "ZOO_ALLTYPE",
                                    "id": 1067,
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=24"
                                },
                                "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=24",
                                "revision": 0,
                                "security": {
                                    "lock": {"id": 0},
                                    "readOnly": false,
                                    "fixed": false,
                                    "profil": {"id": 0, "title": ""},
                                    "confidentiality": "public"
                                },
                                "status": "alive",
                                "initid": 1081
                            },
                            "attributes": {
                                "zoo_title": {
                                    "value": "Document de test contenant tous les attributs de Dynacase 11",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase 11"
                                },
                                "zoo_account": {"value": null, "displayValue": null},
                                "zoo_account_multiple": [],
                                "zoo_docid": {
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                },
                                "zoo_docid_multiple": [{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }],
                                "zoo_date": {"value": "2015-04-06", "displayValue": "06\/04\/2015"},
                                "zoo_time": {"value": "12:00:00", "displayValue": "12:00:00"},
                                "zoo_timestamp": {"value": "2015-04-22 15:13:00", "displayValue": "22\/04\/2015 15:13"},
                                "zoo_integer": {"value": 42, "displayValue": "42"},
                                "zoo_double": {"value": 12.15, "displayValue": "12.15"},
                                "zoo_money": {"value": 12000, "displayValue": "12000"},
                                "zoo_password": {"value": "p@ssw0rd", "displayValue": "p@ssw0rd"},
                                "zoo_color": {"value": "#804AFF", "displayValue": "#804AFF"},
                                "zoo_file": {
                                    "size": "21501",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "1511__880.jpg",
                                    "url": "file\/1081\/14\/zoo_file\/-1\/1511__880.jpg?cache=no&inline=yes",
                                    "mime": "image\/jpeg",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=20",
                                    "value": "image\/jpeg; charset=binary|14|1511__880.jpg",
                                    "displayValue": "1511__880.jpg"
                                },
                                "zoo_image": {
                                    "thumbnail": "file\/1081\/15\/zoo_image\/-1\/eiffel.jpg?cache=no&inline=yes&width=48",
                                    "size": "60052",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "eiffel.jpg",
                                    "url": "file\/1081\/15\/zoo_image\/-1\/eiffel.jpg?cache=no&inline=yes",
                                    "mime": "image\/jpeg",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
                                    "value": "image\/jpeg; charset=binary|15|eiffel.jpg",
                                    "displayValue": "eiffel.jpg"
                                },
                                "zoo_htmltext": {
                                    "value": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p> <p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p> <p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p> <p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p> <p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>",
                                    "displayValue": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p> <p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p> <p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p> <p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p> <p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>"
                                },
                                "zoo_longtext": {
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                },
                                "zoo_text": {
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                },
                                "zoo_enumlist": {"value": "AE", "displayValue": "Emirats Arabes unis"},
                                "zoo_enumauto": {"value": "AG", "displayValue": "Antigua et Barbade"},
                                "zoo_enumvertical": {"value": "70", "displayValue": "70 %"},
                                "zoo_enumhorizontal": {"value": "yellow", "displayValue": "Jaune"},
                                "zoo_enumbool": {"value": "C", "displayValue": "Critique"},
                                "zoo_enumserverlist": {"value": "AG", "displayValue": "Antigua et Barbade"},
                                "zoo_enumserverauto": {"value": "BR", "displayValue": "Br\u00e9sil"},
                                "zoo_enumserververtical": {"value": "lightblue", "displayValue": "Bleu\/Bleu ciel"},
                                "zoo_enumserverhorizontal": {"value": "100", "displayValue": "100 %"},
                                "zoo_enumserverbool": {"value": "C", "displayValue": "Critique"},
                                "zoo_enumslist": [{"value": "AD", "displayValue": "Andorre"}, {
                                    "value": "BO",
                                    "displayValue": "Bolivie"
                                }, {"value": "BR", "displayValue": "Br\u00e9sil"}],
                                "zoo_enumsauto": [{"value": "AF", "displayValue": "Afghanistan"}],
                                "zoo_enumsvertical": [{"value": "30", "displayValue": "30 %"}],
                                "zoo_enumshorizontal": [{"value": "red", "displayValue": "Rouge"}],
                                "zoo_enumsserverlist": [{"value": "BG", "displayValue": "Bulgarie"}, {
                                    "value": "BW",
                                    "displayValue": "Botswana"
                                }],
                                "zoo_enumsserverauto": [{"value": "BT", "displayValue": "Bhoutan"}, {
                                    "value": "GP",
                                    "displayValue": "Guadeloupe"
                                }],
                                "zoo_enumsserververtical": [{"value": "100", "displayValue": "100 %"}],
                                "zoo_enumsserverhorizontal": [{"value": "blue", "displayValue": "Bleu"}],
                                "zoo_date_array": [{
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }, {"value": "2015-04-24", "displayValue": "24\/04\/2015"}, {
                                    "value": "2015-04-24",
                                    "displayValue": "24\/04\/2015"
                                }],
                                "zoo_time_array": [{"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}, {
                                    "value": "12:00",
                                    "displayValue": "12:00"
                                }, {"value": "12:00", "displayValue": "12:00"}],
                                "zoo_timestamp_array": [{
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {
                                    "value": "2015-04-12 15:13",
                                    "displayValue": "12\/04\/2015 15:13"
                                }, {"value": "2015-04-12 15:13", "displayValue": "12\/04\/2015 15:13"}],
                                "zoo_docid_array": [{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }, {
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": null,
                                    "icon": null,
                                    "value": null,
                                    "displayValue": null
                                }],
                                "zoo_docid_multiple_array": [[{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }], [{
                                    "familyRelation": "ZOO_ALLTYPE",
                                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1080&amp;latest=Y",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fnoimage.png&size=14",
                                    "value": "1080",
                                    "displayValue": "Document de test contenant tous les attributs de Dynacase"
                                }]],
                                "zoo_account_array": [],
                                "zoo_account_multiple_array": [],
                                "zoo_double_array": [{"value": 12, "displayValue": "12"}],
                                "zoo_integer_array": [{"value": 12, "displayValue": "12"}],
                                "zoo_money_array": [{"value": 12, "displayValue": "12"}],
                                "zoo_enum_array": [{"value": "70", "displayValue": "70 %"}],
                                "zoo_enums_array": [[{"value": "70", "displayValue": "70 %"}]],
                                "zoo_color_array": [{"value": "#DBFF9C", "displayValue": "#DBFF9C"}],
                                "zoo_password_array": [{"value": "p@ssw0rd", "displayValue": "p@ssw0rd"}],
                                "zoo_file_array": [{
                                    "size": 0,
                                    "creationDate": "",
                                    "fileName": "",
                                    "url": "",
                                    "mime": "",
                                    "icon": "",
                                    "value": null,
                                    "displayValue": null
                                }, {
                                    "size": 0,
                                    "creationDate": "",
                                    "fileName": "",
                                    "url": "",
                                    "mime": "",
                                    "icon": "",
                                    "value": null,
                                    "displayValue": null
                                }],
                                "zoo_image_array": [{
                                    "thumbnail": "file\/1081\/16\/zoo_image_array\/0\/vintage-rainbow-color-badge.png?cache=no&inline=yes&width=48",
                                    "size": "60330",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "vintage-rainbow-color-badge.png",
                                    "url": "file\/1081\/16\/zoo_image_array\/0\/vintage-rainbow-color-badge.png?cache=no&inline=yes",
                                    "mime": "image\/png",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image2.png&size=24",
                                    "value": "image\/png; charset=binary|16|vintage-rainbow-color-badge.png",
                                    "displayValue": "vintage-rainbow-color-badge.png"
                                }, {
                                    "thumbnail": "file\/1081\/17\/zoo_image_array\/1\/retour%20vers%20le%20futur.jpg?cache=no&inline=yes&width=48",
                                    "size": "73490",
                                    "creationDate": "2015-04-16 14:25:54",
                                    "fileName": "retour vers le futur.jpg",
                                    "url": "file\/1081\/17\/zoo_image_array\/1\/retour%20vers%20le%20futur.jpg?cache=no&inline=yes",
                                    "mime": "image\/jpeg",
                                    "icon": "resizeimg.php?img=CORE%2FImages%2Fmime-image.png&size=24",
                                    "value": "image\/jpeg; charset=binary|17|retour vers le futur.jpg",
                                    "displayValue": "retour vers le futur.jpg"
                                }],
                                "zoo_text_array": [{
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.  L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.  Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.  En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.  Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                }],
                                "zoo_longtext_array": [{
                                    "value": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb",
                                    "displayValue": "Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.\n\nL'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.\n\nElle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.\n\nEn Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.\n\nRemarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb"
                                }],
                                "zoo_htmltext_array": [{
                                    "value": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>",
                                    "displayValue": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>"
                                }, {
                                    "value": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>",
                                    "displayValue": "<p>Une donn\u00e9e ouverte est une donn\u00e9e num\u00e9rique d'origine publique ou priv\u00e9e. Elle peut \u00eatre notamment produite par une collectivit\u00e9, un service public (\u00e9ventuellement d\u00e9l\u00e9gu\u00e9) ou une entreprise. Elle est diffus\u00e9e de mani\u00e8re structur\u00e9e selon une m\u00e9thodologie et une licence ouverte garantissant son libre acc\u00e8s et sa r\u00e9utilisation par tous, sans restriction technique, juridique ou financi\u00e8re.<\/p><p>L'ouverture des donn\u00e9es (en anglais open data) repr\u00e9sente \u00e0 la fois un mouvement, une philosophie d'acc\u00e8s \u00e0 l'information et une pratique de publication de donn\u00e9es librement accessibles et exploitables.<\/p><p>Elle s'inscrit dans une tendance qui consid\u00e8re l'information publique comme un bien commun (tel que d\u00e9fini par Elinor Ostrom) dont la diffusion est d'int\u00e9r\u00eat public et g\u00e9n\u00e9ral.<\/p><p>En Europe et dans certains pays, des directives et lois imposent aux collectivit\u00e9s de publier certaines donn\u00e9es publiques sous forme num\u00e9rique.<\/p><p>Remarque : Le pr\u00e9sent article est g\u00e9n\u00e9raliste. Le sujet de la donn\u00e9e ouverte en France est trait\u00e9 dans un autre article : \u00ab Donn\u00e9es ouvertes en France \u00bb<\/p>"
                                }]
                            },
                            "uri": "\/dynacase\/api\/v1\/documents\/1081.json"
                        },
                        "family": {
                            "structure": {
                                "zoo_f_title": {
                                    "id": "zoo_f_title",
                                    "visibility": "W",
                                    "label": "Titre",
                                    "type": "frame",
                                    "logicalOrder": 0,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_title": {
                                            "id": "zoo_title",
                                            "visibility": "W",
                                            "label": "Le titre",
                                            "type": "text",
                                            "logicalOrder": 1,
                                            "multiple": false,
                                            "options": [],
                                            "needed": false
                                        }
                                    }
                                },
                                "zoo_t_tab": {
                                    "id": "zoo_t_tab",
                                    "visibility": "W",
                                    "label": "Basiques",
                                    "type": "tab",
                                    "logicalOrder": 2,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_fr_rels": {
                                            "id": "zoo_fr_rels",
                                            "visibility": "W",
                                            "label": "Relations",
                                            "type": "frame",
                                            "logicalOrder": 3,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_account": {
                                                    "id": "zoo_account",
                                                    "visibility": "W",
                                                    "label": "Un compte",
                                                    "type": "account",
                                                    "logicalOrder": 4,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "helpOutputs": ["zoo_account"]
                                                },
                                                "zoo_account_multiple": {
                                                    "id": "zoo_account_multiple",
                                                    "visibility": "W",
                                                    "label": "Des comptes",
                                                    "type": "account",
                                                    "logicalOrder": 5,
                                                    "multiple": true,
                                                    "options": {"multiple": "yes"},
                                                    "needed": false,
                                                    "helpOutputs": ["zoo_account_multiple"]
                                                },
                                                "zoo_docid": {
                                                    "id": "zoo_docid",
                                                    "visibility": "W",
                                                    "label": "Un document",
                                                    "type": "docid",
                                                    "logicalOrder": 6,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_docid_multiple": {
                                                    "id": "zoo_docid_multiple",
                                                    "visibility": "W",
                                                    "label": "Des documents",
                                                    "type": "docid",
                                                    "logicalOrder": 7,
                                                    "multiple": true,
                                                    "options": {"multiple": "yes"},
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_date": {
                                            "id": "zoo_fr_date",
                                            "visibility": "W",
                                            "label": "Le temps",
                                            "type": "frame",
                                            "logicalOrder": 8,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_date": {
                                                    "id": "zoo_date",
                                                    "visibility": "W",
                                                    "label": "Une date",
                                                    "type": "date",
                                                    "logicalOrder": 9,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_time": {
                                                    "id": "zoo_time",
                                                    "visibility": "W",
                                                    "label": "Une heure",
                                                    "type": "time",
                                                    "logicalOrder": 10,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_timestamp": {
                                                    "id": "zoo_timestamp",
                                                    "visibility": "W",
                                                    "label": "Une date avec  une heure",
                                                    "type": "timestamp",
                                                    "logicalOrder": 11,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_number": {
                                            "id": "zoo_fr_number",
                                            "visibility": "W",
                                            "label": "Les nombres",
                                            "type": "frame",
                                            "logicalOrder": 12,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_integer": {
                                                    "id": "zoo_integer",
                                                    "visibility": "W",
                                                    "label": "Un entier",
                                                    "type": "int",
                                                    "logicalOrder": 13,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_double": {
                                                    "id": "zoo_double",
                                                    "visibility": "W",
                                                    "label": "Un d\u00e9cimal",
                                                    "type": "double",
                                                    "logicalOrder": 14,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_money": {
                                                    "id": "zoo_money",
                                                    "visibility": "W",
                                                    "label": "Un sous",
                                                    "type": "money",
                                                    "logicalOrder": 15,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_misc": {
                                            "id": "zoo_fr_misc",
                                            "visibility": "W",
                                            "label": "Divers",
                                            "type": "frame",
                                            "logicalOrder": 16,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_password": {
                                                    "id": "zoo_password",
                                                    "visibility": "W",
                                                    "label": "Un mot de passe",
                                                    "type": "password",
                                                    "logicalOrder": 17,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_color": {
                                                    "id": "zoo_color",
                                                    "visibility": "W",
                                                    "label": "Une couleur",
                                                    "type": "color",
                                                    "logicalOrder": 18,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_file": {
                                            "id": "zoo_fr_file",
                                            "visibility": "W",
                                            "label": "Fichiers & images",
                                            "type": "frame",
                                            "logicalOrder": 19,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_file": {
                                                    "id": "zoo_file",
                                                    "visibility": "W",
                                                    "label": "Un fichier",
                                                    "type": "file",
                                                    "logicalOrder": 20,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_image": {
                                                    "id": "zoo_image",
                                                    "visibility": "W",
                                                    "label": "Une image",
                                                    "type": "image",
                                                    "logicalOrder": 21,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        },
                                        "zoo_fr_text": {
                                            "id": "zoo_fr_text",
                                            "visibility": "W",
                                            "label": "Les textes",
                                            "type": "frame",
                                            "logicalOrder": 22,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_htmltext": {
                                                    "id": "zoo_htmltext",
                                                    "visibility": "W",
                                                    "label": "Un texte formart\u00e9",
                                                    "type": "htmltext",
                                                    "logicalOrder": 23,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_longtext": {
                                                    "id": "zoo_longtext",
                                                    "visibility": "W",
                                                    "label": "Un texte multiligne",
                                                    "type": "longtext",
                                                    "logicalOrder": 24,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                },
                                                "zoo_text": {
                                                    "id": "zoo_text",
                                                    "visibility": "W",
                                                    "label": "Un texte simple",
                                                    "type": "text",
                                                    "logicalOrder": 25,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_enums": {
                                    "id": "zoo_t_tab_enums",
                                    "visibility": "W",
                                    "label": "Les \u00e9num\u00e9r\u00e9s",
                                    "type": "tab",
                                    "logicalOrder": 26,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_fr_enumsimple": {
                                            "id": "zoo_fr_enumsimple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s directs simple",
                                            "type": "frame",
                                            "logicalOrder": 27,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumlist": {
                                                    "id": "zoo_enumlist",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 liste",
                                                    "type": "enum",
                                                    "logicalOrder": 28,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumlist"
                                                },
                                                "zoo_enumauto": {
                                                    "id": "zoo_enumauto",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 auto",
                                                    "type": "enum",
                                                    "logicalOrder": 29,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumauto"
                                                },
                                                "zoo_enumvertical": {
                                                    "id": "zoo_enumvertical",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 30,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "0", "label": "0 %"}, {
                                                        "key": "30",
                                                        "label": "30 %"
                                                    }, {"key": "70", "label": "70 %"}, {
                                                        "key": "100",
                                                        "label": "100 %"
                                                    }],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumvertical"
                                                },
                                                "zoo_enumhorizontal": {
                                                    "id": "zoo_enumhorizontal",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 31,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "red", "label": "Rouge"}, {
                                                        "key": "yellow",
                                                        "label": "Jaune"
                                                    }, {"key": "green", "label": "Vert"}, {
                                                        "key": "blue",
                                                        "label": "Bleu"
                                                    }, {
                                                        "key": "lightblue",
                                                        "label": "Bleu\/Bleu ciel"
                                                    }, {"key": "navyblue", "label": "Bleu\/Bleu marine"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumhorizontal"
                                                },
                                                "zoo_enumbool": {
                                                    "id": "zoo_enumbool",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 bool\u00e9en",
                                                    "type": "enum",
                                                    "logicalOrder": 32,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "N", "label": "Normal"}, {
                                                        "key": "C",
                                                        "label": "Critique"
                                                    }],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumbool"
                                                }
                                            }
                                        },
                                        "zoo_fr_enumserversimple": {
                                            "id": "zoo_fr_enumserversimple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s server simple",
                                            "type": "frame",
                                            "logicalOrder": 33,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumserverlist": {
                                                    "id": "zoo_enumserverlist",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 liste",
                                                    "type": "enum",
                                                    "logicalOrder": 34,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverlist"
                                                },
                                                "zoo_enumserverauto": {
                                                    "id": "zoo_enumserverauto",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 auto",
                                                    "type": "enum",
                                                    "logicalOrder": 35,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverauto"
                                                },
                                                "zoo_enumserververtical": {
                                                    "id": "zoo_enumserververtical",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 36,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserververtical"
                                                },
                                                "zoo_enumserverhorizontal": {
                                                    "id": "zoo_enumserverhorizontal",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 37,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverhorizontal"
                                                },
                                                "zoo_enumserverbool": {
                                                    "id": "zoo_enumserverbool",
                                                    "visibility": "W",
                                                    "label": "Un \u00e9num\u00e9r\u00e9 bool\u00e9en",
                                                    "type": "enum",
                                                    "logicalOrder": 38,
                                                    "multiple": false,
                                                    "options": {"bmenu": "no", "eformat": "auto"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumserverbool"
                                                }
                                            }
                                        },
                                        "zoo_fr_enummultiple": {
                                            "id": "zoo_fr_enummultiple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s directs simple",
                                            "type": "frame",
                                            "logicalOrder": 39,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumslist": {
                                                    "id": "zoo_enumslist",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s liste",
                                                    "type": "enum",
                                                    "logicalOrder": 40,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumslist"
                                                },
                                                "zoo_enumsauto": {
                                                    "id": "zoo_enumsauto",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s auto",
                                                    "type": "enum",
                                                    "logicalOrder": 41,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "AD", "label": "Andorre"}, {
                                                        "key": "AE",
                                                        "label": "Emirats Arabes unis"
                                                    }, {"key": "AF", "label": "Afghanistan"}, {
                                                        "key": "AG",
                                                        "label": "Antigua et Barbade"
                                                    }, {"key": "AI", "label": "Anguilla"}, {
                                                        "key": "AL",
                                                        "label": "Albanie"
                                                    }, {"key": "AM", "label": "Arm\u00e9nie"}, {
                                                        "key": "AN",
                                                        "label": "Antilles n\u00e9erlandaises"
                                                    }, {"key": "AO", "label": "Angola"}, {
                                                        "key": "AR",
                                                        "label": "Argentine"
                                                    }, {"key": "AS", "label": "Samoa am\u00e9ricain"}, {
                                                        "key": "AT",
                                                        "label": "Autriche"
                                                    }, {"key": "AU", "label": "Australie"}, {
                                                        "key": "AW",
                                                        "label": "Aruba"
                                                    }, {"key": "AZ", "label": "Azerba\u00efdjan"}, {
                                                        "key": "BB",
                                                        "label": "Barbade"
                                                    }, {"key": "BD", "label": "Bangladesh"}, {
                                                        "key": "BE",
                                                        "label": "Belgique"
                                                    }, {"key": "BF", "label": "Burkina Faso"}, {
                                                        "key": "BG",
                                                        "label": "Bulgarie"
                                                    }, {"key": "BH", "label": "Bahrein"}, {
                                                        "key": "BI",
                                                        "label": "Burundi"
                                                    }, {"key": "BJ", "label": "B\u00e9nin"}, {
                                                        "key": "BM",
                                                        "label": "Bermudes"
                                                    }, {"key": "BN", "label": "Brunei Darussalam"}, {
                                                        "key": "BO",
                                                        "label": "Bolivie"
                                                    }, {"key": "BR", "label": "Br\u00e9sil"}, {
                                                        "key": "BS",
                                                        "label": "Bahamas"
                                                    }, {"key": "BT", "label": "Bhoutan"}, {
                                                        "key": "BV",
                                                        "label": "Iles Bouvet"
                                                    }, {"key": "BW", "label": "Botswana"}, {
                                                        "key": "BY",
                                                        "label": "Bi\u00e9lorussie"
                                                    }, {"key": "BZ", "label": "Belize"}, {
                                                        "key": "CA",
                                                        "label": "Canada"
                                                    }, {"key": "CC", "label": "Iles Cocos"}, {
                                                        "key": "CF",
                                                        "label": "Centre-Afrique"
                                                    }, {"key": "CG", "label": "Congo"}, {
                                                        "key": "CH",
                                                        "label": "Suisse"
                                                    }, {"key": "CI", "label": "C\u00f4te d'Ivoire"}, {
                                                        "key": "CK",
                                                        "label": "Iles Cook"
                                                    }, {"key": "CL", "label": "Chili"}, {
                                                        "key": "CM",
                                                        "label": "Cameroun"
                                                    }, {"key": "CN", "label": "Chine"}, {
                                                        "key": "CO",
                                                        "label": "Colombie"
                                                    }, {"key": "CR", "label": "Costa Rica"}, {
                                                        "key": "CU",
                                                        "label": "Cuba"
                                                    }, {"key": "CV", "label": "Cap Vert"}, {
                                                        "key": "CX",
                                                        "label": "Ile Christmas"
                                                    }, {"key": "CY", "label": "Chypre"}, {
                                                        "key": "CZ",
                                                        "label": "Tch\u00e9quie"
                                                    }, {"key": "DE", "label": "Allemagne"}, {
                                                        "key": "DJ",
                                                        "label": "Djibouti"
                                                    }, {"key": "DK", "label": "Danemark"}, {
                                                        "key": "DM",
                                                        "label": "Dominique"
                                                    }, {
                                                        "key": "DO",
                                                        "label": "R\u00e9publique dominicaine"
                                                    }, {"key": "DZ", "label": "Alg\u00e9rie"}, {
                                                        "key": "EC",
                                                        "label": "Equateur"
                                                    }, {"key": "EE", "label": "Estonie"}, {
                                                        "key": "EG",
                                                        "label": "Egypte"
                                                    }, {"key": "EH", "label": "Sahara occidental"}, {
                                                        "key": "ER",
                                                        "label": "Erythr\u00e9e"
                                                    }, {"key": "ES", "label": "Espagne"}, {
                                                        "key": "ET",
                                                        "label": "Ethiopie"
                                                    }, {"key": "EU", "label": "Union Europ\u00e9enne"}, {
                                                        "key": "FI",
                                                        "label": "Finlande"
                                                    }, {"key": "FJ", "label": "Fidji"}, {
                                                        "key": "FK",
                                                        "label": "Falkland"
                                                    }, {"key": "FM", "label": "Micron\u00e9sie"}, {
                                                        "key": "FO",
                                                        "label": "F\u00e9ro\u00e9"
                                                    }, {"key": "FR", "label": "France"}, {
                                                        "key": "GA",
                                                        "label": "Gabon"
                                                    }, {"key": "GD", "label": "Grenade"}, {
                                                        "key": "GE",
                                                        "label": "G\u00e9orgie"
                                                    }, {"key": "GF", "label": "Guyane fran\u00e7aise"}, {
                                                        "key": "GH",
                                                        "label": "Ghana"
                                                    }, {"key": "GI", "label": "Gibraltar"}, {
                                                        "key": "GL",
                                                        "label": "Groenland"
                                                    }, {"key": "GM", "label": "Gambie"}, {
                                                        "key": "GN",
                                                        "label": "Guin\u00e9e"
                                                    }, {"key": "GP", "label": "Guadeloupe"}, {
                                                        "key": "GR",
                                                        "label": "Gr\u00e8ce"
                                                    }, {"key": "GT", "label": "Guatemala"}, {
                                                        "key": "GU",
                                                        "label": "Guam (USA)"
                                                    }, {"key": "GW", "label": "Guin\u00e9e Bissau"}, {
                                                        "key": "GY",
                                                        "label": "Guyane"
                                                    }, {"key": "HK", "label": "Hong Kong"}, {
                                                        "key": "HM",
                                                        "label": "Iles Heard et Mac Donald"
                                                    }, {"key": "HN", "label": "Honduras"}, {
                                                        "key": "HR",
                                                        "label": "Croatie"
                                                    }, {"key": "HT", "label": "Ha\u00efti"}, {
                                                        "key": "HU",
                                                        "label": "Hongrie"
                                                    }, {"key": "ID", "label": "Indon\u00e9sie"}, {
                                                        "key": "IE",
                                                        "label": "Irlande"
                                                    }, {"key": "IL", "label": "Isra\u00ebl"}, {
                                                        "key": "IN",
                                                        "label": "Inde"
                                                    }, {
                                                        "key": "IO",
                                                        "label": "Territoires britanniques de l'oc\u00e9an indien"
                                                    }, {"key": "IQ", "label": "Irak"}, {
                                                        "key": "IR",
                                                        "label": "Iran"
                                                    }, {"key": "IS", "label": "Islande"}, {
                                                        "key": "IT",
                                                        "label": "Italie"
                                                    }, {"key": "JM", "label": "Jama\u00efque"}, {
                                                        "key": "JO",
                                                        "label": "Jordanie"
                                                    }, {"key": "JP", "label": "Japon"}, {
                                                        "key": "KE",
                                                        "label": "Kenya"
                                                    }, {"key": "KG", "label": "Kirghizistan"}, {
                                                        "key": "KH",
                                                        "label": "Cambodge"
                                                    }, {"key": "KI", "label": "Kiribati"}, {
                                                        "key": "KM",
                                                        "label": "Comores"
                                                    }, {"key": "KN", "label": "Saint Kitts et Nevis"}, {
                                                        "key": "KP",
                                                        "label": "Cor\u00e9e du Nord"
                                                    }, {"key": "KR", "label": "Cor\u00e9e du Sud"}, {
                                                        "key": "KW",
                                                        "label": "Kowe\u00eft"
                                                    }, {"key": "KY", "label": "Cayman"}, {
                                                        "key": "KZ",
                                                        "label": "Kazakhstan"
                                                    }, {"key": "LA", "label": "Laos"}, {
                                                        "key": "LB",
                                                        "label": "Liban"
                                                    }, {"key": "LC", "label": "Sainte Lucie"}, {
                                                        "key": "LI",
                                                        "label": "Liechtenstein"
                                                    }, {"key": "LK", "label": "Sri Lanka"}, {
                                                        "key": "LR",
                                                        "label": "Lib\u00e9ria"
                                                    }, {"key": "LS", "label": "Lesotho"}, {
                                                        "key": "LT",
                                                        "label": "Lituanie"
                                                    }, {"key": "LU", "label": "Luxembourg"}, {
                                                        "key": "LV",
                                                        "label": "Lettonie"
                                                    }, {"key": "LY", "label": "Libye"}, {
                                                        "key": "MA",
                                                        "label": "Maroc"
                                                    }, {"key": "MC", "label": "Monaco"}, {
                                                        "key": "MD",
                                                        "label": "Moldavie"
                                                    }, {"key": "MG", "label": "Madagascar"}, {
                                                        "key": "MK",
                                                        "label": "R\u00e9publique de Mac\u00e9doine"
                                                    }, {"key": "MM", "label": "Birmanie"}, {
                                                        "key": "MN",
                                                        "label": "Mongolie"
                                                    }, {"key": "MO", "label": "Macao"}, {
                                                        "key": "MQ",
                                                        "label": "Martinique"
                                                    }, {"key": "MR", "label": "Mauritanie"}, {
                                                        "key": "MS",
                                                        "label": "Montserrat"
                                                    }, {"key": "MT", "label": "Malte"}, {
                                                        "key": "MU",
                                                        "label": "Ile Maurice"
                                                    }, {"key": "MV", "label": "Maldives"}, {
                                                        "key": "MW",
                                                        "label": "Malawi"
                                                    }, {"key": "MX", "label": "Mexice"}, {
                                                        "key": "MY",
                                                        "label": "Malaisie"
                                                    }, {"key": "MZ", "label": "Mozambique"}, {
                                                        "key": "NA",
                                                        "label": "Namibie"
                                                    }, {"key": "NC", "label": "Nouvelle Cal\u00e9donie"}, {
                                                        "key": "NE",
                                                        "label": "Niger"
                                                    }, {"key": "NF", "label": "Norfolk"}, {
                                                        "key": "NG",
                                                        "label": "Nig\u00e9ria"
                                                    }, {"key": "NI", "label": "Nicaragua"}, {
                                                        "key": "NL",
                                                        "label": "Pays-Bas"
                                                    }, {"key": "NO", "label": "Norv\u00e8ge"}, {
                                                        "key": "NP",
                                                        "label": "N\u00e9pal"
                                                    }, {"key": "NR", "label": "Nauru"}, {
                                                        "key": "NU",
                                                        "label": "Niue"
                                                    }, {"key": "NZ", "label": "Nouvelle Z\u00e9lande"}, {
                                                        "key": "OM",
                                                        "label": "Oman"
                                                    }, {"key": "PA", "label": "Panama"}, {
                                                        "key": "PE",
                                                        "label": "P\u00e9rou"
                                                    }, {
                                                        "key": "PF",
                                                        "label": "Polyn\u00e9sie fran\u00e7aise"
                                                    }, {
                                                        "key": "PG",
                                                        "label": "Papouasie Nouvelle Guin\u00e9e"
                                                    }, {"key": "PH", "label": "Philippines"}, {
                                                        "key": "PK",
                                                        "label": "Pakistan"
                                                    }, {"key": "PL", "label": "Plogne"}, {
                                                        "key": "PM",
                                                        "label": "Saint Pierre et Miquelon"
                                                    }, {"key": "PN", "label": "Pitcairn"}, {
                                                        "key": "PR",
                                                        "label": "Porto-Rico"
                                                    }, {"key": "PT", "label": "Portugal"}, {
                                                        "key": "PW",
                                                        "label": "Palau"
                                                    }, {"key": "PY", "label": "Paraguay"}, {
                                                        "key": "QA",
                                                        "label": "Qatar"
                                                    }, {"key": "RE", "label": "R\u00e9union"}, {
                                                        "key": "RO",
                                                        "label": "Roumanie"
                                                    }, {"key": "RU", "label": "Russie"}, {
                                                        "key": "RW",
                                                        "label": "Rwanda"
                                                    }, {"key": "SA", "label": "Arabie Saoudite"}, {
                                                        "key": "SB",
                                                        "label": "Iles Salomon"
                                                    }, {"key": "SC", "label": "Seychelles"}, {
                                                        "key": "SD",
                                                        "label": "Soudan"
                                                    }, {"key": "SE", "label": "Su\u00e8de"}, {
                                                        "key": "SG",
                                                        "label": "Singapour"
                                                    }, {"key": "SH", "label": "Sainte H\u00e9l\u00e8ne"}, {
                                                        "key": "SI",
                                                        "label": "Slov\u00e9nie"
                                                    }, {
                                                        "key": "SJ",
                                                        "label": "Iles Svalbaard et Jan Mayen"
                                                    }, {"key": "SK", "label": "R\u00e9publique Slovaque"}, {
                                                        "key": "SL",
                                                        "label": "Sierra Leone"
                                                    }, {"key": "SM", "label": "San Marin"}, {
                                                        "key": "SN",
                                                        "label": "S\u00e9n\u00e9gal"
                                                    }, {"key": "SO", "label": "Somalie"}, {
                                                        "key": "SR",
                                                        "label": "Surinam"
                                                    }, {
                                                        "key": "ST",
                                                        "label": "Saint Tom\u00e9 et Principe"
                                                    }, {"key": "SV", "label": "El Salvador"}, {
                                                        "key": "SY",
                                                        "label": "Syrie"
                                                    }, {"key": "SZ", "label": "Swaziland"}, {
                                                        "key": "TC",
                                                        "label": "Iles Turques et Ca\u00efques"
                                                    }, {"key": "TD", "label": "Tchad"}, {
                                                        "key": "TF",
                                                        "label": "Territoire austral fran\u00e7ais"
                                                    }, {"key": "TG", "label": "Togo"}, {
                                                        "key": "TH",
                                                        "label": "Tha\u00eflande"
                                                    }, {"key": "TJ", "label": "Tadjikistan"}, {
                                                        "key": "TK",
                                                        "label": "Tokelau"
                                                    }, {"key": "TM", "label": "Turkm\u00e9nistan"}, {
                                                        "key": "TN",
                                                        "label": "Tunisie"
                                                    }, {"key": "TO", "label": "Tonga"}, {
                                                        "key": "TP",
                                                        "label": "Timor oriental"
                                                    }, {"key": "TR", "label": "Turquie"}, {
                                                        "key": "TT",
                                                        "label": "Trinit\u00e9 et Tobago"
                                                    }, {"key": "TV", "label": "Tuvalu"}, {
                                                        "key": "TW",
                                                        "label": "Ta\u00efwan"
                                                    }, {"key": "TZ", "label": "Tanzanie"}, {
                                                        "key": "UA",
                                                        "label": "Ukraine"
                                                    }, {"key": "UK", "label": "Grande-Bretagne"}, {
                                                        "key": "UM",
                                                        "label": "diverses \u00eeles des Etats-Unis"
                                                    }, {"key": "US", "label": "Etats-Unis"}, {
                                                        "key": "UY",
                                                        "label": "Uruguay"
                                                    }, {"key": "UZ", "label": "Ouzb\u00e9kistan"}, {
                                                        "key": "VA",
                                                        "label": "Vatican"
                                                    }, {
                                                        "key": "VC",
                                                        "label": "Saint Vincent et Grenadines"
                                                    }, {"key": "VE", "label": "V\u00e9n\u00e9zuela"}, {
                                                        "key": "VG",
                                                        "label": "Iles Vierges britanniques"
                                                    }, {
                                                        "key": "VI",
                                                        "label": "Iles Vierges des Etats-Unis"
                                                    }, {"key": "VN", "label": "Vietnam"}, {
                                                        "key": "VU",
                                                        "label": "Vanuatu"
                                                    }, {"key": "WF", "label": "Wallis et Futuna"}, {
                                                        "key": "WS",
                                                        "label": "Samoa occidental"
                                                    }, {"key": "YE", "label": "Yemen"}, {
                                                        "key": "YT",
                                                        "label": "Mayotte"
                                                    }, {"key": "YU", "label": "ex-Yougoslavie"}, {
                                                        "key": "ZA",
                                                        "label": "Afrique du Sud"
                                                    }, {"key": "ZM", "label": "Zambie"}, {
                                                        "key": "ZR",
                                                        "label": "Za\u00efre (R\u00e9publique D\u00e9mocratique du Congo)"
                                                    }, {"key": "ZW", "label": "Zimbabwe"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsauto"
                                                },
                                                "zoo_enumsvertical": {
                                                    "id": "zoo_enumsvertical",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 42,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "0", "label": "0 %"}, {
                                                        "key": "30",
                                                        "label": "30 %"
                                                    }, {"key": "70", "label": "70 %"}, {
                                                        "key": "100",
                                                        "label": "100 %"
                                                    }],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsvertical"
                                                },
                                                "zoo_enumshorizontal": {
                                                    "id": "zoo_enumshorizontal",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 43,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumItems": [{"key": "red", "label": "Rouge"}, {
                                                        "key": "yellow",
                                                        "label": "Jaune"
                                                    }, {"key": "green", "label": "Vert"}, {
                                                        "key": "blue",
                                                        "label": "Bleu"
                                                    }, {
                                                        "key": "lightblue",
                                                        "label": "Bleu\/Bleu ciel"
                                                    }, {"key": "navyblue", "label": "Bleu\/Bleu marine"}],
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumshorizontal"
                                                }
                                            }
                                        },
                                        "zoo_fr_enumservermultiple": {
                                            "id": "zoo_fr_enumservermultiple",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9s server simple",
                                            "type": "frame",
                                            "logicalOrder": 44,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_enumsserverlist": {
                                                    "id": "zoo_enumsserverlist",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s liste",
                                                    "type": "enum",
                                                    "logicalOrder": 45,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserverlist"
                                                },
                                                "zoo_enumsserverauto": {
                                                    "id": "zoo_enumsserverauto",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s auto",
                                                    "type": "enum",
                                                    "logicalOrder": 46,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserverauto"
                                                },
                                                "zoo_enumsserververtical": {
                                                    "id": "zoo_enumsserververtical",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s vertical",
                                                    "type": "enum",
                                                    "logicalOrder": 47,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserververtical"
                                                },
                                                "zoo_enumsserverhorizontal": {
                                                    "id": "zoo_enumsserverhorizontal",
                                                    "visibility": "W",
                                                    "label": "Des \u00e9num\u00e9r\u00e9s horizontal",
                                                    "type": "enum",
                                                    "logicalOrder": 48,
                                                    "multiple": true,
                                                    "options": {"bmenu": "no", "eformat": "auto", "multiple": "yes"},
                                                    "needed": false,
                                                    "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enumsserverhorizontal"
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_date": {
                                    "id": "zoo_t_tab_date",
                                    "visibility": "W",
                                    "label": "Les dates",
                                    "type": "tab",
                                    "logicalOrder": 49,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_date": {
                                            "id": "zoo_frame_date",
                                            "visibility": "W",
                                            "label": "Date, heures & date avec l'heure",
                                            "type": "frame",
                                            "logicalOrder": 50,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_dates": {
                                                    "id": "zoo_array_dates",
                                                    "visibility": "W",
                                                    "label": "Le temps",
                                                    "type": "array",
                                                    "logicalOrder": 51,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_date_array": {
                                                            "id": "zoo_date_array",
                                                            "visibility": "W",
                                                            "label": "Des dates",
                                                            "type": "date",
                                                            "logicalOrder": 52,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_time_array": {
                                                            "id": "zoo_time_array",
                                                            "visibility": "W",
                                                            "label": "Des heures",
                                                            "type": "time",
                                                            "logicalOrder": 53,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_timestamp_array": {
                                                            "id": "zoo_timestamp_array",
                                                            "visibility": "W",
                                                            "label": "Des dates avec l'heure",
                                                            "type": "timestamp",
                                                            "logicalOrder": 54,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_relations": {
                                    "id": "zoo_t_tab_relations",
                                    "visibility": "W",
                                    "label": "Les relations",
                                    "type": "tab",
                                    "logicalOrder": 55,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_relation": {
                                            "id": "zoo_frame_relation",
                                            "visibility": "W",
                                            "label": "Relations \u00e0 entretenir",
                                            "type": "frame",
                                            "logicalOrder": 56,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_docid": {
                                                    "id": "zoo_array_docid",
                                                    "visibility": "W",
                                                    "label": "Les documents",
                                                    "type": "array",
                                                    "logicalOrder": 57,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_docid_array": {
                                                            "id": "zoo_docid_array",
                                                            "visibility": "W",
                                                            "label": "Des documents",
                                                            "type": "docid",
                                                            "logicalOrder": 58,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_docid_multiple_array": {
                                                            "id": "zoo_docid_multiple_array",
                                                            "visibility": "W",
                                                            "label": "Encore plus de documents",
                                                            "type": "docid",
                                                            "logicalOrder": 59,
                                                            "multiple": true,
                                                            "options": {"multiple": "yes"},
                                                            "needed": false
                                                        }
                                                    }
                                                },
                                                "zoo_array_account": {
                                                    "id": "zoo_array_account",
                                                    "visibility": "W",
                                                    "label": "Les comptes",
                                                    "type": "array",
                                                    "logicalOrder": 60,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_account_array": {
                                                            "id": "zoo_account_array",
                                                            "visibility": "W",
                                                            "label": "Des comptes",
                                                            "type": "account",
                                                            "logicalOrder": 61,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false,
                                                            "helpOutputs": ["zoo_account_array"]
                                                        },
                                                        "zoo_account_multiple_array": {
                                                            "id": "zoo_account_multiple_array",
                                                            "visibility": "W",
                                                            "label": "Encore plus de comptes",
                                                            "type": "account",
                                                            "logicalOrder": 62,
                                                            "multiple": true,
                                                            "options": {"multiple": "yes"},
                                                            "needed": false,
                                                            "helpOutputs": ["zoo_account_multiple_array"]
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_numbers": {
                                    "id": "zoo_t_tab_numbers",
                                    "visibility": "W",
                                    "label": "Les nombres",
                                    "type": "tab",
                                    "logicalOrder": 63,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_numbers": {
                                            "id": "zoo_frame_numbers",
                                            "visibility": "W",
                                            "label": "Entier, d\u00e9cimaux et monnaie",
                                            "type": "frame",
                                            "logicalOrder": 64,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_numbers": {
                                                    "id": "zoo_array_numbers",
                                                    "visibility": "W",
                                                    "label": "Quelques nombres",
                                                    "type": "array",
                                                    "logicalOrder": 65,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_double_array": {
                                                            "id": "zoo_double_array",
                                                            "visibility": "W",
                                                            "label": "Des d\u00e9cimaux",
                                                            "type": "double",
                                                            "logicalOrder": 66,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_integer_array": {
                                                            "id": "zoo_integer_array",
                                                            "visibility": "W",
                                                            "label": "Des entiers",
                                                            "type": "int",
                                                            "logicalOrder": 67,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_money_array": {
                                                            "id": "zoo_money_array",
                                                            "visibility": "W",
                                                            "label": "Des sous",
                                                            "type": "money",
                                                            "logicalOrder": 68,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_misc": {
                                    "id": "zoo_t_tab_misc",
                                    "visibility": "W",
                                    "label": "Divers",
                                    "type": "tab",
                                    "logicalOrder": 69,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_misc": {
                                            "id": "zoo_frame_misc",
                                            "visibility": "W",
                                            "label": "\u00c9num\u00e9r\u00e9, couleur et mot de passe",
                                            "type": "frame",
                                            "logicalOrder": 70,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_misc": {
                                                    "id": "zoo_array_misc",
                                                    "visibility": "W",
                                                    "label": "Quelques diverses donn\u00e9es",
                                                    "type": "array",
                                                    "logicalOrder": 71,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_enum_array": {
                                                            "id": "zoo_enum_array",
                                                            "visibility": "W",
                                                            "label": "Des \u00e9num\u00e9r\u00e9s",
                                                            "type": "enum",
                                                            "logicalOrder": 72,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false,
                                                            "enumItems": [{"key": "0", "label": "0 %"}, {
                                                                "key": "30",
                                                                "label": "30 %"
                                                            }, {"key": "70", "label": "70 %"}, {
                                                                "key": "100",
                                                                "label": "100 %"
                                                            }],
                                                            "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enum_array"
                                                        },
                                                        "zoo_enums_array": {
                                                            "id": "zoo_enums_array",
                                                            "visibility": "W",
                                                            "label": "Encore plus d'\u00e9num\u00e9r\u00e9s",
                                                            "type": "enum",
                                                            "logicalOrder": 73,
                                                            "multiple": true,
                                                            "options": {"multiple": "yes"},
                                                            "needed": false,
                                                            "enumItems": [{"key": "0", "label": "0 %"}, {
                                                                "key": "30",
                                                                "label": "30 %"
                                                            }, {"key": "70", "label": "70 %"}, {
                                                                "key": "100",
                                                                "label": "100 %"
                                                            }],
                                                            "enumUri": "\/dynacase\/api\/v1\/families\/ZOO_ALLTYPE\/enumerates\/zoo_enums_array"
                                                        },
                                                        "zoo_color_array": {
                                                            "id": "zoo_color_array",
                                                            "visibility": "W",
                                                            "label": "Des couleurs",
                                                            "type": "color",
                                                            "logicalOrder": 74,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_password_array": {
                                                            "id": "zoo_password_array",
                                                            "visibility": "W",
                                                            "label": "Des mots de passe",
                                                            "type": "password",
                                                            "logicalOrder": 75,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_files": {
                                    "id": "zoo_t_tab_files",
                                    "visibility": "W",
                                    "label": "Les fichiers",
                                    "type": "tab",
                                    "logicalOrder": 76,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_files": {
                                            "id": "zoo_frame_files",
                                            "visibility": "W",
                                            "label": "Fichiers & images",
                                            "type": "frame",
                                            "logicalOrder": 77,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_files": {
                                                    "id": "zoo_array_files",
                                                    "visibility": "W",
                                                    "label": "Quelques fichiers",
                                                    "type": "array",
                                                    "logicalOrder": 78,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_file_array": {
                                                            "id": "zoo_file_array",
                                                            "visibility": "W",
                                                            "label": "Des fichiers",
                                                            "type": "file",
                                                            "logicalOrder": 79,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_image_array": {
                                                            "id": "zoo_image_array",
                                                            "visibility": "W",
                                                            "label": "Des images",
                                                            "type": "image",
                                                            "logicalOrder": 80,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                "zoo_t_tab_texts": {
                                    "id": "zoo_t_tab_texts",
                                    "visibility": "W",
                                    "label": "Les textes",
                                    "type": "tab",
                                    "logicalOrder": 81,
                                    "multiple": false,
                                    "options": [],
                                    "content": {
                                        "zoo_frame_texts": {
                                            "id": "zoo_frame_texts",
                                            "visibility": "W",
                                            "label": "Les textes non format\u00e9s",
                                            "type": "frame",
                                            "logicalOrder": 82,
                                            "multiple": false,
                                            "options": [],
                                            "content": {
                                                "zoo_array_texts": {
                                                    "id": "zoo_array_texts",
                                                    "visibility": "W",
                                                    "label": "Textes simples et multilignes",
                                                    "type": "array",
                                                    "logicalOrder": 83,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_text_array": {
                                                            "id": "zoo_text_array",
                                                            "visibility": "W",
                                                            "label": "Des textes",
                                                            "type": "text",
                                                            "logicalOrder": 84,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        },
                                                        "zoo_longtext_array": {
                                                            "id": "zoo_longtext_array",
                                                            "visibility": "W",
                                                            "label": "Des textes multiligne",
                                                            "type": "longtext",
                                                            "logicalOrder": 85,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                },
                                                "zoo_array_html": {
                                                    "id": "zoo_array_html",
                                                    "visibility": "W",
                                                    "label": "Les textes HTML",
                                                    "type": "array",
                                                    "logicalOrder": 86,
                                                    "multiple": false,
                                                    "options": [],
                                                    "needed": false,
                                                    "content": {
                                                        "zoo_htmltext_array": {
                                                            "id": "zoo_htmltext_array",
                                                            "visibility": "W",
                                                            "label": "Des textes format\u00e9s",
                                                            "type": "htmltext",
                                                            "logicalOrder": 87,
                                                            "multiple": true,
                                                            "options": [],
                                                            "needed": false
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "locale": {
                        "label": "Fran\u00e7ais",
                        "localeLabel": "Fran\u00e7ais",
                        "flag": "",
                        "locale": "fr",
                        "culture": "fr-FR",
                        "dateFormat": "%d\/%m\/%Y",
                        "dateTimeFormat": "%d\/%m\/%Y %H:%M",
                        "timeFormat": "%H:%M:%S"
                    },
                    "style": {
                        "css": [{
                            "path": "css\/dcp\/document\/bootstrap.css?ws=3228",
                            "key": "bootstrap"
                        }, {
                            "path": "css\/dcp\/document\/kendo.css?ws=3228",
                            "key": "kendo"
                        }, {
                            "path": "css\/dcp\/document\/document.css?ws=3228",
                            "key": "document"
                        }, {
                            "path": "lib\/jquery-dataTables\/1.10\/bootstrap\/3\/dataTables.bootstrap.css?ws=3228",
                            "key": "datatable"
                        }]
                    },
                    "script": {"js": []}
                },
                "properties": {
                    "requestIdentifier": "!defaultEdition",
                    "uri": "\/dynacase\/api\/v1\/documents\/1081\/views\/!coreEdition",
                    "identifier": "!coreEdition",
                    "mode": "edition",
                    "label": "Vue de consultation core",
                    "isDisplayable": false,
                    "order": 0,
                    "menu": "",
                    "mask": {"id": 0, "title": ""}
                }
            }
        }
    }
});
