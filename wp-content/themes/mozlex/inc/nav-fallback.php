<?php
/**
 * Fallback nav khi chưa tạo menu trong Appearance → Menus.
 */
function mozlex_default_nav( $args = array() ) {
	$menu_class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'nav-list';
	$is_drawer = ( 'drawer-list' === $menu_class );
	$map = array(
		'/'                      => array( 'TRANG CHỦ', 'menu_trangchu' ),
		'/ve-mozlex/'            => array( 'GIỚI THIỆU', 'menu_gioithieu' ),
		'/san-pham/'             => array( 'SẢN PHẨM', 'menu_sanpham' ),
		'/linh-vuc-hoat-dong/'    => array( 'LĨNH VỰC HOẠT ĐỘNG', 'menu_linhvuc' ),
		'/tin-tuc/'              => array( 'TIN TỨC', 'menu_tintuc' ),
		'/lien-he/'              => array( 'LIÊN HỆ', 'menu_lienhe' ),
	);
	echo '<ul class="' . esc_attr( $menu_class ) . '">';
	foreach ( $map as $path => $data ) {
		list( $label, $opt ) = $data;
		$show = mozlex_opt( $opt, '1' );
		if ( '0' === $show ) continue;
		$is_current = ( '/' === $path ) ? is_front_page() : ( $_SERVER['REQUEST_URI'] ?? '' ) === $path;
		if ( '/san-pham/' === $path ) {
			// Lấy tất cả danh mục cha để linh hoạt: thêm Sơn, Sơn đỏ... tự hiện
			$top_cats = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name', 'order' => 'ASC', 'number' => 20 ) );
			// Đưa Thiết bị khác xuống cuối, Công nghệ/Thương mại/Xây dựng lên đầu và cho màu đen
			usort( $top_cats, function($a,$b){
				if($a->slug==='thiet-bi-khac' && $b->slug!=='thiet-bi-khac') return 1;
				if($b->slug==='thiet-bi-khac' && $a->slug!=='thiet-bi-khac') return -1;
				$order=['cong-nghe'=>1,'thuong-mai'=>2,'xay-dung'=>3];
				$oa=$order[$a->slug]??99; $ob=$order[$b->slug]??99;
				if($oa!==$ob) return $oa<=>$ob;
				return strcmp($a->name,$b->name);
			});
			if ( is_wp_error( $top_cats ) ) $top_cats = array();
			if ( $is_drawer ) {
				// Mobile drawer — accordion cho mọi danh mục cha
				printf( '<li class="has-children%s"><a href="%s">%s</a>', $is_current ? ' is-current' : '', esc_url( home_url( $path ) ), esc_html( $label ) );
				echo '<ul class="drawer-sub">';
				if ( $top_cats ) {
					foreach ( $top_cats as $tc ) {
						$children = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => (int) $tc->term_id, 'orderby' => 'name' ) );
						if ( $children && ! is_wp_error( $children ) && count( $children ) ) {
							printf( '<li class="has-children"><a href="%s">%s</a><ul class="drawer-sub">', esc_url( get_term_link( $tc ) ), esc_html( $tc->name ) );
							foreach ( $children as $child ) printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $child ) ), esc_html( $child->name ) );
							echo '</ul></li>';
						} else {
							printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $tc ) ), esc_html( $tc->name ) );
						}
					}
				} else {
					echo '<li><a href="' . esc_url( home_url( '/san-pham/' ) ) . '">Tất cả sản phẩm</a></li>';
				}
				echo '</ul></li>';
			} else {
				// Desktop — mega linh hoạt: mỗi danh mục cha là 1 ô, có con thì hiện sub
				printf( '<li class="has-mega%s"><a href="%s">%s</a>', $is_current ? ' is-current' : '', esc_url( home_url( $path ) ), esc_html( $label ) );
				echo '<div class="mega-menu" aria-hidden="true"><div class="mega-inner">';
				if ( $top_cats ) {
					foreach ( array_slice( $top_cats, 0, 8 ) as $tc ) {
						$children = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => (int) $tc->term_id, 'orderby' => 'name' ) );
						if ( $children && ! is_wp_error( $children ) && count( $children ) ) {
							echo '<div class="mega-group">';
							printf( '<a class="mega-cat mega-parent" href="%s"><span>%s</span><span class="mega-arrow">›</span></a>', esc_url( get_term_link( $tc ) ), esc_html( $tc->name ) );
							echo '<div class="mega-sub">';
							foreach ( $children as $child ) {
								$cnt = (int) $child->count;
								printf( '<a class="mega-subcat" href="%s">%s%s</a>', esc_url( get_term_link( $child ) ), esc_html( $child->name ), $cnt ? ' <small>'.$cnt.'</small>' : '' );
							}
							echo '</div></div>';
						} else {
							$cnt = (int) $tc->count;
							printf( '<a class="mega-cat" href="%s"><span>%s</span>%s</a>', esc_url( get_term_link( $tc ) ), esc_html( $tc->name ), $cnt ? '<small>'.$cnt.' SP</small>' : '' );
						}
					}
				} else {
					echo '<a class="mega-cat" href="' . esc_url( home_url( '/san-pham/' ) ) . '"><span>Tất cả sản phẩm</span><small>Xem</small></a>';
				}
				echo '</div></div></li>';
			}
		} else {
			printf(
				'<li%s><a href="%s">%s</a></li>',
				$is_current ? ' class="is-current"' : '',
				esc_url( home_url( $path ) ),
				esc_html( $label )
			);
		}
	}
	echo '</ul>';
}
