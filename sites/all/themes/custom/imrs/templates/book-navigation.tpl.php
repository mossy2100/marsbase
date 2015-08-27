<?php

/**
 * @file
 * Default theme implementation to navigate books.
 *
 * Presented under nodes that are a part of book outlines.
 *
 * Available variables:
 * - $tree: The immediate children of the current node rendered as an unordered
 *   list.
 * - $current_depth: Depth of the current node within the book outline. Provided
 *   for context.
 * - $prev_url: URL to the previous node.
 * - $prev_title: Title of the previous node.
 * - $parent_url: URL to the parent node.
 * - $parent_title: Title of the parent node. Not printed by default. Provided
 *   as an option.
 * - $next_url: URL to the next node.
 * - $next_title: Title of the next node.
 * - $has_links: Flags TRUE whenever the previous, parent or next data has a
 *   value.
 * - $book_id: The book ID of the current outline being viewed. Same as the node
 *   ID containing the entire outline. Provided for context.
 * - $book_url: The book/node URL of the current outline being viewed. Provided
 *   as an option. Not used by default.
 * - $book_title: The book/node title of the current outline being viewed.
 *   Provided as an option. Not used by default.
 *
 * @see template_preprocess_book_navigation()
 *
 * @ingroup themeable
 */
?>
<?php if ($tree || $has_links): ?>
  <div id="book-navigation-<?php print $book_id; ?>" class="book-navigation">
    <?php print $tree; ?>

    <?php if ($has_links): ?>
    <table class="book-navigation__links">
      <tr>
      <?php if ($prev_url): ?>
        <td class="td-book-navigation__previous">
          <a href="<?php print $prev_url; ?>" class="book-navigation__previous" title="<?php print t('Go to previous section'); ?>">&larr;&nbsp;<?php print $prev_title; ?></a>
        </td>
      <?php endif; ?>
      <?php if ($parent_url): ?>
        <td class="td-book-navigation__up">
          <a href="<?php print $parent_url; ?>" class="book-navigation__up" title="<?php print t('Go to Contents'); ?>">Contents&nbsp;&uarr;</a>
        </td>
      <?php endif; ?>
      <?php if ($next_url): ?>
        <td class="td-book-navigation__next">
          <a href="<?php print $next_url; ?>" class="book-navigation__next" title="<?php print t('Go to next section'); ?>"><?php print $next_title; ?>&nbsp;&rarr;</a>
        </td>
      <?php endif; ?>
      </tr>
    </table>
    <?php endif; ?>

  </div>
<?php endif; ?>
