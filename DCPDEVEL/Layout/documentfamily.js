$(document).ready(() => {
  const $resultzone = $('.resultzone');
  const $form = $('.searchform');
  const $inputs = $('.searchform input');
  const $searching = $('.searching');
  const $export = $('.searchexport');
  const $configExport = $('.configexport');

  $('.searchzone input').button();
  $searching.dialog({ title: null, resizable: false, autoOpen: false });
  $('.ui-dialog-titlebar').hide();

  $('.exportinput').buttonset();
  $export.button({ icon: 'ui-icon-extlink' });
  $configExport.button({ icon: 'ui-icon-gear' });

  $resultzone.on('refresh', (event, options) => {
    let data = {};
    const template = $('#zoneItem').html();
    let isWaitingClose = false;

    if (!$form.data('originalAction')) {
      $form.data('originalAction', $form.attr('action'));
    }
    data = {};
    $inputs.each((index, input) => {
      if ($(input).val() && !$(input).attr('readonly')) {
        data[$(input).attr('name')] = $(input).val();
      }
    });


        // View waiting message if waiting mode 300ms
    window.setTimeout(() => {
      if (!isWaitingClose) {
        if (!$searching.dialog('isOpen')) {
          $searching.dialog('open');
        }
        $('.ui-dialog').show();
        $searching.dialog('option', 'position', { my: 'center top+50', at: 'center', of: $resultzone });
      }
    }, 300);


    $.ajax({
      url: $form.attr('action'),
      type: $form.attr('method'),
      dataType: 'json', // selon le retour attendu
      data,
    }).done((data) => {
            //  $waiting.dialog("close");
      $('.ui-dialog').hide(); isWaitingClose = true;
      const content = Mustache.render(template, data);

      if (options && options.append) {
        $resultzone.append(content);
      } else {
        $resultzone.html(content);
      }
      $resultzone.find('button').button();
    }).fail((response) => {
            // $waiting.dialog("close");

      $('.ui-dialog').hide(); isWaitingClose = true;
      $resultzone.text(response.contentText);
      console.error(response);
    });
  });

  $resultzone.trigger('refresh');

  $resultzone.on('click', '.document-button', function () {
    const url = `?app=FDL&action=FDL_CARD&id=${$(this).data('docid')}`;
    window.open(url, '_blank');
  });
  $resultzone.on('click', '.document-next', function () {
    const url = $(this).data('url');
    $form.attr('action', url);
    $(this).hide();
    $resultzone.trigger('refresh', { append: true });
  });

  $inputs.on('focus', function () {
    $inputs.attr('readonly', 'readonly');
    $inputs.addClass('search-disabled');
    $(this).removeClass('search-disabled');
    $(this).removeAttr('readonly');
    if ($(this).val()) {
      $form.attr('action', $form.data('originalAction'));
      $resultzone.trigger('refresh');
    }
  });
  $inputs.on('keyup change', () => {
    $resultzone.trigger('refresh');
  });

  $export.on('click', () => {
    const family = $('meta[name=family]').attr('content');
    let url = `?app=DCPDEVEL&action=EXPORTDOCUMENTS&family=${family}`;
    const $csvOption = $('.exportform select');
    $inputs.each((index, input) => {
      if ($(input).val() && !$(input).attr('readonly')) {
        url += `&${$(input).attr('name')}=${encodeURIComponent($(input).val())}`;
      }
    });
    $csvOption.each((index, input) => {
      if ($(input).val()) {
        url += `&${$(input).attr('name')}=${encodeURIComponent($(input).val())}`;
      }
    });
    window.open(url, '_blank');
  });

    /**
     * workaround bug jquery ui for select in dialog
     */
  $('.exportform select').selectmenu({
    open(event, ui) {
      const $dialog = $(event.target).closest('.ui-dialog');

      if ($dialog.length > 0) {
        const zIndex = parseInt($dialog.css('z-index'));
        const $menu = $(event.target).selectmenu('menuWidget').parent();

        if (zIndex > 100) {
          $dialog.css('z-index', 101);
        }
        $menu.css('z-index', zIndex + 1);
      }
    },
  });

  $configExport.on('click', () => {
    $('.exportform').dialog({ modal: true });
  });
});
