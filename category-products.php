<?php
/**
 * Template: Bagisto Category Products Page
 * Description: Displays products for a single Bagisto category based on slug in /category/{slug}
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$category_slug = get_query_var( 'bagisto_category_slug' );

if ( empty( $category_slug ) ) {
    ?>
    <main class="site-main nailedit-products-page max-w-[1200px] mx-auto">
        <div class="nailedit-error">
            <?php esc_html_e( 'Category not specified.', 'nailedit' ); ?>
        </div>
    </main>
    <?php
    get_footer();
    exit;
}

if ( function_exists( 'nailedit_get_local_api_base' ) ) {
    $base = nailedit_get_local_api_base();
} else {
    $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
}

// Override API base for VPS deployment
$current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
if (strpos($current_host, '45.93.139.96') !== false) {
    $base = 'http://45.93.139.96:8088/api/';
}

$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page     = 12;

$categories_url = add_query_arg(
    array(
        'page'  => 1,
        'limit' => 500,
    ),
    $base . 'v1/categories'
);

$cat_response = wp_remote_get(
    $categories_url,
    array( 'timeout' => 15 )
);

if ( is_wp_error( $cat_response ) ) {
    ?>
    <main class="site-main nailedit-products-page max-w-[1200px] mx-auto">
        <div class="nailedit-error">
            <?php echo esc_html( $cat_response->get_error_message() ); ?>
        </div>
    </main>
    <?php
    get_footer();
    exit;
}

$cat_body = wp_remote_retrieve_body( $cat_response );
$cat_data = json_decode( $cat_body, true );

$category_id   = 0;
$category_name = '';

if ( isset( $cat_data['data'] ) && is_array( $cat_data['data'] ) ) {
    foreach ( $cat_data['data'] as $cat ) {
        if ( isset( $cat['slug'] ) && $cat['slug'] === $category_slug ) {
            $category_id   = isset( $cat['id'] ) ? (int) $cat['id'] : 0;
            $category_name = isset( $cat['name'] ) ? $cat['name'] : $category_slug;
            break;
        }
    }
}


// TEMP DEBUG: show resolved category info in HTML source
echo '<!-- NAILEDIT CATEGORY DEBUG: slug=' . esc_html( $category_slug ) . ' id=' . (int) $category_id . ' name=' . esc_html( $category_name ) . ' -->';

if ( ! $category_id ) {
    ?>
    <main class="site-main nailedit-products-page max-w-[1200px] mx-auto">
        <div class="nailedit-no-products">
            <p><?php esc_html_e( 'Category not found.', 'nailedit' ); ?></p>
        </div>
    </main>
    <?php
    get_footer();
    exit;
}

// Determine if current category has child categories
$child_categories = array();
$has_children     = false;
$products         = array();
$total_pages      = 1;
$total_items      = 0;

if ( isset( $cat_data['data'] ) && is_array( $cat_data['data'] ) ) {
	foreach ( $cat_data['data'] as $cat ) {
		if ( isset( $cat['parent_id'], $cat['id'] ) && (int) $cat['parent_id'] === (int) $category_id ) {
			$child_categories[] = $cat;
		}
	}
}

if ( ! empty( $child_categories ) ) {
	$has_children = true;
}
if ( ! $has_children ) {
	// Fetch price range from Bagisto API for this category
	$price_range_url      = rtrim( $base, '/' ) . '/v1/catalog/price-range?' . http_build_query( array( 'category_id' => $category_id ) );
	$price_range_response = wp_remote_get( $price_range_url );
	$slider_min           = 1;
	$slider_max           = 3000;

	// DEBUG: Visible output (remove after testing)
	$debug_info = array(
		'url'         => $price_range_url,
		'is_error'    => is_wp_error( $price_range_response ),
		'status_code' => is_wp_error( $price_range_response ) ? 'ERROR' : wp_remote_retrieve_response_code( $price_range_response ),
	);

	if ( ! is_wp_error( $price_range_response ) && wp_remote_retrieve_response_code( $price_range_response ) === 200 ) {
		$price_range_body = wp_remote_retrieve_body( $price_range_response );
		$price_range_data = json_decode( $price_range_body, true );

		$debug_info['response_body'] = $price_range_body;
		$debug_info['parsed_data']   = $price_range_data;

		// Try direct min/max first
		if ( isset( $price_range_data['min'] ) && isset( $price_range_data['max'] ) ) {
			$slider_min          = (float) $price_range_data['min'];
			$slider_max          = (float) $price_range_data['max'];
			$debug_info['result'] = 'Found direct min/max';
		}
		// Try nested data structure
		elseif ( isset( $price_range_data['data']['min'] ) && isset( $price_range_data['data']['max'] ) ) {
			$slider_min          = (float) $price_range_data['data']['min'];
			$slider_max          = (float) $price_range_data['data']['max'];
			$debug_info['result'] = 'Found nested data.min/max';
		} else {
			$debug_info['result'] = 'No min/max found in response';
		}
	} else {
		if ( is_wp_error( $price_range_response ) ) {
			$debug_info['error_message'] = $price_range_response->get_error_message();
		}
	}

	// Temporary visible debug
	echo '<!-- PRICE RANGE DEBUG: ' . print_r( $debug_info, true ) . ' -->';
	$debug_info['slider_min'] = $slider_min;
	$debug_info['slider_max'] = $slider_max;

	// Read filters from query string
	$price_param = isset( $_GET['price'] ) ? wp_unslash( $_GET['price'] ) : '';
	$min_price   = '';
	$max_price   = '';
	if ( is_string( $price_param ) && $price_param !== '' ) {
		$parts = explode( ',', $price_param );
		if ( isset( $parts[0] ) && $parts[0] !== '' ) {
			$min_price = floatval( $parts[0] );
		}
		if ( isset( $parts[1] ) && $parts[1] !== '' ) {
			$max_price = floatval( $parts[1] );
		}
	}
	$brand = isset( $_GET['brand'] ) ? absint( $_GET['brand'] ) : 0;
	$color = isset( $_GET['color'] ) ? absint( $_GET['color'] ) : 0;
	$size  = isset( $_GET['size'] ) ? absint( $_GET['size'] ) : 0;

	$api_args = array(
		'page'        => $current_page,
		'limit'       => $per_page,
		'category_id' => $category_id,
	);

	// TEMP: do NOT send any additional filters to the API, only category_id + paging.
	// Bagisto expects price range as a single "price=min,max" parameter and we can
	// also send brand/color/size, but this is commented out for debugging.
	// if ( $price_param !== '' ) {
	// 	$api_args['price'] = $price_param;
	// }
	// if ( $brand ) {
	// 	$api_args['brand'] = $brand;
	// }
	// if ( $color ) {
	// 	$api_args['color'] = $color;
	// }
	// if ( $size ) {
	// 	$api_args['size'] = $size;
	// }

	// Use lightweight category endpoint that returns products for the given slug.
	// Request 260x220 WebP thumbnails directly from Bagisto cache.
	$category_endpoint = $base . 'v1/category/' . rawurlencode( (string) $category_slug );
	$api_url          = add_query_arg(
		array(
			'width'  => 260,
			'height' => 220,
			'format' => 'webp',
		),
		$category_endpoint
	);

	$response = wp_remote_get(
		$api_url,
		array( 'timeout' => 15 )
	);


    
	if ( is_wp_error( $response ) ) {
		?>
		<main class="site-main nailedit-products-page max-w-[1200px] mx-auto">
			<div class="nailedit-error">
				<?php echo esc_html( $response->get_error_message() ); ?>
			</div>
		</main>
		<?php
		get_footer();
		exit;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! is_array( $data ) ) {
		?>
		<main class="site-main nailedit-products-page max-w-[1200px] mx-auto">
			<div class="nailedit-error">
				<?php esc_html_e( 'Unexpected API response.', 'nailedit' ); ?>
			</div>
		</main>
		<?php
		get_footer();
		exit;
	}

	// Normalize products from API response.
	$products = array();
	if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
		// Legacy structure: { data: [ ... ], meta: { ... } }
		$products = $data['data'];
	} elseif ( is_array( $data ) && array_keys( $data ) === range( 0, count( $data ) - 1 ) ) {
		// New lightweight structure: [ { id, name, price, image, ... }, ... ]
		$products = $data;
	}

	// Pagination meta is only available on the legacy structure.
	$total_pages = 1;
	$total_items = 0;
	if ( isset( $data['meta'] ) && is_array( $data['meta'] ) ) {
		$total_pages = $data['meta']['last_page'] ?? 1;
		$total_items = $data['meta']['total'] ?? 0;
	}

	// Sidebar categories: fixed parent category ID 111
	$sidebar_categories = array();
	$sidebar_endpoint   = 'v1/descendant-categories?parent_id=111';
	$sidebar_response   = wp_remote_get(
		$base . $sidebar_endpoint,
		array( 'timeout' => 10 )
	);
	if ( ! is_wp_error( $sidebar_response ) ) {
		$sidebar_body = wp_remote_retrieve_body( $sidebar_response );
		$sidebar_data = json_decode( $sidebar_body, true );
		if ( is_array( $sidebar_data ) && isset( $sidebar_data['data'] ) && is_array( $sidebar_data['data'] ) ) {
			$sidebar_categories = $sidebar_data['data'];
		}
	}
}
?>




<?php echo get_template_part( 'template-parts/page-header' ); ?>
<main class="site-main nailedit-products-page max-w-[1200px] mx-auto">
    <?php if ( $has_children ) : ?>
        <section class="w-full">
            <h1 class="text-2xl font-bold text-primary mb-6"><?php echo esc_html( $category_name ); ?></h1>
            <div class="nailedit-products-grid sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ( $child_categories as $child ) : ?>
                    <?php
                    $child_name = isset( $child['name'] ) ? $child['name'] : '';
                    $child_slug = isset( $child['slug'] ) ? $child['slug'] : '';

                    if ( ! $child_slug ) {
                        continue;
                    }

                    $child_url = home_url( '/category/' . sanitize_title( $child_slug ) . '/' );
                    ?>
                    <article class="rounded-24 bg-white w-full relative mb-[40px] shadow-xl hover:shadow-2xl transition-shadow">
                        <a href="<?php echo esc_url( $child_url ); ?>" class="block h-full">
                            <?php 
                            $child_banner = isset( $child['banner_url'] ) ? $child['banner_url'] : '';
                            if ( $child_banner ) : 
                            ?>
                                <div class="rounded-t-24 overflow-hidden h-[200px]">
                                    <img class="w-full h-full object-cover" src="<?php echo esc_url( $child_banner ); ?>" alt="<?php echo esc_attr( $child_name ); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="p-[20px] text-center flex flex-col items-center justify-center gap-3">
                                <h2 class="font-bold text-[16px] text-primary line-clamp-2"><?php echo esc_html( $child_name ); ?></h2>
                                <span class="inline-flex items-center justify-center rounded-full bg-secondary text-primary font-semibold text-[13px] px-5 py-2 hover:bg-fourth transition"><?php esc_html_e( 'Vaata tooteid', 'nailedit' ); ?></span>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else : ?>
        <div class="flex flex-col md:flex-row md:items-start md:gap-6">
			<aside class="w-full md:w-1/4 mb-6 md:mb-0">
				<?php if ( ! empty( $sidebar_categories ) ) : ?>
					<nav class="bg-white/80 border border-slate-200 rounded-2xl p-4 text-[13px]">
						<h2 class="font-semibold text-primary mb-3"><?php esc_html_e( 'Kategooriad', 'nailedit' ); ?></h2>
						<ul class="space-y-1">
							<?php foreach ( $sidebar_categories as $side_cat ) : ?>
								<?php
									$side_name = isset( $side_cat['name'] ) ? $side_cat['name'] : '';
									$side_slug = isset( $side_cat['slug'] ) ? $side_cat['slug'] : '';
									if ( ! $side_slug ) {
										continue;
									}
									$side_url   = home_url( '/category/' . sanitize_title( $side_slug ) . '/' );
									$is_current = ( $side_slug === $category_slug );
								?>
								<li>
									<a href="<?php echo esc_url( $side_url ); ?>" class="block px-3 py-1 rounded-full <?php echo $is_current ? 'bg-secondary text-primary font-semibold' : 'text-slate-700 hover:bg-slate-100'; ?>">
										<?php echo esc_html( $side_name ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
			</aside>

			<section class="w-full md:flex-1">
				<?php if ( empty( $products ) ) : ?>
					<div class="nailedit-no-products">
						<p><?php esc_html_e( 'No products found in this category.', 'nailedit' ); ?></p>
                    </div>
                <?php else : ?>
                    <?php $nailedit_products_grid_classes = 'nailedit-products-grid sm:grid-cols-2 lg:grid-cols-3'; ?>
                    <div class="<?php echo esc_attr( $nailedit_products_grid_classes ); ?>">
                    <?php foreach ( $products as $product ) : ?>
                        <?php
                        $title       = $product['name'] ?? __( 'Unnamed product', 'nailedit' );
                        $price       = $product['min_price'] ?? ( $product['prices']['final']['formatted_price'] ?? ( $product['price'] ?? '' ) );
                        $product_id  = $product['id'] ?? 0;
                        $url_key     = $product['url_key'] ?? '';
                        $description = $product['description'] ?? '';
                        $description = wp_strip_all_tags( $description );
                        $description = wp_trim_words( $description, 20 );

                        $image = '';
                        if ( ! empty( $product['base_image'] ) && is_array( $product['base_image'] ) ) {
                            $image = $product['base_image']['medium_image_url'] ?? $product['base_image']['small_image_url'] ?? '';
                        }
                        if ( ! $image && ! empty( $product['images'] ) && is_array( $product['images'] ) ) {
                            $first = $product['images'][0];
                            if ( is_array( $first ) ) {
                                $image = $first['medium_image_url'] ?? $first['small_image_url'] ?? '';
                            }
                        }
                        // Fallback for new lightweight endpoint: direct 'image' field.
                        if ( ! $image && ! empty( $product['image'] ) && is_string( $product['image'] ) ) {
                            $image_path = $product['image'];
                            // If it's a relative path like /storage/..., build full URL from Bagisto base (without /api).
                            if ( strpos( $image_path, 'http://' ) !== 0 && strpos( $image_path, 'https://' ) !== 0 ) {
                                $bagisto_base = rtrim( $base, '/' );
                                // Strip trailing /api from base if present.
                                $bagisto_base = preg_replace( '#/api/?$#', '', $bagisto_base );
                                $image = $bagisto_base . '/' . ltrim( $image_path, '/' );
                            } else {
                                $image = $image_path;
                            }
                        }

                        // Build product URL from Bagisto url_key when available; fall back to ID.
						if ( ! empty( $url_key ) ) {
							$product_url = home_url( '/product/' . sanitize_title( $url_key ) . '/' );
						} elseif ( $product_id ) {
							$product_url = home_url( '/product/' . absint( $product_id ) . '/' );
						} else {
							$product_url = '#';
						}
                        ?>
                        <article class="rounded-24 bg-white w-full relative mb-[40px] shadow-xl hover:shadow-2xl transition-shadow">
                            <a href="<?php echo esc_url( $product_url ); ?>" class="nailedit-product-link block h-full">
                                <?php if ( $image ) : ?>
                                    <div class="nailedit-product-thumb rounded-24 overflow-hidden" style="border-bottom-left-radius: 0px !important;border-bottom-right-radius: 0px !important;">
                                        <img class="w-full h-full object-cover" src="<?php echo esc_url( nailedit_fix_image_url( $image ) ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="p-[15px] pb-[30px] text-center text-[14px] flex flex-wrap flex-col gap-[10px] justify-center">
                                    <h3 class="font-bold text-[14px] text-primary line-clamp-2"><?php echo esc_html( $title ); ?></h3>
                                    <?php if ( $price ) : ?>
                                        <p class="font-bold text-primary"><?php echo esc_html( nailedit_format_price($price)); ?></p>
                                    <?php endif; ?>
                                    <button type="button" class="mt-2 absolute bottom-[-20px] left-0 right-0 max-w-[130px] mx-auto inline-flex items-center justify-center rounded-full bg-secondary text-primary font-semibold text-[13px] px-5 py-2 hover:bg-fourth transition"><?php esc_html_e( 'Osta', 'nailedit' ); ?></button>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                    </div>

                    <?php if ( $total_pages > 1 ) : ?>
                        <nav class="nailedit-pagination">
                            <?php
                            $base_url = home_url( '/category/' . $category_slug . '/' );

                            // Preserve active filters in pagination links
                            $common_args = array();
                            if ( $price_param !== '' ) {
                                $common_args['price'] = $price_param;
                            }
                            if ( $brand ) {
                                $common_args['brand'] = $brand;
                            }
                            if ( $color ) {
                                $common_args['color'] = $color;
                            }
                            if ( $size ) {
                                $common_args['size'] = $size;
                            }

                            if ( $current_page > 1 ) {
                                $prev_args = array_merge( $common_args, array( 'paged' => $current_page - 1 ) );
                                $prev_url  = add_query_arg( $prev_args, $base_url );
                                echo '<a href="' . esc_url( $prev_url ) . '" class="nailedit-page-link nailedit-prev">&laquo; ' . esc_html__( 'Previous', 'nailedit' ) . '</a>';
                            }

                            echo '<div class="nailedit-page-numbers">';
                            for ( $i = 1; $i <= $total_pages; $i++ ) {
                                $page_args = array_merge( $common_args, array( 'paged' => $i ) );
                                $page_url  = add_query_arg( $page_args, $base_url );
                                $active_class = ( $i === $current_page ) ? ' active' : '';
                                echo '<a href="' . esc_url( $page_url ) . '" class="nailedit-page-link' . esc_attr( $active_class ) . '">' . (int) $i . '</a>';
                            }
                            echo '</div>';

                            if ( $current_page < $total_pages ) {
                                $next_args = array_merge( $common_args, array( 'paged' => $current_page + 1 ) );
                                $next_url  = add_query_arg( $next_args, $base_url );
                                echo '<a href="' . esc_url( $next_url ) . '" class="nailedit-page-link nailedit-next">' . esc_html__( 'Next', 'nailedit' ) . ' &raquo;</a>';
                            }
                            ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
			</section>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
