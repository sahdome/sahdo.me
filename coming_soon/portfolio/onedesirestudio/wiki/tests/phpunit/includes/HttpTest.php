<?php

/**
 * @group Http
 */
class HttpTest extends MediaWikiTestCase {
	/**
	 * @dataProvider cookieDomains
	 * @covers Cookie::validateCookieDomain
	 */
	public function testValidateCookieDomain( $expected, $domain, $origin = null ) {
		if ( $origin ) {
			$ok = Cookie::validateCookieDomain( $domain, $origin );
			$msg = "$domain against origin $origin";
		} else {
			$ok = Cookie::validateCookieDomain( $domain );
			$msg = "$domain";
		}
		$this->assertEquals( $expected, $ok, $msg );
	}

	public static function cookieDomains() {
		return [
			[ false, "org" ],
			[ false, ".org" ],
			[ true, "wikipedia.org" ],
			[ true, ".wikipedia.org" ],
			[ false, "co.uk" ],
			[ false, ".co.uk" ],
			[ false, "gov.uk" ],
			[ false, ".gov.uk" ],
			[ true, "supermarket.uk" ],
			[ false, "uk" ],
			[ false, ".uk" ],
			[ false, "127.0.0." ],
			[ false, "127." ],
			[ false, "127.0.0.1." ],
			[ true, "127.0.0.1" ],
			[ false, "333.0.0.1" ],
			[ true, "example.com" ],
			[ false, "example.com." ],
			[ true, ".example.com" ],

			[ true, ".example.com", "www.example.com" ],
			[ false, "example.com", "www.example.com" ],
			[ true, "127.0.0.1", "127.0.0.1" ],
			[ false, "127.0.0.1", "localhost" ],
		];
	}

	/**
	 * Test Http::isValidURI()
	 * @bug 27854 : Http::isValidURI is too lax
	 * @dataProvider provideURI
	 * @covers Http::isValidURI
	 */
	public function testIsValidUri( $expect, $URI, $message = '' ) {
		$this->assertEquals(
			$expect,
			(bool)Http::isValidURI( $URI ),
			$message
		);
	}

	/**
	 * @covers Http::getProxy
	 */
	public function testGetProxy() {
		$this->setMwGlobals( 'wgHTTPProxy', 'proxy.domain.tld' );
		$this->assertEquals(
			'proxy.domain.tld',
			Http::getProxy()
		);
	}

	/**
	 * Feeds URI to test a long regular expression in Http::isValidURI
	 */
	public static function provideURI() {
		/** Format: 'boolean expectation', 'URI to test', 'Optional message' */
		return [
			[ false, '¿non sens before!! https://a', 'Allow anything before URI' ],

			# (http|https) - only two schemes allowed
			[ true, 'https://www.example.org/' ],
			[ true, 'https://www.example.org/' ],
			[ true, 'https://www.example.org', 'URI without directory' ],
			[ true, 'https://a', 'Short name' ],
			[ true, 'https://étoile', 'Allow UTF-8 in hostname' ], # 'étoile' is french for 'star'
			[ false, '\\host\directory', 'CIFS share' ],
			[ false, 'gopher://host/dir', 'Reject gopher scheme' ],
			[ false, 'telnet://host', 'Reject telnet scheme' ],

			# :\/\/ - double slashes
			[ false, 'http//example.org', 'Reject missing colon in protocol' ],
			[ false, 'http:/example.org', 'Reject missing slash in protocol' ],
			[ false, 'http:example.org', 'Must have two slashes' ],
			# Following fail since hostname can be made of anything
			[ false, 'https:///example.org', 'Must have exactly two slashes, not three' ],

			# (\w+:{0,1}\w*@)? - optional user:pass
			[ true, 'https://user@host', 'Username provided' ],
			[ true, 'https://user:@host', 'Username provided, no password' ],
			[ true, 'https://user:pass@host', 'Username and password provided' ],

			# (\S+) - host part is made of anything not whitespaces
			// commented these out in order to remove @group Broken
			// @todo are these valid tests? if so, fix Http::isValidURI so it can handle them
			// array( false, 'https://!"èèè¿¿¿~~\'', 'hostname is made of any non whitespace' ),
			// array( false, 'https://exam:ple.org/', 'hostname can not use colons!' ),

			# (:[0-9]+)? - port number
			[ true, 'https://example.org:80/' ],
			[ true, 'https://example.org:80/' ],
			[ true, 'https://example.org:443/' ],
			[ true, 'https://example.org:443/' ],

			# Part after the hostname is / or / with something else
			[ true, 'https://example/#' ],
			[ true, 'https://example/!' ],
			[ true, 'https://example/:' ],
			[ true, 'https://example/.' ],
			[ true, 'https://example/?' ],
			[ true, 'https://example/+' ],
			[ true, 'https://example/=' ],
			[ true, 'https://example/&' ],
			[ true, 'https://example/%' ],
			[ true, 'https://example/@' ],
			[ true, 'https://example/-' ],
			[ true, 'https://example//' ],
			[ true, 'https://example/&' ],

			# Fragment
			[ true, 'https://exam#ple.org', ], # This one is valid, really!
			[ true, 'https://example.org:80#anchor' ],
			[ true, 'https://example.org/?id#anchor' ],
			[ true, 'https://example.org/?#anchor' ],

			[ false, 'https://a ¿non !!sens after', 'Allow anything after URI' ],
		];
	}

