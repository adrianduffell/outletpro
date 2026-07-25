<?php
/**
 * Content for outletpro/outlet-sort-filter pattern.
 *
 * Keep to 40 characters wide and 2-space indents for nice display in the block editor.
 *
 * @package OutletPro
 *
 * @phpcs:disable
 */

$outletpro_orderby_id = uniqid('outletpro-orderby-');
?>
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group"><!-- wp:html -->
<style data-wp-block-html="css">
body[data-resizable-iframe-connected]{
  text-align: right;
}
</style>

<script data-wp-block-html="js">
document.addEventListener(
  'DOMContentLoaded',
  function() {
    const select =
      document.getElementById(
        '<?php echo esc_js( $outletpro_orderby_id ); ?>'
      );

    if ( ! select ) {
      return;
    }

    const url = new URL(
      window.location.href
    );

    const currentOrderby =
      url.searchParams.get(
        'orderby'
      ) || '';

    select.value = currentOrderby;
    select.addEventListener(
      'change',
      function() {
        if ( select.value ) {
          url.searchParams.set(
            'orderby',
            select.value
          );
        } else {
          url.searchParams.delete(
            'orderby'
          );
        }

        Array.from( url.searchParams.keys() )
          .filter(
            ( key ) =>
              /^query-\d+-page$/.test( key )
          )
          .forEach(
            ( key ) => url.searchParams.delete( key )
          );

        window.location.href =
          url.toString();
      }
    );
  }
);
</script>

<select
  id="<?php echo esc_attr( $outletpro_orderby_id ); ?>"
  aria-label="<?php echo esc_html__( 'Sort', 'outletpro' ); ?>"
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
