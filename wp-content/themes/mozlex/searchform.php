<?php
/** Accessible product search, shared by search and error pages. */
$field_id = wp_unique_id( 'product-search-' );
?>
<form role="search" method="get" class="page-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr-only" for="<?php echo esc_attr( $field_id ); ?>"><?php esc_html_e( 'Tìm kiếm sản phẩm', 'mozlex' ); ?></label>
	<div class="page-search-input-wrap">
		<svg class="page-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
		<input type="search" id="<?php echo esc_attr( $field_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Tìm tên hoặc model sản phẩm…', 'mozlex' ); ?>" autocomplete="off">
		<button type="button" class="search-clear-btn" aria-label="<?php esc_attr_e( 'Xóa từ khóa', 'mozlex' ); ?>" <?php echo empty( get_search_query() ) ? 'hidden' : ''; ?>>&times;</button>
	</div>
	<input type="hidden" name="post_type" value="product">
	<?php if ( is_search() && get_query_var( 'product_category' ) ) : ?><input type="hidden" name="product_category" value="<?php echo esc_attr( get_query_var( 'product_category' ) ); ?>"><?php endif; ?>
	<button type="submit" class="btn btn-ink"><?php esc_html_e( 'Tìm kiếm', 'mozlex' ); ?></button>
</form>
