export default (searchId, $target) => {
  var $r = $.Deferred();
  $target.find(".search-grid").remove();
  $target.removeClass("result--on");
  var $table = $(
    `<ank-se-grid url-config="/api/v2/smartstructures/dsearch/gridConfig/<collection>" server-paging="true" server-sorting="true" server-filtering="true" collection="${searchId}" class="search-grid">
      </ank-se-grid>`
  );
  $target.append($table);
  $target.removeClass("result--waiting").addClass("result--on");
  $r.resolve();
  return $r;
};
