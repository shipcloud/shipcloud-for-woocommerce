<?php
    namespace Shipcloud\I18n;

    use Punic\Territory;
    require_once WCSC_FOLDER . '/vendor/punic/punic.php';

    /**
     * Class Cldr_Converter
     *
     * @since 1.9.0
     */
    class Cldr_Converter {
        /**
         * Determine language by a country code
         * We're trying to get the official language first. If not present we're trying to get the
         * de facto official language from CLDR
         *
         * @since 1.9.0
         * @param string $code the code for which we're trying to determine the spoken language
         */
        public function language_from_country_code($code) {
            $language = Territory::getLanguages($code, 'o', true);

            if (sizeof($language) == 0) {
                $language = Territory::getLanguages($code, 'f', true);
            }

            if (sizeof($language) > 0) {
                return $language[0];
            }
        }
    }
?>