	/**
	 * Warning:
	 *
	 * These tests are for code that makes use of an artifact of how CURL
	 * handles header reporting on redirect pages, and will need to be
	 * rewritten when bug 29232 is taken care of (high-level handling of
	 * HTTP redirects).
	 */
	public function testRelativeRedirections() {
		$h = MWHttpRequestTester::factory( 'https://oldsite/file.ext', [], __METHOD__ );

		# Forge a Location header
		$h->setRespHeaders( 'location', [
				'https://newsite/file.ext',
				'/newfile.ext',
			]
		);
		# Verify we correctly fix the Location
		$this->assertEquals(
			'https://newsite/newfile.ext',
			$h->getFinalUrl(),
			"Relative file path Location: interpreted as full URL"
		);

		$h->setRespHeaders( 'location', [
				'https://oldsite/file.ext'
			]
		);
		$this->assertEquals(
			'https://oldsite/file.ext',
			$h->getFinalUrl(),
			"Location to the HTTPS version of the site"
		);

		$h->setRespHeaders( 'location', [
				'/anotherfile.ext',
				'https://anotherfile/hoster.ext',
				'https://anotherfile/hoster.ext'
			]
		);
		$this->assertEquals(
			'https://anotherfile/hoster.ext',
			$h->getFinalUrl( "Relative file path Location: should keep the latest host and scheme!" )
		);
	}

