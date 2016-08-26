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
				'693-1'          => 'ES',
			),
			array(
				'country'        => 'Ethiopia',
				'language'       => 'Amharic',
				'3166-1-alpha-2' => 'ET',
				'3166-1-alpha-3' => 'ETH',
				'693-1'          => 'AM',
			),
			array(
				'country'        => 'Denmark',
				'language'       => 'Danish',
				'3166-1-alpha-2' => 'DK',
				'3166-1-alpha-3' => 'DNK',
				'693-1'          => 'DA',
			),
			array(
				'country'        => 'France',
				'language'       => 'French',
				'3166-1-alpha-2' => 'FR',
				'3166-1-alpha-3' => 'FRA',
				'693-1'          => 'FR',
			),
			array(
				'country'        => 'Germany',
				'3166-1-alpha-2' => 'DE',
				'3166-1-alpha-3' => 'DEU',
				'693-1'          => 'DE',
			),
			array(
				'country'        => 'Italy',
				'language'       => 'Italian',
				'3166-1-alpha-2' => 'IT',
				'3166-1-alpha-3' => 'ITA',
				'693-1'          => 'IT',
			),
			array(
				'country'        => 'Netherlands',
				'language'       => 'Dutch',
				'3166-1-alpha-2' => 'NL',
				'3166-1-alpha-3' => 'NLD',
				'639-1'          => 'NL',
			),
			array(
				'country'        => 'Spain',
				'language'       => 'Spanish',
				'3166-1-alpha-2' => 'ES',
				'3166-1-alpha-3' => 'ESP',
				'693-1'          => 'ES',
			),
			array(
				'country'        => 'Poland',
				'language'       => 'Polish',
				'3166-1-alpha-2' => 'PL',
				'3166-1-alpha-3' => 'POL',
				'693-1'          => 'PL',
			),
			array(
				'country'        => 'Portugal',
				'language'       => 'Polish',
				'3166-1-alpha-2' => 'PT',
				'3166-1-alpha-3' => 'PRT',
				'693-1'          => 'PT',
			),
			array(
				'country'        => 'Switzerland',
				'language'       => 'German',
				'3166-1-alpha-2' => 'CH',
				'3166-1-alpha-3' => 'CHE',
				'693-1'          => 'ES',
			),
			array(
				'country'        => 'United Kingdom',
				'language'       => 'English',
				'3166-1-alpha-2' => 'GB',
				'3166-1-alpha-3' => 'GBR',
				'693-1'          => 'EN',
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
	 * @param string $from ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 693-1)
	 * @param string $to   ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 693-1)
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
 * @param string $from ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 693-1)
 * @param string $to   ISO Standard (3166-1-alpha-2, 3166-1-alpha-3 or 693-1)
 * @param string $code ISO code
 * @param string $language Language which have to be choosen
 *
 * @return boolean|string False if nothing was found, or the found ISO string
 */
function i18n_iso_convert( $from, $to, $code, $language = null ){
	$iso = I18n_ISO_Convert::instance();
	return $iso->convert( $from, $to, $code, $language );
}