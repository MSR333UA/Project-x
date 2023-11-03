<?php

if (!function_exists('ihc_get_countries')):
	
	function ihc_get_countries(){
		/*
		 * @param none
		 * @return array
		 */
		 return array(
						'AF' => __( 'Afghanistan', 'ihc' ),
						'AX' => __( '&#197;land Islands', 'ihc' ),
						'AL' => __( 'Albania', 'ihc' ),
						'DZ' => __( 'Algeria', 'ihc' ),
						'AS' => __( 'American Samoa', 'ihc' ),
						'AD' => __( 'Andorra', 'ihc' ),
						'AO' => __( 'Angola', 'ihc' ),
						'AI' => __( 'Anguilla', 'ihc' ),
						'AQ' => __( 'Antarctica', 'ihc' ),
						'AG' => __( 'Antigua and Barbuda', 'ihc' ),
						'AR' => __( 'Argentina', 'ihc' ),
						'AM' => __( 'Armenia', 'ihc' ),
						'AW' => __( 'Aruba', 'ihc' ),
						'AU' => __( 'Australia', 'ihc' ),
						'AT' => __( 'Austria', 'ihc' ),
						'AZ' => __( 'Azerbaijan', 'ihc' ),
						'BS' => __( 'Bahamas', 'ihc' ),
						'BH' => __( 'Bahrain', 'ihc' ),
						'BD' => __( 'Bangladesh', 'ihc' ),
						'BB' => __( 'Barbados', 'ihc' ),
						'BY' => __( 'Belarus', 'ihc' ),
						'BE' => __( 'Belgium', 'ihc' ),
						'PW' => __( 'Belau', 'ihc' ),
						'BZ' => __( 'Belize', 'ihc' ),
						'BJ' => __( 'Benin', 'ihc' ),
						'BM' => __( 'Bermuda', 'ihc' ),
						'BT' => __( 'Bhutan', 'ihc' ),
						'BO' => __( 'Bolivia', 'ihc' ),
						'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'ihc' ),
						'BA' => __( 'Bosnia and Herzegovina', 'ihc' ),
						'BW' => __( 'Botswana', 'ihc' ),
						'BV' => __( 'Bouvet Island', 'ihc' ),
						'BR' => __( 'Brazil', 'ihc' ),
						'IO' => __( 'British Indian Ocean Territory', 'ihc' ),
						'VG' => __( 'British Virgin Islands', 'ihc' ),
						'BN' => __( 'Brunei', 'ihc' ),
						'BG' => __( 'Bulgaria', 'ihc' ),
						'BF' => __( 'Burkina Faso', 'ihc' ),
						'BI' => __( 'Burundi', 'ihc' ),
						'KH' => __( 'Cambodia', 'ihc' ),
						'CM' => __( 'Cameroon', 'ihc' ),
						'CA' => __( 'Canada', 'ihc' ),
						'CV' => __( 'Cape Verde', 'ihc' ),
						'KY' => __( 'Cayman Islands', 'ihc' ),
						'CF' => __( 'Central African Republic', 'ihc' ),
						'TD' => __( 'Chad', 'ihc' ),
						'CL' => __( 'Chile', 'ihc' ),
						'CN' => __( 'China', 'ihc' ),
						'CX' => __( 'Christmas Island', 'ihc' ),
						'CC' => __( 'Cocos (Keeling) Islands', 'ihc' ),
						'CO' => __( 'Colombia', 'ihc' ),
						'KM' => __( 'Comoros', 'ihc' ),
						'CG' => __( 'Congo (Brazzaville)', 'ihc' ),
						'CD' => __( 'Congo (Kinshasa)', 'ihc' ),
						'CK' => __( 'Cook Islands', 'ihc' ),
						'CR' => __( 'Costa Rica', 'ihc' ),
						'HR' => __( 'Croatia', 'ihc' ),
						'CU' => __( 'Cuba', 'ihc' ),
						'CW' => __( 'Cura&ccedil;ao', 'ihc' ),
						'CY' => __( 'Cyprus', 'ihc' ),
						'CZ' => __( 'Czech Republic', 'ihc' ),
						'DK' => __( 'Denmark', 'ihc' ),
						'DJ' => __( 'Djibouti', 'ihc' ),
						'DM' => __( 'Dominica', 'ihc' ),
						'DO' => __( 'Dominican Republic', 'ihc' ),
						'EC' => __( 'Ecuador', 'ihc' ),
						'EG' => __( 'Egypt', 'ihc' ),
						'SV' => __( 'El Salvador', 'ihc' ),
						'GQ' => __( 'Equatorial Guinea', 'ihc' ),
						'ER' => __( 'Eritrea', 'ihc' ),
						'EE' => __( 'Estonia', 'ihc' ),
						'ET' => __( 'Ethiopia', 'ihc' ),
						'FK' => __( 'Falkland Islands', 'ihc' ),
						'FO' => __( 'Faroe Islands', 'ihc' ),
						'FJ' => __( 'Fiji', 'ihc' ),
						'FI' => __( 'Finland', 'ihc' ),
						'FR' => __( 'France', 'ihc' ),
						'GF' => __( 'French Guiana', 'ihc' ),
						'PF' => __( 'French Polynesia', 'ihc' ),
						'TF' => __( 'French Southern Territories', 'ihc' ),
						'GA' => __( 'Gabon', 'ihc' ),
						'GM' => __( 'Gambia', 'ihc' ),
						'GE' => __( 'Georgia', 'ihc' ),
						'DE' => __( 'Germany', 'ihc' ),
						'GH' => __( 'Ghana', 'ihc' ),
						'GI' => __( 'Gibraltar', 'ihc' ),
						'GR' => __( 'Greece', 'ihc' ),
						'GL' => __( 'Greenland', 'ihc' ),
						'GD' => __( 'Grenada', 'ihc' ),
						'GP' => __( 'Guadeloupe', 'ihc' ),
						'GU' => __( 'Guam', 'ihc' ),
						'GT' => __( 'Guatemala', 'ihc' ),
						'GG' => __( 'Guernsey', 'ihc' ),
						'GN' => __( 'Guinea', 'ihc' ),
						'GW' => __( 'Guinea-Bissau', 'ihc' ),
						'GY' => __( 'Guyana', 'ihc' ),
						'HT' => __( 'Haiti', 'ihc' ),
						'HM' => __( 'Heard Island and McDonald Islands', 'ihc' ),
						'HN' => __( 'Honduras', 'ihc' ),
						'HK' => __( 'Hong Kong', 'ihc' ),
						'HU' => __( 'Hungary', 'ihc' ),
						'IS' => __( 'Iceland', 'ihc' ),
						'IN' => __( 'India', 'ihc' ),
						'ID' => __( 'Indonesia', 'ihc' ),
						'IR' => __( 'Iran', 'ihc' ),
						'IQ' => __( 'Iraq', 'ihc' ),
						'IE' => __( 'Republic of Ireland', 'ihc' ),
						'IM' => __( 'Isle of Man', 'ihc' ),
						'IL' => __( 'Israel', 'ihc' ),
						'IT' => __( 'Italy', 'ihc' ),
						'CI' => __( 'Ivory Coast', 'ihc' ),
						'JM' => __( 'Jamaica', 'ihc' ),
						'JP' => __( 'Japan', 'ihc' ),
						'JE' => __( 'Jersey', 'ihc' ),
						'JO' => __( 'Jordan', 'ihc' ),
						'KZ' => __( 'Kazakhstan', 'ihc' ),
						'KE' => __( 'Kenya', 'ihc' ),
						'KI' => __( 'Kiribati', 'ihc' ),
						'KW' => __( 'Kuwait', 'ihc' ),
						'KG' => __( 'Kyrgyzstan', 'ihc' ),
						'LA' => __( 'Laos', 'ihc' ),
						'LV' => __( 'Latvia', 'ihc' ),
						'LB' => __( 'Lebanon', 'ihc' ),
						'LS' => __( 'Lesotho', 'ihc' ),
						'LR' => __( 'Liberia', 'ihc' ),
						'LY' => __( 'Libya', 'ihc' ),
						'LI' => __( 'Liechtenstein', 'ihc' ),
						'LT' => __( 'Lithuania', 'ihc' ),
						'LU' => __( 'Luxembourg', 'ihc' ),
						'MO' => __( 'Macao S.A.R., China', 'ihc' ),
						'MK' => __( 'Macedonia', 'ihc' ),
						'MG' => __( 'Madagascar', 'ihc' ),
						'MW' => __( 'Malawi', 'ihc' ),
						'MY' => __( 'Malaysia', 'ihc' ),
						'MV' => __( 'Maldives', 'ihc' ),
						'ML' => __( 'Mali', 'ihc' ),
						'MT' => __( 'Malta', 'ihc' ),
						'MH' => __( 'Marshall Islands', 'ihc' ),
						'MQ' => __( 'Martinique', 'ihc' ),
						'MR' => __( 'Mauritania', 'ihc' ),
						'MU' => __( 'Mauritius', 'ihc' ),
						'YT' => __( 'Mayotte', 'ihc' ),
						'MX' => __( 'Mexico', 'ihc' ),
						'FM' => __( 'Micronesia', 'ihc' ),
						'MD' => __( 'Moldova', 'ihc' ),
						'MC' => __( 'Monaco', 'ihc' ),
						'MN' => __( 'Mongolia', 'ihc' ),
						'ME' => __( 'Montenegro', 'ihc' ),
						'MS' => __( 'Montserrat', 'ihc' ),
						'MA' => __( 'Morocco', 'ihc' ),
						'MZ' => __( 'Mozambique', 'ihc' ),
						'MM' => __( 'Myanmar', 'ihc' ),
						'NA' => __( 'Namibia', 'ihc' ),
						'NR' => __( 'Nauru', 'ihc' ),
						'NP' => __( 'Nepal', 'ihc' ),
						'NL' => __( 'Netherlands', 'ihc' ),
						'AN' => __( 'Netherlands Antilles', 'ihc' ),
						'NC' => __( 'New Caledonia', 'ihc' ),
						'NZ' => __( 'New Zealand', 'ihc' ),
						'NI' => __( 'Nicaragua', 'ihc' ),
						'NE' => __( 'Niger', 'ihc' ),
						'NG' => __( 'Nigeria', 'ihc' ),
						'NU' => __( 'Niue', 'ihc' ),
						'NF' => __( 'Norfolk Island', 'ihc' ),
						'MP' => __( 'Northern Mariana Islands', 'ihc' ),
						'KP' => __( 'North Korea', 'ihc' ),
						'NO' => __( 'Norway', 'ihc' ),
						'OM' => __( 'Oman', 'ihc' ),
						'PK' => __( 'Pakistan', 'ihc' ),
						'PS' => __( 'Palestinian Territory', 'ihc' ),
						'PA' => __( 'Panama', 'ihc' ),
						'PG' => __( 'Papua New Guinea', 'ihc' ),
						'PY' => __( 'Paraguay', 'ihc' ),
						'PE' => __( 'Peru', 'ihc' ),
						'PH' => __( 'Philippines', 'ihc' ),
						'PN' => __( 'Pitcairn', 'ihc' ),
						'PL' => __( 'Poland', 'ihc' ),
						'PT' => __( 'Portugal', 'ihc' ),
						'PR' => __( 'Puerto Rico', 'ihc' ),
						'QA' => __( 'Qatar', 'ihc' ),
						'RE' => __( 'Reunion', 'ihc' ),
						'RO' => __( 'Romania', 'ihc' ),
						'RU' => __( 'Russia', 'ihc' ),
						'RW' => __( 'Rwanda', 'ihc' ),
						'BL' => __( 'Saint Barth&eacute;lemy', 'ihc' ),
						'SH' => __( 'Saint Helena', 'ihc' ),
						'KN' => __( 'Saint Kitts and Nevis', 'ihc' ),
						'LC' => __( 'Saint Lucia', 'ihc' ),
						'MF' => __( 'Saint Martin (French part)', 'ihc' ),
						'SX' => __( 'Saint Martin (Dutch part)', 'ihc' ),
						'PM' => __( 'Saint Pierre and Miquelon', 'ihc' ),
						'VC' => __( 'Saint Vincent and the Grenadines', 'ihc' ),
						'SM' => __( 'San Marino', 'ihc' ),
						'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'ihc' ),
						'SA' => __( 'Saudi Arabia', 'ihc' ),
						'SN' => __( 'Senegal', 'ihc' ),
						'RS' => __( 'Serbia', 'ihc' ),
						'SC' => __( 'Seychelles', 'ihc' ),
						'SL' => __( 'Sierra Leone', 'ihc' ),
						'SG' => __( 'Singapore', 'ihc' ),
						'SK' => __( 'Slovakia', 'ihc' ),
						'SI' => __( 'Slovenia', 'ihc' ),
						'SB' => __( 'Solomon Islands', 'ihc' ),
						'SO' => __( 'Somalia', 'ihc' ),
						'ZA' => __( 'South Africa', 'ihc' ),
						'GS' => __( 'South Georgia/Sandwich Islands', 'ihc' ),
						'KR' => __( 'South Korea', 'ihc' ),
						'SS' => __( 'South Sudan', 'ihc' ),
						'ES' => __( 'Spain', 'ihc' ),
						'LK' => __( 'Sri Lanka', 'ihc' ),
						'SD' => __( 'Sudan', 'ihc' ),
						'SR' => __( 'Suriname', 'ihc' ),
						'SJ' => __( 'Svalbard and Jan Mayen', 'ihc' ),
						'SZ' => __( 'Swaziland', 'ihc' ),
						'SE' => __( 'Sweden', 'ihc' ),
						'CH' => __( 'Switzerland', 'ihc' ),
						'SY' => __( 'Syria', 'ihc' ),
						'TW' => __( 'Taiwan', 'ihc' ),
						'TJ' => __( 'Tajikistan', 'ihc' ),
						'TZ' => __( 'Tanzania', 'ihc' ),
						'TH' => __( 'Thailand', 'ihc' ),
						'TL' => __( 'Timor-Leste', 'ihc' ),
						'TG' => __( 'Togo', 'ihc' ),
						'TK' => __( 'Tokelau', 'ihc' ),
						'TO' => __( 'Tonga', 'ihc' ),
						'TT' => __( 'Trinidad and Tobago', 'ihc' ),
						'TN' => __( 'Tunisia', 'ihc' ),
						'TR' => __( 'Turkey', 'ihc' ),
						'TM' => __( 'Turkmenistan', 'ihc' ),
						'TC' => __( 'Turks and Caicos Islands', 'ihc' ),
						'TV' => __( 'Tuvalu', 'ihc' ),
						'UG' => __( 'Uganda', 'ihc' ),
						'UA' => __( 'Ukraine', 'ihc' ),
						'AE' => __( 'United Arab Emirates', 'ihc' ),
						'GB' => __( 'United Kingdom (UK)', 'ihc' ),
						'US' => __( 'United States (US)', 'ihc' ),
						'UM' => __( 'United States (US) Minor Outlying Islands', 'ihc' ),
						'VI' => __( 'United States (US) Virgin Islands', 'ihc' ),
						'UY' => __( 'Uruguay', 'ihc' ),
						'UZ' => __( 'Uzbekistan', 'ihc' ),
						'VU' => __( 'Vanuatu', 'ihc' ),
						'VA' => __( 'Vatican', 'ihc' ),
						'VE' => __( 'Venezuela', 'ihc' ),
						'VN' => __( 'Vietnam', 'ihc' ),
						'WF' => __( 'Wallis and Futuna', 'ihc' ),
						'EH' => __( 'Western Sahara', 'ihc' ),
						'WS' => __( 'Samoa', 'ihc' ),
						'YE' => __( 'Yemen', 'ihc' ),
						'ZM' => __( 'Zambia', 'ihc' ),
						'ZW' => __( 'Zimbabwe', 'ihc' )
		);
	}
	
endif;