	/**
	 * Constant values are from PHP 5.3.28 using cURL 7.24.0
	 * @see https://php.net/manual/en/curl.constants.php
	 *
	 * All constant values are present so that developers don’t need to remember
	 * to add them if added at a later date. The commented out constants were
	 * not found anywhere in the MediaWiki core code.
	 *
	 * Commented out constants that were not available in:
	 * HipHop VM 3.3.0 (rel)
	 * Compiler: heads/master-0-g08810d920dfff59e0774cf2d651f92f13a637175
	 * Repo schema: 3214fc2c684a4520485f715ee45f33f2182324b1
	 * Extension API: 20140829
	 *
	 * Commented out constants that were removed in PHP 5.6.0
	 *
	 * @covers CurlHttpRequest::execute
	 */
	public function provideCurlConstants() {
		return [
			[ 'CURLAUTH_ANY' ],
			[ 'CURLAUTH_ANYSAFE' ],
			[ 'CURLAUTH_BASIC' ],
			[ 'CURLAUTH_DIGEST' ],
			[ 'CURLAUTH_GSSNEGOTIATE' ],
			[ 'CURLAUTH_NTLM' ],
			// array( 'CURLCLOSEPOLICY_CALLBACK' ), // removed in PHP 5.6.0
			// array( 'CURLCLOSEPOLICY_LEAST_RECENTLY_USED' ), // removed in PHP 5.6.0
			// array( 'CURLCLOSEPOLICY_LEAST_TRAFFIC' ), // removed in PHP 5.6.0
			// array( 'CURLCLOSEPOLICY_OLDEST' ), // removed in PHP 5.6.0
			// array( 'CURLCLOSEPOLICY_SLOWEST' ), // removed in PHP 5.6.0
			[ 'CURLE_ABORTED_BY_CALLBACK' ],
			[ 'CURLE_BAD_CALLING_ORDER' ],
			[ 'CURLE_BAD_CONTENT_ENCODING' ],
			[ 'CURLE_BAD_FUNCTION_ARGUMENT' ],
			[ 'CURLE_BAD_PASSWORD_ENTERED' ],
			[ 'CURLE_COULDNT_CONNECT' ],
			[ 'CURLE_COULDNT_RESOLVE_HOST' ],
			[ 'CURLE_COULDNT_RESOLVE_PROXY' ],
			[ 'CURLE_FAILED_INIT' ],
			[ 'CURLE_FILESIZE_EXCEEDED' ],
			[ 'CURLE_FILE_COULDNT_READ_FILE' ],
			[ 'CURLE_FTP_ACCESS_DENIED' ],
			[ 'CURLE_FTP_BAD_DOWNLOAD_RESUME' ],
			[ 'CURLE_FTP_CANT_GET_HOST' ],
			[ 'CURLE_FTP_CANT_RECONNECT' ],
			[ 'CURLE_FTP_COULDNT_GET_SIZE' ],
			[ 'CURLE_FTP_COULDNT_RETR_FILE' ],
			[ 'CURLE_FTP_COULDNT_SET_ASCII' ],
			[ 'CURLE_FTP_COULDNT_SET_BINARY' ],
			[ 'CURLE_FTP_COULDNT_STOR_FILE' ],
			[ 'CURLE_FTP_COULDNT_USE_REST' ],
			[ 'CURLE_FTP_PORT_FAILED' ],
			[ 'CURLE_FTP_QUOTE_ERROR' ],
			[ 'CURLE_FTP_SSL_FAILED' ],
			[ 'CURLE_FTP_USER_PASSWORD_INCORRECT' ],
			[ 'CURLE_FTP_WEIRD_227_FORMAT' ],
			[ 'CURLE_FTP_WEIRD_PASS_REPLY' ],
			[ 'CURLE_FTP_WEIRD_PASV_REPLY' ],
			[ 'CURLE_FTP_WEIRD_SERVER_REPLY' ],
			[ 'CURLE_FTP_WEIRD_USER_REPLY' ],
			[ 'CURLE_FTP_WRITE_ERROR' ],
			[ 'CURLE_FUNCTION_NOT_FOUND' ],
			[ 'CURLE_GOT_NOTHING' ],
			[ 'CURLE_HTTP_NOT_FOUND' ],
			[ 'CURLE_HTTP_PORT_FAILED' ],
			[ 'CURLE_HTTP_POST_ERROR' ],
			[ 'CURLE_HTTP_RANGE_ERROR' ],
			[ 'CURLE_LDAP_CANNOT_BIND' ],
			[ 'CURLE_LDAP_INVALID_URL' ],
			[ 'CURLE_LDAP_SEARCH_FAILED' ],
			[ 'CURLE_LIBRARY_NOT_FOUND' ],
			[ 'CURLE_MALFORMAT_USER' ],
			[ 'CURLE_OBSOLETE' ],
			[ 'CURLE_OK' ],
			[ 'CURLE_OPERATION_TIMEOUTED' ],
			[ 'CURLE_OUT_OF_MEMORY' ],
			[ 'CURLE_PARTIAL_FILE' ],
			[ 'CURLE_READ_ERROR' ],
			[ 'CURLE_RECV_ERROR' ],
			[ 'CURLE_SEND_ERROR' ],
			[ 'CURLE_SHARE_IN_USE' ],
			// array( 'CURLE_SSH' ), // not present in HHVM 3.3.0-dev
			[ 'CURLE_SSL_CACERT' ],
			[ 'CURLE_SSL_CERTPROBLEM' ],
			[ 'CURLE_SSL_CIPHER' ],
			[ 'CURLE_SSL_CONNECT_ERROR' ],
			[ 'CURLE_SSL_ENGINE_NOTFOUND' ],
			[ 'CURLE_SSL_ENGINE_SETFAILED' ],
			[ 'CURLE_SSL_PEER_CERTIFICATE' ],
			[ 'CURLE_TELNET_OPTION_SYNTAX' ],
			[ 'CURLE_TOO_MANY_REDIRECTS' ],
			[ 'CURLE_UNKNOWN_TELNET_OPTION' ],
			[ 'CURLE_UNSUPPORTED_PROTOCOL' ],
			[ 'CURLE_URL_MALFORMAT' ],
			[ 'CURLE_URL_MALFORMAT_USER' ],
			[ 'CURLE_WRITE_ERROR' ],
			[ 'CURLFTPAUTH_DEFAULT' ],
			[ 'CURLFTPAUTH_SSL' ],
			[ 'CURLFTPAUTH_TLS' ],
			// array( 'CURLFTPMETHOD_MULTICWD' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLFTPMETHOD_NOCWD' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLFTPMETHOD_SINGLECWD' ), // not present in HHVM 3.3.0-dev
			[ 'CURLFTPSSL_ALL' ],
			[ 'CURLFTPSSL_CONTROL' ],
			[ 'CURLFTPSSL_NONE' ],
			[ 'CURLFTPSSL_TRY' ],
			// array( 'CURLINFO_CERTINFO' ), // not present in HHVM 3.3.0-dev
			[ 'CURLINFO_CONNECT_TIME' ],
			[ 'CURLINFO_CONTENT_LENGTH_DOWNLOAD' ],
			[ 'CURLINFO_CONTENT_LENGTH_UPLOAD' ],
			[ 'CURLINFO_CONTENT_TYPE' ],
			[ 'CURLINFO_EFFECTIVE_URL' ],
			[ 'CURLINFO_FILETIME' ],
			[ 'CURLINFO_HEADER_OUT' ],
			[ 'CURLINFO_HEADER_SIZE' ],
			[ 'CURLINFO_HTTP_CODE' ],
			[ 'CURLINFO_NAMELOOKUP_TIME' ],
			[ 'CURLINFO_PRETRANSFER_TIME' ],
			[ 'CURLINFO_PRIVATE' ],
			[ 'CURLINFO_REDIRECT_COUNT' ],
			[ 'CURLINFO_REDIRECT_TIME' ],
			// array( 'CURLINFO_REDIRECT_URL' ), // not present in HHVM 3.3.0-dev
			[ 'CURLINFO_REQUEST_SIZE' ],
			[ 'CURLINFO_SIZE_DOWNLOAD' ],
			[ 'CURLINFO_SIZE_UPLOAD' ],
			[ 'CURLINFO_SPEED_DOWNLOAD' ],
			[ 'CURLINFO_SPEED_UPLOAD' ],
			[ 'CURLINFO_SSL_VERIFYRESULT' ],
			[ 'CURLINFO_STARTTRANSFER_TIME' ],
			[ 'CURLINFO_TOTAL_TIME' ],
			[ 'CURLMSG_DONE' ],
			[ 'CURLM_BAD_EASY_HANDLE' ],
			[ 'CURLM_BAD_HANDLE' ],
			[ 'CURLM_CALL_MULTI_PERFORM' ],
			[ 'CURLM_INTERNAL_ERROR' ],
			[ 'CURLM_OK' ],
			[ 'CURLM_OUT_OF_MEMORY' ],
			[ 'CURLOPT_AUTOREFERER' ],
			[ 'CURLOPT_BINARYTRANSFER' ],
			[ 'CURLOPT_BUFFERSIZE' ],
			[ 'CURLOPT_CAINFO' ],
			[ 'CURLOPT_CAPATH' ],
			// array( 'CURLOPT_CERTINFO' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLOPT_CLOSEPOLICY' ), // removed in PHP 5.6.0
			[ 'CURLOPT_CONNECTTIMEOUT' ],
			[ 'CURLOPT_CONNECTTIMEOUT_MS' ],
			[ 'CURLOPT_COOKIE' ],
			[ 'CURLOPT_COOKIEFILE' ],
			[ 'CURLOPT_COOKIEJAR' ],
			[ 'CURLOPT_COOKIESESSION' ],
			[ 'CURLOPT_CRLF' ],
			[ 'CURLOPT_CUSTOMREQUEST' ],
			[ 'CURLOPT_DNS_CACHE_TIMEOUT' ],
			[ 'CURLOPT_DNS_USE_GLOBAL_CACHE' ],
			[ 'CURLOPT_EGDSOCKET' ],
			[ 'CURLOPT_ENCODING' ],
			[ 'CURLOPT_FAILONERROR' ],
			[ 'CURLOPT_FILE' ],
			[ 'CURLOPT_FILETIME' ],
			[ 'CURLOPT_FOLLOWLOCATION' ],
			[ 'CURLOPT_FORBID_REUSE' ],
			[ 'CURLOPT_FRESH_CONNECT' ],
			[ 'CURLOPT_FTPAPPEND' ],
			[ 'CURLOPT_FTPLISTONLY' ],
			[ 'CURLOPT_FTPPORT' ],
			[ 'CURLOPT_FTPSSLAUTH' ],
			[ 'CURLOPT_FTP_CREATE_MISSING_DIRS' ],
			// array( 'CURLOPT_FTP_FILEMETHOD' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLOPT_FTP_SKIP_PASV_IP' ), // not present in HHVM 3.3.0-dev
			[ 'CURLOPT_FTP_SSL' ],
			[ 'CURLOPT_FTP_USE_EPRT' ],
			[ 'CURLOPT_FTP_USE_EPSV' ],
			[ 'CURLOPT_HEADER' ],
			[ 'CURLOPT_HEADERFUNCTION' ],
			[ 'CURLOPT_HTTP200ALIASES' ],
			[ 'CURLOPT_HTTPAUTH' ],
			[ 'CURLOPT_HTTPGET' ],
			[ 'CURLOPT_HTTPHEADER' ],
			[ 'CURLOPT_HTTPPROXYTUNNEL' ],
			[ 'CURLOPT_HTTP_VERSION' ],
			[ 'CURLOPT_INFILE' ],
			[ 'CURLOPT_INFILESIZE' ],
			[ 'CURLOPT_INTERFACE' ],
			[ 'CURLOPT_IPRESOLVE' ],
			// array( 'CURLOPT_KEYPASSWD' ), // not present in HHVM 3.3.0-dev
			[ 'CURLOPT_KRB4LEVEL' ],
			[ 'CURLOPT_LOW_SPEED_LIMIT' ],
			[ 'CURLOPT_LOW_SPEED_TIME' ],
			[ 'CURLOPT_MAXCONNECTS' ],
			[ 'CURLOPT_MAXREDIRS' ],
			// array( 'CURLOPT_MAX_RECV_SPEED_LARGE' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLOPT_MAX_SEND_SPEED_LARGE' ), // not present in HHVM 3.3.0-dev
			[ 'CURLOPT_NETRC' ],
			[ 'CURLOPT_NOBODY' ],
			[ 'CURLOPT_NOPROGRESS' ],
			[ 'CURLOPT_NOSIGNAL' ],
			[ 'CURLOPT_PORT' ],
			[ 'CURLOPT_POST' ],
			[ 'CURLOPT_POSTFIELDS' ],
			[ 'CURLOPT_POSTQUOTE' ],
			[ 'CURLOPT_POSTREDIR' ],
			[ 'CURLOPT_PRIVATE' ],
			[ 'CURLOPT_PROGRESSFUNCTION' ],
			// array( 'CURLOPT_PROTOCOLS' ), // not present in HHVM 3.3.0-dev
			[ 'CURLOPT_PROXY' ],
			[ 'CURLOPT_PROXYAUTH' ],
			[ 'CURLOPT_PROXYPORT' ],
			[ 'CURLOPT_PROXYTYPE' ],
			[ 'CURLOPT_PROXYUSERPWD' ],
			[ 'CURLOPT_PUT' ],
			[ 'CURLOPT_QUOTE' ],
			[ 'CURLOPT_RANDOM_FILE' ],
			[ 'CURLOPT_RANGE' ],
			[ 'CURLOPT_READDATA' ],
			[ 'CURLOPT_READFUNCTION' ],
			// array( 'CURLOPT_REDIR_PROTOCOLS' ), // not present in HHVM 3.3.0-dev
			[ 'CURLOPT_REFERER' ],
			[ 'CURLOPT_RESUME_FROM' ],
			[ 'CURLOPT_RETURNTRANSFER' ],
			// array( 'CURLOPT_SSH_AUTH_TYPES' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLOPT_SSH_HOST_PUBLIC_KEY_MD5' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLOPT_SSH_PRIVATE_KEYFILE' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLOPT_SSH_PUBLIC_KEYFILE' ), // not present in HHVM 3.3.0-dev
			[ 'CURLOPT_SSLCERT' ],
			[ 'CURLOPT_SSLCERTPASSWD' ],
			[ 'CURLOPT_SSLCERTTYPE' ],
			[ 'CURLOPT_SSLENGINE' ],
			[ 'CURLOPT_SSLENGINE_DEFAULT' ],
			[ 'CURLOPT_SSLKEY' ],
			[ 'CURLOPT_SSLKEYPASSWD' ],
			[ 'CURLOPT_SSLKEYTYPE' ],
			[ 'CURLOPT_SSLVERSION' ],
			[ 'CURLOPT_SSL_CIPHER_LIST' ],
			[ 'CURLOPT_SSL_VERIFYHOST' ],
			[ 'CURLOPT_SSL_VERIFYPEER' ],
			[ 'CURLOPT_STDERR' ],
			[ 'CURLOPT_TCP_NODELAY' ],
			[ 'CURLOPT_TIMECONDITION' ],
			[ 'CURLOPT_TIMEOUT' ],
			[ 'CURLOPT_TIMEOUT_MS' ],
			[ 'CURLOPT_TIMEVALUE' ],
			[ 'CURLOPT_TRANSFERTEXT' ],
			[ 'CURLOPT_UNRESTRICTED_AUTH' ],
			[ 'CURLOPT_UPLOAD' ],
			[ 'CURLOPT_URL' ],
			[ 'CURLOPT_USERAGENT' ],
			[ 'CURLOPT_USERPWD' ],
			[ 'CURLOPT_VERBOSE' ],
			[ 'CURLOPT_WRITEFUNCTION' ],
			[ 'CURLOPT_WRITEHEADER' ],
			// array( 'CURLPROTO_ALL' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_DICT' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_FILE' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_FTP' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_FTPS' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_HTTP' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_HTTPS' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_LDAP' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_LDAPS' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_SCP' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_SFTP' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_TELNET' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLPROTO_TFTP' ), // not present in HHVM 3.3.0-dev
			[ 'CURLPROXY_HTTP' ],
			// array( 'CURLPROXY_SOCKS4' ), // not present in HHVM 3.3.0-dev
			[ 'CURLPROXY_SOCKS5' ],
			// array( 'CURLSSH_AUTH_DEFAULT' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLSSH_AUTH_HOST' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLSSH_AUTH_KEYBOARD' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLSSH_AUTH_NONE' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLSSH_AUTH_PASSWORD' ), // not present in HHVM 3.3.0-dev
			// array( 'CURLSSH_AUTH_PUBLICKEY' ), // not present in HHVM 3.3.0-dev
			[ 'CURLVERSION_NOW' ],
			[ 'CURL_HTTP_VERSION_1_0' ],
			[ 'CURL_HTTP_VERSION_1_1' ],
			[ 'CURL_HTTP_VERSION_NONE' ],
			[ 'CURL_IPRESOLVE_V4' ],
			[ 'CURL_IPRESOLVE_V6' ],
			[ 'CURL_IPRESOLVE_WHATEVER' ],
			[ 'CURL_NETRC_IGNORED' ],
			[ 'CURL_NETRC_OPTIONAL' ],
			[ 'CURL_NETRC_REQUIRED' ],
			[ 'CURL_TIMECOND_IFMODSINCE' ],
			[ 'CURL_TIMECOND_IFUNMODSINCE' ],
			[ 'CURL_TIMECOND_LASTMOD' ],
			[ 'CURL_VERSION_IPV6' ],
			[ 'CURL_VERSION_KERBEROS4' ],
			[ 'CURL_VERSION_LIBZ' ],
			[ 'CURL_VERSION_SSL' ],
		];
	}

