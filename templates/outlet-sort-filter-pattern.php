<?php
/**
 * Content for wc-outlet/outlet-sort-filter pattern.
 *
 * Keep to 40 characters wide and 2-space indents for nice display in the block editor.
 *
 * @package WC_Outlet
 *
 * @phpcs:disable
 */
?>
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group"><!-- wp:html -->
<style data-wp-block-html="css">
[data-wc-outlet-id="orderby"] {
  width: auto;
}
</style>

<script data-wp-block-html="js">
document.addEventListener(
  'DOMContentLoaded',
  function() {
    const selects =
      document.querySelectorAll(
        '[data-wc-outlet-id="orderby"]'
      );

    if ( selects.length === 0 ) {
      return;
    }

    const url = new URL(
      window.location.href
    );

    const currentOrderby =
      url.searchParams.get(
        'orderby'
      ) || '';

    selects.forEach(
      function( select ) {
        select.value = currentOrderby;
        select.addEventListener(
          'change',
          function() {
            url.searchParams.set(
              'orderby',
              select.value
            );

            url.searchParams.delete(
              'paged'
            );

            window.location.href =
              url.toString();
          }
        );
      }
    );
  }
);
</script>

<select
  data-wc-outlet-id="orderby"
  aria-label="<?php echo esc_html__( 'Sort', 'wc-outlet' ); ?>"
>
  <option value="">
    <?php echo esc_html__( 'Default sorting', 'woocommerce' ); ?>
  </option>
  <option value="popularity">
      <?php echo esc_html__( 'Sort by popularity', 'woocommerce' ); ?>
  </option>
  <option value="rating">
    <?php echo esc_html__( 'Sort by average rating', 'woocommerce' ); ?>
  </option>
  <option value="date">
    <?php echo esc_html__( 'Sort by latest', 'woocommerce' ); ?>
  </option>
  <option value="price">
    <?php echo esc_html__( 'Sort by price: low to high', 'woocommerce' ); ?>
   </option>
   <option value="price-desc">
    <?php echo esc_html__( 'Sort by price: high to low', 'woocommerce' ); ?>
  </option>
</select>
<!-- /wp:html --></div>
<!-- /wp:group -->
