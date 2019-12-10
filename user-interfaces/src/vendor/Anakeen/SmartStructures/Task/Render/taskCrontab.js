import Mustache from "mustache";
import $ from "jquery";

window.ank.smartElement.globalController.registerFunction("taskRenderEdit", controller => {
  controller.addEventListener(
    "smartFieldBeforeRender",
    {
      smartFieldCheck: field => field.id === "task_crontab"
    },
    (event, sEl, sField) => {
      let cValue = sField.getValue();
      if (cValue && cValue.value) {
        let parts = cValue.value.split(/\s+/);
        cValue.crontab = {
          minutes: parts[0],
          hours: parts[1],
          days: parts[2],
          months: parts[3],
          weekDays: parts[4]
        };
        sField.setValue(cValue);
      }
    }
  );

  controller.addEventListener(
    "smartFieldReady",
    {
      smartFieldCheck: field => field.id === "task_crontab"
    },
    (event, sElement, sField, $el) => {
      let $input = $el.find("input.dcpAttribute__value");
      const refreshCrontab = () => {
        let cValue = $input.val();
        let $crontab = $(event.target).find(".task-crontab-info");
        let $human = $(event.target).find(
          ".dcpAttribute__value[data-attrid=task_humancrontab] .dcpAttribute__content__value"
        );

        let tpl = sField.getOption("template");
        if (cValue) {
          $.getJSON("/api/v2/admin/task/crontab/" + cValue)
            .done(function(response) {
              let dataValue = {
                value: cValue,
                crontab: response.data.parts,
                dates: response.data.dates
              };
              let $tplR = $(
                Mustache.render(tpl, {
                  attribute: {
                    attributeValue: dataValue
                  }
                })
              );
              $tplR.insertBefore($crontab);
              $crontab.remove();
              $human.text(response.data.human);
              $human
                .closest(".dcpCustomTemplate")
                .find(".task-dates")
                .html($tplR.find(".task-next-dates"));
              $human.closest(".dcpAttribute__content").removeClass("has-error");
              $el.removeClass("has-error");
            })
            .fail(function(response) {
              if (response.responseJSON && response.responseJSON.message) {
                $human.text(response.responseJSON.message);
              } else {
                $human.text(response.responseText);
              }
              $human
                .closest(".dcpCustomTemplate")
                .find(".task-dates")
                .html("");
              $human.closest(".dcpAttribute__content").addClass("has-error");
              $el.addClass("has-error");
            });
        } else {
          $human
            .closest(".dcpCustomTemplate")
            .find(".task-dates")
            .html("");
          $el.find(".task-next-dates").html("");
          $human.text("");
          $el.find(".task-crontab-value").html("");
          $el.removeClass("has-error");
          $human.closest(".dcpAttribute__content").removeClass("has-error");
        }
      };
      if ($el.find(".dcpAttribute__description").length === 2) {
        $el
          .find(".dcpAttribute__description")
          .get(1)
          .remove();
      }

      $input.on("keyup blur", function() {
        refreshCrontab();
      });
      window.setTimeout(refreshCrontab, 100);
    }
  );
});
