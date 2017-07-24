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
				'3166-1-alpha-2' => 'AF',
				'3166-1-alpha-3' => 'AFG',
			),
			array(
				'country'        => 'Åland Islands',
				'3166-1-alpha-2' => 'AX',
				'3166-1-alpha-3' => 'ALA',
			),
			array(
				'country'        => 'Albania',
				'language'       => 'Albanian',
				'3166-1-alpha-2' => 'AL',
				'3166-1-alpha-3' => 'ALB',
				'639-1'          => 'SQ',
			),
			array(
				'country'        => 'Austria',
				'language'       => 'German',
				'3166-1-alpha-2' => 'AT',
				'3166-1-alpha-3' => 'AUT',
				'639-1'          => 'ES',
			),
			array(
				'country'        => 'Belgium',
				'language'       => 'Dutch',
				'3166-1-alpha-2' => 'BE',
				'3166-1-alpha-3' => 'BEL',
				'639-1'          => 'NL',
			),
			array(
				'country'        => 'Bulgaria',
				'language'       => 'Bulgarian',
				'3166-1-alpha-2' => 'BG',
				'3166-1-alpha-3' => 'BGR',
			),
			array(
				'country'        => 'Czech Republic',
				'language'       => 'Czech',
				'3166-1-alpha-2' => 'CZ',
				'3166-1-alpha-3' => 'CZE',
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
			),
			array(
				'country'        => 'Ethiopia',
				'language'       => 'Amharic',
				'3166-1-alpha-2' => 'ET',
				'3166-1-alpha-3' => 'ETH',
				'639-1'          => 'AM',
			),
			array(
				'country'        => 'France',
				'language'       => 'French',
				'3166-1-alpha-2' => 'FR',
				'3166-1-alpha-3' => 'FRA',
				'639-1'          => 'FR',
			),
			array(
				'country'        => 'Finland',
				'language'       => 'Finnish',
				'3166-1-alpha-2' => 'FI',
				'3166-1-alpha-3' => 'FIN',
			),
			array(
				'country'        => 'Germany',
				'3166-1-alpha-2' => 'DE',
				'3166-1-alpha-3' => 'DEU',
				'639-1'          => 'DE',
			),
			array(
				'country'        => 'Greece',
				'language'       => 'Greek',
				'3166-1-alpha-2' => 'GR',
				'3166-1-alpha-3' => 'GRC',
			),
			array(
				'country'        => 'Hungary',
				'language'       => 'Hungarian',
				'3166-1-alpha-2' => 'HU',
				'3166-1-alpha-3' => 'HUN',
			),
			array(
				'country'        => 'Italy',
				'language'       => 'Italian',
				'3166-1-alpha-2' => 'IT',
				'3166-1-alpha-3' => 'ITA',
				'639-1'          => 'IT',
			),
			array(
				'country'        => 'Ireland',
				'language'       => 'Irish',
				'3166-1-alpha-2' => 'IE',
				'3166-1-alpha-3' => 'IRL',
			),
			array(
				'country'        => 'Latvia',
				'language'       => 'Latvian',
				'3166-1-alpha-2' => 'LV',
				'3166-1-alpha-3' => 'LVA',
			),
			array(
				'country'        => 'Lithuania',
				'language'       => 'Lithuanian',
				'3166-1-alpha-2' => 'LT',
				'3166-1-alpha-3' => 'LTU',
			),
			array(
				'country'        => 'Moldova',
				'language'       => 'Romanian',
				'3166-1-alpha-2' => 'MD',
				'3166-1-alpha-3' => 'MDA',
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
			),
			array(
				'country'        => 'Republic of Belarus',
				'language'       => 'Belarusian',
				'3166-1-alpha-2' => 'BY',
				'3166-1-alpha-3' => 'BLR',
			),
			array(
				'country'        => 'Slovak Republic',
				'language'       => 'Slovak',
				'3166-1-alpha-2' => 'SK',
				'3166-1-alpha-3' => 'SVK',
			),
			array(
				'country'        => 'Spain',
				'language'       => 'Spanish',
				'3166-1-alpha-2' => 'ES',
				'3166-1-alpha-3' => 'ESP',
				'639-1'          => 'ES',
			),
			array(
				'country'        => 'Switzerland',
				'language'       => 'German',
				'3166-1-alpha-2' => 'CH',
				'3166-1-alpha-3' => 'CHE',
			),
			array(
				'country'        => 'Sweden',
				'language'       => 'Swedish',
				'3166-1-alpha-2' => 'SE',
				'3166-1-alpha-3' => 'SWE',
			),
			array(
				'country'        => 'Ukraine',
				'language'       => 'Ukranian',
				'3166-1-alpha-2' => 'UA',
				'3166-1-alpha-3' => 'UKR',
			),
			array(
				'country'        => 'United Kingdom',
				'language'       => 'English',
				'3166-1-alpha-2' => 'GB',
				'3166-1-alpha-3' => 'GBR',
				'639-1'          => 'EN',
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
