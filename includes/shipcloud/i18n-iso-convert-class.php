<?php

/**
 * Class I18n_ISO_Convert
 *
 * @author Sven Wagener - awesome.ug
 * @license GNU General Public License v3
 */
class I18n_ISO_Convert {

	/**
	 * Instance
	 *
	 * @var I18n_ISO_Convert
	 */
	protected static $_instance = null;

	/**
	 * Country list
	 *
	 * @var array
	 */
	var $countries = array();

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_countries();
	}

	/**
	 * Initializing the list of countries
	 */
	private function init_countries() {
		$this->countries = array(
			array(
				'country'        => 'Afghanistan',
				'language'       => 'Pashto',
				'3166-1-alpha-2' => 'AF',
				'3166-1-alpha-3' => 'AFG',
				'639-1'          => 'PS',
			),
			array(
				'country'        => 'Åland Islands',
				'language'       => 'Swedish',
				'3166-1-alpha-2' => 'AX',
				'3166-1-alpha-3' => 'ALA',
				'639-1'          => 'SV',
			),
			array(
				'country'        => 'Albania',
				'language'       => 'Albanian',
				'3166-1-alpha-2' => 'AL',
				'3166-1-alpha-3' => 'ALB',
				'639-1'          => 'SQ',
			),
			array(
			  'country'        => 'Andorra',
				'language'       => 'Catalan',
			  '3166-1-alpha-2' => 'AD',
			  '3166-1-alpha-3' => 'AND',
				'639-1'          => 'CA',
			),
			array(
				'country'        => 'Austria',
				'language'       => 'German',
				'3166-1-alpha-2' => 'AT',
				'3166-1-alpha-3' => 'AUT',
				'639-1'          => 'ES',
			),
			array(
			  'country'        => 'Belarus',
			  'language'       => 'Belarusian',
			  '3166-1-alpha-2' => 'BY',
			  '3166-1-alpha-3' => 'BLR',
			  '639-1'          => 'BE',
			),
			array(
				'country'        => 'Belgium',
				'language'       => 'Dutch',
				'3166-1-alpha-2' => 'BE',
				'3166-1-alpha-3' => 'BEL',
				'639-1'          => 'NL',
			),
			array(
			  'country'        => 'Bosnia and Herzegovina',
			  'language'       => 'Bosnian',
			  '3166-1-alpha-2' => 'BA',
			  '3166-1-alpha-3' => 'BAH',
			  '639-1'          => 'BS',
			),
			array(
			  'country'        => 'Bulgaria',
			  'language'       => 'Bulgarian',
			  '3166-1-alpha-2' => 'BG',
			  '3166-1-alpha-3' => 'BGR',
			  '639-1'          => 'BG',
			),
			array(
			  'country'        => 'Croatia',
			  'language'       => 'Croatian',
			  '3166-1-alpha-2' => 'HR',
			  '3166-1-alpha-3' => 'HRV',
			  '639-1'          => 'HR',
			),
			array(
			  'country'        => 'Cyprus',
				'language'       => 'Greek',
			  '3166-1-alpha-2' => 'CY',
			  '3166-1-alpha-3' => 'CYP',
				'639-1'          => 'EL',
			),
			array(
			  'country'        => 'Czech Republic',
			  'language'       => 'Czech',
			  '3166-1-alpha-2' => 'CZ',
			  '3166-1-alpha-3' => 'CZE',
			  '639-1'          => 'CS',
			),
			array(
				'country'        => 'Denmark',
				'language'       => 'Danish',
				'3166-1-alpha-2' => 'DK',
				'3166-1-alpha-3' => 'DNK',
				'639-1'          => 'DA',
			),
			array(
			  'country'        => 'Estonia',
			  'language'       => 'Estonian',
			  '3166-1-alpha-2' => 'EE',
			  '3166-1-alpha-3' => 'EST',
			  '639-1'          => 'ET',
			),
			array(
				'country'        => 'Ethiopia',
				'language'       => 'Amharic',
				'3166-1-alpha-2' => 'ET',
				'3166-1-alpha-3' => 'ETH',
				'639-1'          => 'AM',
			),
			array(
			  'country'        => 'Faroe Islands',
				'language'       => 'Faroese',
			  '3166-1-alpha-2' => 'FO',
			  '3166-1-alpha-3' => 'FRO',
				'639-1'          => 'FO',
			),
			array(
			  'country'        => 'Finland',
			  'language'       => 'Finnish',
			  '3166-1-alpha-2' => 'FI',
			  '3166-1-alpha-3' => 'FIN',
			  '639-1'          => 'FI',
			),
			array(
				'country'        => 'France',
				'language'       => 'French',
				'3166-1-alpha-2' => 'FR',
				'3166-1-alpha-3' => 'FRA',
				'639-1'          => 'FR',
			),
			array(
				'country'        => 'Germany',
				'language'       => 'German',
				'3166-1-alpha-2' => 'DE',
				'3166-1-alpha-3' => 'DEU',
				'639-1'          => 'DE',
			),
			array(
			  'country'        => 'Gibraltar',
				'language'       => 'English',
			  '3166-1-alpha-2' => 'GI',
			  '3166-1-alpha-3' => 'GIB',
				'639-1'          => 'EN',
			),
			array(
			  'country'        => 'Greece',
			  'language'       => 'Greek',
			  '3166-1-alpha-2' => 'GR',
			  '3166-1-alpha-3' => 'GRC',
			  '639-1'          => 'EL',
			),
			array(
			  'country'        => 'Hungary',
			  'language'       => 'Hungarian',
			  '3166-1-alpha-2' => 'HU',
			  '3166-1-alpha-3' => 'HUN',
			  '639-1'          => 'HU',
			),
			array(
			  'country'        => 'Iceland',
			  'language'       => 'Icelandic',
			  '3166-1-alpha-2' => 'IS',
			  '3166-1-alpha-3' => 'ISL',
			  '639-1'          => 'IS',
			),
			array(
			  'country'        => 'Ireland',
			  'language'       => 'Irish',
			  '3166-1-alpha-2' => 'IE',
			  '3166-1-alpha-3' => 'IRL',
			  '639-1'          => 'GA',
			),
			array(
			  'country'        => 'Isle of Man',
			  'language'       => 'English',
			  '3166-1-alpha-2' => 'IM',
			  '3166-1-alpha-3' => 'IMN',
			  '639-1'          => 'EN',
			),
			array(
				'country'        => 'Italy',
				'language'       => 'Italian',
				'3166-1-alpha-2' => 'IT',
				'3166-1-alpha-3' => 'ITA',
				'639-1'          => 'IT',
			),
			array(
			  'country'        => 'Kosovo',
			  '3166-1-alpha-2' => 'RS',
			  '3166-1-alpha-3' => 'XKX',
			),
			array(
			  'country'        => 'Latvia',
			  'language'       => 'Latvian',
			  '3166-1-alpha-2' => 'LV',
			  '3166-1-alpha-3' => 'LVA',
			  '639-1'          => 'LV',
			),
			array(
			  'country'        => 'Liechtenstein',
				'language'       => 'German',
			  '3166-1-alpha-2' => 'LI',
			  '3166-1-alpha-3' => 'LIE',
				'639-1'          => 'DE',
			),
			array(
			  'country'        => 'Lithuania',
			  'language'       => 'Lithuanian',
			  '3166-1-alpha-2' => 'LT',
			  '3166-1-alpha-3' => 'LTU',
			  '639-1'          => 'LT',
			),
			array(
			  'country'        => 'Luxembourg',
			  'language'       => 'Luxembourgish',
			  '3166-1-alpha-2' => 'LU',
			  '3166-1-alpha-3' => 'LUX',
			  '639-1'          => 'LB',
			),
			array(
			  'country'        => 'Macedonia',
			  'language'       => 'Macedonian',
			  '3166-1-alpha-2' => 'MK',
			  '3166-1-alpha-3' => 'MKD',
			  '639-1'          => 'MK',
			),
			array(
			  'country'        => 'Malta',
				'language'       => 'Maltese',
			  '3166-1-alpha-2' => 'MT',
			  '3166-1-alpha-3' => 'MLT',
				'639-1'          => 'MT',
			),
			array(
			  'country'        => 'Moldova',
			  'language'       => 'Romanian',
			  '3166-1-alpha-2' => 'MD',
			  '3166-1-alpha-3' => 'MDA',
			  '639-1'          => 'RO',
			),
			array(
			  'country'        => 'Monaco',
			  'language'       => 'French',
			  '3166-1-alpha-2' => 'MC',
			  '3166-1-alpha-3' => 'MCO',
			  '639-1'          => 'FR',
			),
			array(
			  'country'        => 'Montenegro',
				'language'       => 'Montenegrin',
			  '3166-1-alpha-2' => 'ME',
			  '3166-1-alpha-3' => 'MNE',
			),
			array(
				'country'        => 'Netherlands',
				'language'       => 'Dutch',
				'3166-1-alpha-2' => 'NL',
				'3166-1-alpha-3' => 'NLD',
				'639-1'          => 'NL',
			),
			array(
			  'country'        => 'Norway',
			  'language'       => 'Norwegian',
			  '3166-1-alpha-2' => 'NO',
			  '3166-1-alpha-3' => 'NOR',
			  '639-1'          => 'NO',
			),
			array(
				'country'        => 'Poland',
				'language'       => 'Polish',
				'3166-1-alpha-2' => 'PL',
				'3166-1-alpha-3' => 'POL',
				'639-1'          => 'PL',
			),
			array(
				'country'        => 'Portugal',
				'language'       => 'Polish',
				'3166-1-alpha-2' => 'PT',
				'3166-1-alpha-3' => 'PRT',
				'639-1'          => 'PT',
			),
			array(
			  'country'        => 'Romania',
			  'language'       => 'Romanian',
			  '3166-1-alpha-2' => 'RO',
			  '3166-1-alpha-3' => 'ROU',
			  '639-1'          => 'RO',
			),
			array(
			  'country'        => 'Russia',
			  'language'       => 'Russian',
			  '3166-1-alpha-2' => 'RU',
			  '3166-1-alpha-3' => 'RUS',
			  '639-1'          => 'RU',
			),
			array(
			  'country'        => 'San Marino',
				'language'       => 'Italian',
			  '3166-1-alpha-2' => 'SM',
			  '3166-1-alpha-3' => 'SMR',
				'639-1'          => 'IT',
			),
			array(
			  'country'        => 'Serbia',
			  'language'       => 'Serbian',
			  '3166-1-alpha-2' => 'RS',
			  '3166-1-alpha-3' => 'SRB',
			  '639-1'          => 'SR',
			),
			array(
			  'country'        => 'Slovakia',
			  'language'       => 'Slovak',
			  '3166-1-alpha-2' => 'SK',
			  '3166-1-alpha-3' => 'SVK',
			  '639-1'          => 'SK',
			),
			array(
			  'country'        => 'Slovenia',
			  'language'       => 'Slovenian',
			  '3166-1-alpha-2' => 'SI',
			  '3166-1-alpha-3' => 'SVN',
			  '639-1'          => 'SL',
			),
			array(
				'country'        => 'Spain',
				'language'       => 'Spanish',
				'3166-1-alpha-2' => 'ES',
				'3166-1-alpha-3' => 'ESP',
				'639-1'          => 'ES',
			),
			array(
			  'country'        => 'Sweden',
			  'language'       => 'Swedish',
			  '3166-1-alpha-2' => 'SE',
			  '3166-1-alpha-3' => 'SWE',
			  '639-1'          => 'SV',
			),
			array(
				'country'        => 'Switzerland',
				'language'       => 'German',
				'3166-1-alpha-2' => 'CH',
				'3166-1-alpha-3' => 'CHE',
				'639-1'          => 'DE',
			),
			array(
			  'country'        => 'Ukraine',
			  'language'       => 'Ukrainian',
			  '3166-1-alpha-2' => 'UA',
			  '3166-1-alpha-3' => 'UKR',
			  '639-1'          => 'UK',
			),
			array(
				'country'        => 'United Kingdom',
				'language'       => 'English',
				'3166-1-alpha-2' => 'GB',
				'3166-1-alpha-3' => 'GBR',
				'639-1'          => 'EN',
			),
			array(
			  'country'        => 'Vatican city',
			  'language'       => 'Italian',
			  '3166-1-alpha-2' => 'VA',
			  '3166-1-alpha-3' => 'VAT',
			  '639-1'          => 'IT',
			)
		);
	}

	/**
	 * Get instance
	 * Falls die einzige Instanz noch nicht existiert, erstelle sie
	 * Gebe die einzige Instanz dann zurück
	 *
	 * @return I18n_ISO_Convert
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Converting country/language code
	 *
	 * @param string $from ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 639-1)
	 * @param string $to   ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 639-1)
	 * @param string $code ISO code
	 * @param string $language Language which have to be choosen
	 *
	 * @return boolean|string False if nothing was found, or the found ISO string
	 */
	public function convert( $from, $to, $code, $language = null ) {

		foreach ( $this->countries AS $country ) {
			if ( ! array_key_exists( $from, $country ) ) {
				continue;
			}

			if ( $country[ $from ] !== $code ) {
				continue;
			}

			if ( ! array_key_exists( $to, $country ) ) {
				continue;
			}

			if ( ! empty( $language ) ) {
				if ( ! array_key_exists( 'language', $country ) ) {
					continue;
				}

				if ( $country[ 'language' ] != $language ) {
					continue;
				}
			}

			return $country[ $to ];
		}

		return false;
	}
}
/**
 * Converting country/language code
 *
 * @param string $from ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 639-1)
 * @param string $to   ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 639-1)
 * @param string $code ISO code
 * @param string $language Language which have to be choosen
 *
 * @return boolean|string False if nothing was found, or the found ISO string
 */
function i18n_iso_convert( $from, $to, $code, $language = null ){
	$iso = I18n_ISO_Convert::instance();
	return $iso->convert( $from, $to, $code, $language );
}
