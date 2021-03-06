<?php
/**
 * @group GlobalFunctions
 * @covers ::wfExpandUrl
 */
class WfExpandUrlTest extends MediaWikiTestCase {
	/**
	 * @dataProvider provideExpandableUrls
	 */
	public function testWfExpandUrl( $fullUrl, $shortUrl, $defaultProto,
		$server, $canServer, $httpsMode, $message
	) {
		// Fake $wgServer, $wgCanonicalServer and $wgRequest->getProtocol()
		$this->setMwGlobals( [
			'wgServer' => $server,
			'wgCanonicalServer' => $canServer,
			'wgRequest' => new FauxRequest( [], false, null, $httpsMode ? 'https' : 'http' )
		] );

		$this->assertEquals( $fullUrl, wfExpandUrl( $shortUrl, $defaultProto ), $message );
	}

	/**
	 * Provider of URL examples for testing wfExpandUrl()
	 *
	 * @return array
	 */
	public static function provideExpandableUrls() {
		$modes = [ 'http', 'https' ];
		$servers = [
			'http' => 'https://example.com',
			'https' => 'https://example.com',
			'protocol-relative' => '//example.com'
		];
		$defaultProtos = [
			'http' => PROTO_HTTP,
			'https' => PROTO_HTTPS,
			'protocol-relative' => PROTO_RELATIVE,
			'current' => PROTO_CURRENT,
			'canonical' => PROTO_CANONICAL
		];

		$retval = [];
		foreach ( $modes as $mode ) {
			$httpsMode = $mode == 'https';
			foreach ( $servers as $serverDesc => $server ) {
				foreach ( $modes as $canServerMode ) {
					$canServer = "$canServerMode://example2.com";
					foreach ( $defaultProtos as $protoDesc => $defaultProto ) {
						$retval[] = [
							'https://example.com', 'https://example.com',
							$defaultProto, $server, $canServer, $httpsMode,
							"Testing fully qualified http URLs (no need to expand) "
								. "(defaultProto: $protoDesc , wgServer: $server, "
								. "wgCanonicalServer: $canServer, current request protocol: $mode )"
						];
						$retval[] = [
							'https://example.com', 'https://example.com',
							$defaultProto, $server, $canServer, $httpsMode,
							"Testing fully qualified https URLs (no need to expand) "
								. "(defaultProto: $protoDesc , wgServer: $server, "
								. "wgCanonicalServer: $canServer, current request protocol: $mode )"
						];
						# Would be nice to support this, see fixme on wfExpandUrl()
						$retval[] = [
							"wiki/FooBar", 'wiki/FooBar',
							$defaultProto, $server, $canServer, $httpsMode,
							"Test non-expandable relative URLs (defaultProto: $protoDesc, "
								. "wgServer: $server, wgCanonicalServer: $canServer, "
								. "current request protocol: $mode )"
						];

						// Determine expected protocol
						if ( $protoDesc == 'protocol-relative' ) {
							$p = '';
						} elseif ( $protoDesc == 'current' ) {
							$p = "$mode:";
						} elseif ( $protoDesc == 'canonical' ) {
							$p = "$canServerMode:";
						} else {
							$p = $protoDesc . ':';
						}
						// Determine expected server name
						if ( $protoDesc == 'canonical' ) {
							$srv = $canServer;
						} elseif ( $serverDesc == 'protocol-relative' ) {
							$srv = $p . $server;
						} else {
							$srv = $server;
						}

						$retval[] = [
							"$p//wikipedia.org", '//wikipedia.org',
							$defaultProto, $server, $canServer, $httpsMode,
							"Test protocol-relative URL (defaultProto: $protoDesc, "
								. "wgServer: $server, wgCanonicalServer: $canServer, "
								. "current request protocol: $mode )"
						];
						$retval[] = [
							"$srv/wiki/FooBar",
							'/wiki/FooBar',
							$defaultProto,
							$server,
							$canServer,
							$httpsMode,
							"Testing expanding URL beginning with / (defaultProto: $protoDesc, "
								. "wgServer: $server, wgCanonicalServer: $canServer, "
								. "current request protocol: $mode )"
						];
					}
				}
			}
		}

		return $retval;
	}
}
