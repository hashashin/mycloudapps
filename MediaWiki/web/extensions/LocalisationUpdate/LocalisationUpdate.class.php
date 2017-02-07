<?php

/**
 * Class for localization update hooks and static methods.
 */
class LocalisationUpdate {
	/**
	 * Hook: LocalisationCacheRecache
	 */
	public static function onRecache( LocalisationCache $lc, $code, array &$cache ) {
		$dir = LocalisationUpdate::getDirectory();
		if ( !$dir ) {
			return true;
		}

		$codeSequence = array_merge( array( $code ), $cache['fallbackSequence'] );
		foreach ( $codeSequence as $csCode ) {
			$fileName = "$dir/" . self::getFilename( $csCode );
			if ( is_readable( $fileName ) ) {
				$data = FormatJson::decode( file_get_contents( $fileName ), true );
				$cache['messages'] = array_merge( $cache['messages'], $data );
			}

			$cache['deps'][] = new FileDependency( $fileName );
		}

		return true;
	}

	/**
	 * Returns a directory where updated translations are stored.
	 *
	 * @return string|false False if not configured.
	 * @since 1.1
	 */
	public static function getDirectory() {
		global $wgLocalisationUpdateDirectory, $wgCacheDirectory;

		// ?: can be used once we drop support for MW 1.19
		return $wgLocalisationUpdateDirectory ?
			$wgLocalisationUpdateDirectory :
			$wgCacheDirectory;
	}

	/**
	 * Returns a filename where updated translations are stored.
	 *
	 * @param string $language Language tag
	 * @return string
	 * @since 1.1
	 */
	public static function getFilename( $language ) {
		return "l10nupdate-$language.json";
	}
}
