<?php
// extends WC_Data_Store_WP

class UnidPostTypeProduct {
	function __construct(){
		add_filter( 'woocommerce_product_pre_search_products', array($this, 'woocommerce_product_pre_search_products'), 10, 6 );
	}

	protected function get_valid_search_terms( $terms ) {
		$valid_terms = array();
		$stopwords   = $this->get_search_stopwords();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match, otherwise trim quotes and spaces.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( empty( $term ) || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			if ( in_array( wc_strtolower( $term ), $stopwords, true ) ) {
				continue;
			}

			$valid_terms[] = $term;
		}

		return $valid_terms;
	}
	protected function get_search_stopwords() {
		// Translators: This is a comma-separated list of very common words that should be excluded from a search, like a, an, and the. These are usually called "stopwords". You should not simply translate these individual words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		$stopwords = array_map(
			'wc_strtolower',
			array_map(
				'trim',
				explode(
					',',
					_x(
						'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
						'Comma-separated list of search stopwords in your language',
						'woocommerce'
					)
				)
			)
		);

		return apply_filters( 'wp_search_stopwords', $stopwords );
	}

	function woocommerce_product_pre_search_products( $false, $term, $type, $include_variations, $all_statuses, $limit ){
			global $wpdb;
			$post_types   = $include_variations ? array( 'product', 'product_variation' ) : array( 'product' );
			$type_where   = '';
			$status_where = '';
			$limit_query  = '';
			$term         = wc_strtolower( $term );

			$post_statuses = apply_filters(
				'woocommerce_search_products_post_statuses',
				current_user_can( 'edit_private_products' ) ? array( 'private', 'publish' ) : array( 'publish' )
			);

			// See if search term contains OR keywords.
			if ( strstr( $term, ' or ' ) ) {
				$term_groups = explode( ' or ', $term );
			} else {
				$term_groups = array( $term );
			}

			$search_where   = '';
			$search_queries = array();

			foreach ( $term_groups as $term_group ) {
				// Parse search terms.
				if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $term_group, $matches ) ) {
					$search_terms = $this->get_valid_search_terms( $matches[0] );
					$count        = count( $search_terms );

					// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
					if ( 9 < $count || 0 === $count ) {
						$search_terms = array( $term_group );
					}
				} else {
					$search_terms = array( $term_group );
				}

				$term_group_query = '';
				$searchand        = '';

				foreach ( $search_terms as $search_term ) {
					$like              = '%' . $wpdb->esc_like( $search_term ) . '%';
					$term_group_query .= $wpdb->prepare( " {$searchand} ( ( posts.post_title LIKE %s) OR ( posts.post_excerpt LIKE %s) OR ( posts.post_content LIKE %s ) OR ( wc_product_meta_lookup.sku LIKE %s ) OR (postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s) )", $like, $like, $like, $like, $like ); // @codingStandardsIgnoreLine.
					$searchand         = ' AND ';
				}

				if ( $term_group_query ) {
					$search_queries[] = $term_group_query;
				}
			}

			if ( ! empty( $search_queries ) ) {
				$search_where = ' AND (' . implode( ') OR (', $search_queries ) . ') ';
			}

			if ( ! empty( $include ) && is_array( $include ) ) {
				$search_where .= ' AND posts.ID IN(' . implode( ',', array_map( 'absint', $include ) ) . ') ';
			}

			if ( ! empty( $exclude ) && is_array( $exclude ) ) {
				$search_where .= ' AND posts.ID NOT IN(' . implode( ',', array_map( 'absint', $exclude ) ) . ') ';
			}

			if ( 'virtual' === $type ) {
				$type_where = ' AND ( wc_product_meta_lookup.virtual = 1 ) ';
			} elseif ( 'downloadable' === $type ) {
				$type_where = ' AND ( wc_product_meta_lookup.downloadable = 1 ) ';
			}

			if ( ! $all_statuses ) {
				$status_where = " AND posts.post_status IN ('" . implode( "','", $post_statuses ) . "') ";
			}

			if ( $limit ) {
				$limit_query = $wpdb->prepare( ' LIMIT %d ', $limit );
			}

			// phpcs:ignore WordPress.VIP.DirectDatabaseQuery.DirectQuery
			$search_results = $wpdb->get_results(
				// phpcs:disable
				"SELECT DISTINCT posts.ID as product_id, posts.post_parent as parent_id FROM {$wpdb->posts} posts
				 LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON posts.ID = wc_product_meta_lookup.product_id
				 LEFT JOIN {$wpdb->prefix}postmeta postmeta ON posts.ID = postmeta.post_id 
				WHERE posts.post_type IN ('" . implode( "','", $post_types ) . "')
				$search_where
				$status_where
				$type_where
				ORDER BY posts.post_parent ASC, posts.post_title ASC
				$limit_query
				"
				// phpcs:enable
			);

			$product_ids = wp_parse_id_list( array_merge( wp_list_pluck( $search_results, 'product_id' ), wp_list_pluck( $search_results, 'parent_id' ) ) );

			if ( is_numeric( $term ) ) {
				$post_id   = absint( $term );
				$post_type = get_post_type( $post_id );

				if ( 'product_variation' === $post_type && $include_variations ) {
					$product_ids[] = $post_id;
				} elseif ( 'product' === $post_type ) {
					$product_ids[] = $post_id;
				}

				$product_ids[] = wp_get_post_parent_id( $post_id );
			}

			return wp_parse_id_list( $product_ids );
	}

}
// SELECT DISTINCT posts.ID as product_id, posts.post_parent as parent_id FROM wp_posts posts 
// LEFT JOIN wp_wc_product_meta_lookup wc_product_meta_lookup ON posts.ID = wc_product_meta_lookup.product_id 
// LEFT JOIN wp_postmeta postmeta ON posts.ID = postmeta.post_id 
// WHERE posts.post_type IN ('product','product_variation')
// AND ( ( ( posts.post_title LIKE '{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}530z0/0s3/899{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}')
// OR ( posts.post_excerpt LIKE '{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}530z0/0s3/899{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}')
// OR ( postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE '530z0/0s3/899')
// OR ( posts.post_content LIKE '{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}530z0/0s3/899{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}' )
// OR ( wc_product_meta_lookup.sku LIKE '{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}530z0/0s3/899{051a8e65498762d91d06811a675a6e87ae8a2df93617cfc01ffbba38d64442c2}' ) )) ORDER BY posts.post_parent ASC, posts.post_title ASC

add_action( 'init', 'init_UnidPostTypeProduct' );
function init_UnidPostTypeProduct() {
	new UnidPostTypeProduct();
}

