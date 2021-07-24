<?php

namespace Drupal\Tests\multiple_selects\Functional;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\taxonomy\Traits\TaxonomyTestTrait;

/**
 * Tests the multiple select widget.
 *
 * @group multiple_selects
 */
class MultipleSelectsWidgetUiTest extends BrowserTestBase {

  use EntityReferenceTestTrait;
  use TaxonomyTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'multiple_selects',
    'node',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * An array containing the created tags.
   *
   * @var array
   */
  protected $tags = [];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->rootUser);

    $this->drupalCreateContentType(['type' => 'page']);

    // Create a new vocabulary.
    $vocabulary = Vocabulary::create(['vid' => 'tags', 'name' => 'tags']);
    $vocabulary->save();

    for ($i = 0; $i < 10; $i++) {
      $this->tags[] = $this->createTerm($vocabulary);
    }

    // Create entity reference field with taxonomy term as a target.
    $handler_settings = [
      'target_bundles' => [
        $vocabulary->id() => $vocabulary->id(),
      ],
      'auto_create' => TRUE,
      'auto_create_bundle' => $vocabulary->id(),
    ];
    $this->createEntityReferenceField('node', 'page', 'field_tags', 'Tags', 'taxonomy_term', 'default', $handler_settings, FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    $this->container->get('entity_display.repository')
      ->getFormDisplay('node', 'page')
      ->setComponent('field_tags', ['type' => 'multiple_options_select'])
      ->save();
  }

  /**
   * Test that using the widget saves the values correctly to the entity.
   */
  public function testWidget() {
    $this->drupalGet('node/add/page');

    // Check that the second field is not enabled by default.
    $this->assertSession()->selectExists('field_tags[0][target_id]');
    $this->assertSession()->fieldNotExists('field_tags[1][target_id]');

    // Click the add another item button.
    $this->assertSession()->buttonExists('Add another item');
    $this->click('.field--name-field-tags .field-add-more-submit');

    // Second select should be visible now.
    $this->assertSession()->selectExists('field_tags[0][target_id]');
    $this->assertSession()->selectExists('field_tags[1][target_id]');

    // Submit a node with multiple tag values.
    $title = $this->randomMachineName();
    $tag_1_key = array_rand($this->tags);
    $tag_1 = $this->tags[$tag_1_key];
    $tag_2_key = array_rand($this->tags);
    $tag_2 = $this->tags[$tag_2_key];
    $this->drupalPostForm(NULL, [
      'title[0][value]' => $title,
      'field_tags[0][target_id]' => $tag_1->id(),
      'field_tags[1][target_id]' => $tag_2->id(),
    ], 'Save');

    // Check that the node was saved.
    $this->assertSession()->pageTextContains("Page $title has been created.");

    // Check that the values are correctly saved.
    $node = $this->drupalGetNodeByTitle($title);
    $this->assertEquals($tag_1->id(), $node->get('field_tags')->get(0)->target_id);
    $this->assertEquals($tag_2->id(), $node->get('field_tags')->get(1)->target_id);
    $this->assertEmpty($node->get('field_tags')->get(2));

    // Check that the values are correctly shown.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $tag_value_1 = $this->assertSession()->selectExists('field_tags[0][target_id]')->getValue();
    $this->assertEquals($tag_1->id(), $tag_value_1);
    $tag_value_2 = $this->assertSession()->selectExists('field_tags[1][target_id]')->getValue();
    $this->assertEquals($tag_2->id(), $tag_value_2);

    // The 3rd field should be visible but has no value.
    $tag_value_3 = $this->assertSession()->selectExists('field_tags[2][target_id]')->getValue();
    $this->assertEquals('_none', $tag_value_3);
  }

  /**
   * Test the widget with multiple values when required.
   */
  public function testWidgetRequired() {
    $field_config = FieldConfig::loadByName('node', 'page', 'field_tags');
    $field_config->setRequired(TRUE);
    $field_config->save();

    $title = $this->randomMachineName();
    $this->drupalPostForm('node/add/page', [
      'title[0][value]' => $title,
    ], 'Save');

    // Since the field is required, the form should throw an error.
    $this->assertSession()->pageTextContains('Tags field is required.');

    $tag_1_key = array_rand($this->tags);
    $tag_1 = $this->tags[$tag_1_key];
    $this->drupalPostForm(NULL, [
      'title[0][value]' => $title,
      'field_tags[0][target_id]' => $tag_1->id(),
    ], 'Save');

    $node = $this->drupalGetNodeByTitle($title);

    $this->drupalPostForm('node/' . $node->id() . '/edit', [
      'title[0][value]' => $title,
      'field_tags[0][target_id]' => $tag_1->id(),
    ], 'Save');

    // Check that the node was updated.
    $this->assertSession()->pageTextContains("Page $title has been updated.");
  }

  /**
   * Test widget with a fixed cardinality.
   */
  public function testCardinality() {
    $field_storage_config = FieldStorageConfig::loadByName('node', 'field_tags');
    $field_storage_config->setCardinality(3);
    $field_storage_config->save();

    $this->drupalGet('node/add/page');
    $this->assertSession()->selectExists('field_tags[0][target_id]');
    $this->assertSession()->selectExists('field_tags[1][target_id]');
    $this->assertSession()->selectExists('field_tags[2][target_id]');
    $this->assertSession()->fieldNotExists('field_tags[4][target_id]');
    $this->assertSession()->buttonNotExists('Add another item');
  }

  /**
   * Test widget with cardinality 1.
   */
  public function testSingleWidget() {
    $field_storage_config = FieldStorageConfig::loadByName('node', 'field_tags');
    $field_storage_config->setCardinality(1);
    $field_storage_config->save();

    $this->drupalGet('node/add/page');
    $this->assertSession()->selectExists('field_tags[0][target_id]');
    $this->assertSession()->fieldNotExists('field_tags[1][target_id]');
    $this->assertSession()->buttonNotExists('Add another item');

    $title = $this->randomMachineName();
    $tag_1_key = array_rand($this->tags);
    $tag_1 = $this->tags[$tag_1_key];
    $this->drupalPostForm(NULL, [
      'title[0][value]' => $title,
      'field_tags[0][target_id]' => $tag_1->id(),
    ], 'Save');

    // Check that the node was saved.
    $this->assertSession()->pageTextContains("Page $title has been created.");

    // Check that the values are correctly saved.
    $node = $this->drupalGetNodeByTitle($title);
    $this->assertEquals($tag_1->id(), $node->get('field_tags')->get(0)->target_id);
    $this->assertEmpty($node->get('field_tags')->get(1));

    // Check that the values are correctly shown.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $tag_value_1 = $this->assertSession()->selectExists('field_tags[0][target_id]')->getValue();
    $this->assertEquals($tag_1->id(), $tag_value_1);
    $this->assertSession()->fieldNotExists('field_tags[1][target_id]');
  }

  /**
   * Test the single widget when required.
   */
  public function testSingleWidgetRequired() {
    $field_storage_config = FieldStorageConfig::loadByName('node', 'field_tags');
    $field_storage_config->setCardinality(1);
    $field_storage_config->save();

    $field_config = FieldConfig::loadByName('node', 'page', 'field_tags');
    $field_config->setRequired(TRUE);
    $field_config->save();

    $title = $this->randomMachineName();
    $this->drupalPostForm('node/add/page', [
      'title[0][value]' => $title,
    ], 'Save');

    // Since the field is required, the form should throw an error.
    $this->assertSession()->pageTextContains('Tags field is required.');

    $tag_1_key = array_rand($this->tags);
    $tag_1 = $this->tags[$tag_1_key];
    $this->drupalPostForm('node/add/page', [
      'title[0][value]' => $title,
      'field_tags[0][target_id]' => $tag_1->id(),
    ], 'Save');

    // Check that the node was saved.
    $this->assertSession()->pageTextContains("Page $title has been created.");
  }

}