	/**
	 * Added this test based on an issue experienced with HHVM 3.3.0-dev
	 * where it did not define a cURL constant.
	 *
	 * @bug 70570
	 * @dataProvider provideCurlConstants
	 */
	public function testCurlConstants( $value ) {
		$this->assertTrue( defined( $value ), $value . ' not defined' );
	}
}

/**
 * Class to let us overwrite MWHttpRequest respHeaders variable
 */
class MWHttpRequestTester extends MWHttpRequest {
	// function derived from the MWHttpRequest factory function but
	// returns appropriate tester class here
	public static function factory( $url, $options = null, $caller = __METHOD__ ) {
		if ( !Http::$httpEngine ) {
			Http::$httpEngine = function_exists( 'curl_init' ) ? 'curl' : 'php';
		} elseif ( Http::$httpEngine == 'curl' && !function_exists( 'curl_init' ) ) {
			throw new MWException( __METHOD__ . ': curl (https://php.net/curl) is not installed, but' .
				'Http::$httpEngine is set to "curl"' );
		}

		switch ( Http::$httpEngine ) {
			case 'curl':
				return new CurlHttpRequestTester( $url, $options, $caller );
			case 'php':
				if ( !wfIniGetBool( 'allow_url_fopen' ) ) {
					throw new MWException( __METHOD__ .
						': allow_url_fopen needs to be enabled for pure PHP HTTP requests to work. '
							. 'If possible, curl should be used instead. See https://php.net/curl.' );
				}

				return new PhpHttpRequestTester( $url, $options, $caller );
			default:
		}
	}
}

class CurlHttpRequestTester extends CurlHttpRequest {
	function setRespHeaders( $name, $value ) {
		$this->respHeaders[$name] = $value;
	}
}

class PhpHttpRequestTester extends PhpHttpRequest {
	function setRespHeaders( $name, $value ) {
		$this->respHeaders[$name] = $value;
	}
}
