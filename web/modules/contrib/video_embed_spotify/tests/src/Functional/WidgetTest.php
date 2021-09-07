<?php

namespace Drupal\Tests\video_embed_spotify\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\video_embed_field\Functional\EntityDisplaySetupTrait;
use Drupal\Tests\video_embed_field\Functional\AdminUserTrait;

/**
 * Test the video embed field widget.
 *
 * @group video_embed_spotify
 */
class WidgetTest extends BrowserTestBase {

  use EntityDisplaySetupTrait;
  use AdminUserTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field_ui',
    'node',
    'video_embed_field',
    'video_embed_spotify',
  ];

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Use the standard profile.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Test the input widget.
   */
  public function testVideoEmbedFieldDefaultWidget() {
    $this->drupalLogin($this->createAdminUser());

    $this->setupEntityDisplays();
    $this->setFormComponentSettings('video_embed_field_textfield');

    $node_title = $this->randomMachineName();

    // Test an invalid input.
    $this->drupalGet(Url::fromRoute('node.add', ['node_type' => $this->contentTypeName])->toString());
    $this->submitForm([
      'title[0][value]' => $node_title,
      $this->fieldName . '[0][value]' => 'Some useless value.',
    ], 'Save');
    $this->assertSession()->pageTextContains('Could not find a video provider to handle the given URL.');

    // Test a valid input.
    $valid_input = 'https://open.spotify.com/album/49sy06JJUk1CRwkG9wbRY';
    $this->submitForm([
      $this->fieldName . '[0][value]' => $valid_input,
    ], 'Save');
    $this->assertSession()->pageTextContains(sprintf('%s %s has been created.', $this->contentTypeName, $node_title));

    // Load the saved node and assert the valid value was saved into the field.
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['title' => $node_title]);
    $node = array_shift($nodes);
    $this->assertEquals($node->{$this->fieldName}[0]->value, $valid_input);
  }

}
