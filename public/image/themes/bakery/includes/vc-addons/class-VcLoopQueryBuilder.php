<?php 

	/**
	 * Parses loop settings and creates WP_Query according to manual
	 *
	 * @link http://codex.wordpress.org/Class_Reference/WP_Query
	 */

	if( !class_exists('VcLoopQueryBuilder') ){
		class VcLoopQueryBuilder {
			protected $args = array();

			function __construct( $data_str ) {
				$data = $this->parseData($data_str);

				foreach ( $data as $key => $value ) {
					$method = 'parse_' . $key;
					if ( method_exists( $this, $method ) ) {
						$this->$method( $value );
					}
				}
			}

			// Pages count
			protected function parse_size( $value ) {
				$this->args['posts_per_page'] = $value === 'All' ? -1 : (int)$value;
			}

			// Sorting field
			protected function parse_order_by( $value ) {
				$this->args['orderby'] = $value;
			}

			// Sorting order
			protected function parse_order( $value ) {
				$this->args['order'] = $value;
			}

			// By post types
			protected function parse_post_type( $value ) {
				$this->args['post_type'] = $this->stringToArray( $value );
			}

			// By author
			protected function parse_authors( $value ) {
				$this->args['author'] = $value;
			}

			// By categories
			protected function parse_categories( $value ) {
				$this->args['cat'] = $value;
			}

			// By taxonomies
			protected function parse_tax_query( $value ) {
				$terms = $this->stringToArray( $value );
				if ( empty( $this->args['tax_query'] ) ) {
					$this->args['tax_query'] = array( 'relation' => 'OR' );
				}
				$negative_term_list = array();
				foreach ( $terms as $term ) {
					if ( (int)$term < 0 ) $negative_term_list[] = abs( $term );
				}
				$terms = get_terms( $this->getTaxonomies(), array( 'include' => array_map( 'abs', $terms ) ) );
				foreach ( $terms as $t ) {
					$operator = in_array( (int)$t->term_id, $negative_term_list ) ? 'NOT IN' : 'IN';
					$this->args['tax_query'][] = array(
						'field' => 'id',
						'taxonomy' => $t->taxonomy,
						'terms' => $t->term_id,
						'operator' => $operator
					);
				}
			}

			// By tags ids
			protected function parse_tags( $value ) {
				$in = $not_in = array();
				$tags_ids = $this->stringToArray( $value );
				foreach ( $tags_ids as $tag ) {
					$tag = (int)$tag;
					if ( $tag < 0 ) {
						$not_in[] = abs( $tag );
					} else {
						$in[] = $tag;
					}
				}
				$this->args['tag__in'] = $in;
				$this->args['tag__not_in'] = $not_in;
			}

			// By posts ids
			protected function parse_by_id( $value ) {
				$in = $not_in = array();
				$ids = $this->stringToArray( $value );
				foreach ( $ids as $id ) {
					$id = (int)$id;
					if ( $id < 0 ) {
						$not_in[] = abs( $id );
					} else {
						$in[] = $id;
					}
				}
				$this->args['post__in'] = $in;
				$this->args['post__not_in'] = $not_in;
			}

			public function excludeId( $id ) {
				if ( ! isset( $this->args['post__not_in'] ) ) $this->args['post__not_in'] = array();
				$this->args['post__not_in'][] = $id;
			}

			/**
			 * get list of taxonomies which has no tags and categories items.
			 *
			 * @static
			 * @return array
			 */
			public static function getTaxonomies() {
				$taxonomy_exclude = (array)apply_filters( 'get_categories_taxonomy', 'category' );
				$taxonomy_exclude[] = 'post_tag';
				$taxonomies = array();
				foreach ( get_taxonomies() as $taxonomy ) {
					if ( ! in_array( $taxonomy, $taxonomy_exclude ) ) $taxonomies[] = $taxonomy;
				}
				return $taxonomies;
			}

			/**
			 * Converts string to array. Filters empty arrays values
			 *
			 * @param $value
			 * @return array
			 */
			protected function stringToArray( $value ) {
				$valid_values = array();
				$list = preg_split( '/\,[\s]*/', $value );
				foreach ( $list as $v ) {
					if ( strlen( $v ) > 0 ) $valid_values[] = $v;
				}
				return $valid_values;
			}

			public static function parseData( $value ) {
				$data = array();
				$values_pairs = preg_split( '/\|/', $value );
				foreach ( $values_pairs as $pair ) {
					if ( ! empty( $pair ) ) {
						list( $key, $value ) = preg_split( '/\:/', $pair );
						$data[$key] = $value;
					}
				}
				return $data;
			}

			public function build() {
				//return array( $this->args, new WP_Query( $this->args ) );
				return new WP_Query( $this->args );
			}
		}
	}
?>