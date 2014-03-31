
/**
 * Get the absolute position of an element.
 * i.e. the position relative to the top-left corner of the document.
 *
 * @param   object  el  Document element
 * @return  object      The position info
 */
function absolutePosition(el) {
  var left = 0;
  var top = 0;
  while (el != null) {
    left += el.offsetLeft;
    top += el.offsetTop;
    el = el.offsetParent;
  }
  return {
    left: left,
    top: top
  };
}
