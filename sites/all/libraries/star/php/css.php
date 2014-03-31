<?php

/**
 * Convert an array of style settings into an inline style attribute.
 *
 * @param array $styles
 * @return string
 */
function array_to_inline_style($styles) {
  $html = '';
  foreach ($styles as $property => $value) {
    $html .= "$property: $value; ";
  }
  return trim($html);
}
