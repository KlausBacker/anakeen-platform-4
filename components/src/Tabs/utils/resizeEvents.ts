export function addResizeListener(
  $el: Element,
  update: (...args: any[]) => void
) {
  $(window).resize(update);
}
