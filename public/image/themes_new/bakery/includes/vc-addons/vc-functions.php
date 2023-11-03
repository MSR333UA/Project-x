<?php 
	// VC functions
	if( !function_exists('vu_parse_multi_attribute') ){
		function vu_parse_multi_attribute( $value, $default = array() ) {
			$result = $default;
			$params_pairs = explode( '|', $value );
			if ( ! empty( $params_pairs ) ) {
				foreach ( $params_pairs as $pair ) {
					$param = preg_split( '/\:/', $pair );
					if ( ! empty( $param[0] ) && isset( $param[1] ) ) {
						$result[ $param[0] ] = rawurldecode( $param[1] );
					}
				}
			}

			return $result;
		}
	}

	if( !function_exists('vc_build_link') ){
		function vc_build_link( $value ) {
			return vu_parse_multi_attribute( $value, array( 'url' => '', 'title' => '', 'target' => '' ) );
		}
	}
?>