<?php
/**
 * SpamBlacklist extension API
 *
 * Copyright © 2013 Wikimedia Foundation
 * Based on code by Ian Baker, Victor Vasiliev, Bryan Tong Minh, Roan Kattouw,
 * Alex Z., and Jackmcbarn
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * https://www.gnu.org/copyleft/gpl.html
 */

/**
 * Query module check a URL against the blacklist
 *
 * @ingroup API
 * @ingroup Extensions
 */
class ApiSpamBlacklist extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();
		$matches = BaseBlacklist::getInstance( 'spam' )->filter( $params['url'], NULL, true );
		$res = $this->getResult();

		if ( $matches !== false ) {
			// this url is blacklisted.
			$res->addValue( 'spamblacklist', 'result', 'blacklisted' );
			$res->setIndexedTagName( $matches, 'match' );
			$res->addValue( 'spamblacklist', 'matches', $matches );
		} else {
			// not blacklisted
			$res->addValue( 'spamblacklist', 'result', 'ok' );
		}
	}

	public function getAllowedParams() {
		return array(
			'url' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_ISMULTI => true,
			)
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'url' => 'A pipe-separated list of URLs to validate against the blacklist',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Validate one or more URLs against the SpamBlacklist.';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=spamblacklist&url=http%3A%2F%2Fwww.example.com%2F%7Chttp%3A%2F%2Fwww.example.org%2F',
			'api.php?action=spamblacklist&url=https%3A%2F%2Fwww.example.net%2Findex.php',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=spamblacklist&url=https://www.example.com/|https://www.example.org/'
				=> 'apihelp-spamblacklist-example-1',
		);
	}

	public function getHelpUrls() {
		return array( 'https://www.mediawiki.org/wiki/Extension:SpamBlacklist/API' );
	}
}
