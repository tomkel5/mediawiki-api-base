<?php

namespace Mediawiki\Api\Test\Unit;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiSession;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\MediawikiSession
 */
class MediawikiSessionTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @return PHPUnit_Framework_MockObject_MockObject|MediawikiApi
	 */
	private function getMockApi() {
		return $this->createMock( '\Mediawiki\Api\MediawikiApi' );
	}

	public function testConstruction() {
		$session = new MediawikiSession( $this->getMockApi() );
		$this->assertInstanceOf( '\Mediawiki\Api\MediawikiSession', $session );
	}

	/**
	 * @dataProvider provideTokenTypes
	 */
	public function testGetToken( $tokenType ) {
		$mockApi = $this->getMockApi();
		$mockApi->expects( $this->exactly( 2 ) )
			->method( 'postRequest' )
			->with( $this->isInstanceOf( '\Mediawiki\Api\SimpleRequest' ) )
			->will( $this->returnValue( [
				'query' => [
					'tokens' => [
					$tokenType => 'TKN-' . $tokenType,
					]
				]
			] ) );

		$session = new MediawikiSession( $mockApi );

		// Although we make 2 calls to the method we assert the tokens method about is only called once
		$this->assertEquals( 'TKN-' . $tokenType, $session->getToken() );
		$this->assertEquals( 'TKN-' . $tokenType, $session->getToken() );
		// Then clearing the tokens and calling again should make a second call!
		$session->clearTokens();
		$this->assertEquals( 'TKN-' . $tokenType, $session->getToken() );
	}

	/**
	 * @dataProvider provideTokenTypes
	 */
	public function testGetTokenPre125( $tokenType ) {
		$mockApi = $this->getMockApi();
		$mockApi->expects( $this->at( 0 ) )
			->method( 'postRequest' )
			->with( $this->isInstanceOf( '\Mediawiki\Api\SimpleRequest' ) )
			->will( $this->returnValue( [
				'warnings' => [
					'query' => [
						'*' => "Unrecognized value for parameter 'meta': tokens",
					]
				]
			] ) );
		$mockApi->expects( $this->at( 1 ) )
			->method( 'postRequest' )
			->with( $this->isInstanceOf( '\Mediawiki\Api\SimpleRequest' ) )
			->will( $this->returnValue( [
				'tokens' => [
					$tokenType => 'TKN-' . $tokenType,
				]
			] ) );

		$session = new MediawikiSession( $mockApi );

		// Although we make 2 calls to the method we assert the tokens method about is only called once
		$this->assertSame( 'TKN-' . $tokenType, $session->getToken() );
		$this->assertSame( 'TKN-' . $tokenType, $session->getToken() );
	}

	public function provideTokenTypes() {
		return [
			[ 'csrf' ],
			[ 'edit' ],
		];
	}

}
