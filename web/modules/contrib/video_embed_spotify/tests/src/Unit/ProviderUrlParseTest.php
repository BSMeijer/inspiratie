<?php

namespace Drupal\Tests\video_embed_spotify\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\video_embed_spotify\Plugin\video_embed_field\Provider\Spotify;

/**
 * Test that URL parsing for the provider is functioning.
 *
 * @group video_embed_spotify
 */
class ProviderUrlParseTest extends UnitTestCase {

  /**
   * Test URL parsing works as expected.
   *
   * @dataProvider urlsWithExpectedIds
   */
  public function testUrlParsing($url, $expected) {
    $this->assertEquals($expected, Spotify::getIdFromInput($url));
  }

  /**
   * A data provider for URL parsing test cases.
   *
   * @return array
   *   An array of test cases.
   */
  public function urlsWithExpectedIds() {
    return [
      [
        'https://open.spotify.com/playlist/2PXdUld4Ueio2pHcB6sM8j',
        '2PXdUld4Ueio2pHcB6sM8j',
      ],
      [
        'https://open.spotify.com/playlist/2PXdUld4Ueio2pHcB6sM8j?si=SMIEFaWpQZWGo4xnvR2Gkm',
        '2PXdUld4Ueio2pHcB6sM8j',
      ],
      [
        'https://open.spotify.com/album/49sy06JJUk1CRwkG9wbRY',
        '49sy06JJUk1CRwkG9wbRY',
      ],
      [
        'https://open.spotify.com/artist/2uFUBdaVGtyMqckSeCl0Q',
        '2uFUBdaVGtyMqckSeCl0Q',
      ],
      [
        'https://open.spotify.com/show/6R2ywcv4TDsbFDhMM2e5J',
        '6R2ywcv4TDsbFDhMM2e5J',
      ],
      [
        'https://open.spotify.com/episode/0zB1BQQGh1Jrqo1FLI4Ce',
        '0zB1BQQGh1Jrqo1FLI4Ce',
      ],
      [
        'https://open.spotify.com/track/1XuxjobTmFFSMS1vWx7pJm',
        '1XuxjobTmFFSMS1vWx7pJm',
      ],
      [
        'https://open.spotify.com/track/1McXbEv5YKBK8gSbJNlGw?context=spotify%3Aplaylist%3A37i9dQZF1X07Bg0Q5GozA&si=3CRC5IuISZK-eh5G_lxHPQ',
        '1McXbEv5YKBK8gSbJNlGw',
      ],
    ];
  }

}
