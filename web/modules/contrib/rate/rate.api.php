<?php

/**
 * @file
 * Provides hook documentation for the Rate module.
 */

/**
 * Alter the options of a rate widget.
 *
 * Override the options (value, label, class) of a rate widget.
 * The options are used to generate the inputs for the rate widget.
 * This hook is called before the rate form is generated.
 *
 * @param array $options
 *   An array of rate widget options.
 * @param string $entity_type
 *   The voted entity type.
 * @param string $entity_bundle
 *   The voted entity bundle.
 * @param int $entity_id
 *   The voted entity id.
 * @param string $rate_widget
 *   The rate widget being used for voting.
 */
function hook_rate_widget_options_alter(array &$options, $entity_type, $entity_bundle, $entity_id, $rate_widget) {
  // Trigger only on articles and specific widgets.
  if ($rate_widget == 'test_rate_widget' && $entity_bundle = 'article') {
    foreach ($options as $key => $option) {
      // Increase the value for each option by 10.
      $options[$key]['value'] = $option['value'] * 10;
      // Change the label of each option.
      $options[$key]['label'] = $option['label'] . '-test';
      // Add a class.
      $options[$key]['class'] = $option['class'] . ' test-class';
    }
  }
}

/**
 * Define templates for rate widgets.
 *
 * @return array
 *   Array of template objects, keyed by the template name.
 */
function hook_rate_templates() {
  $templates = [];

  $templates['thumbs_up_down'] = new stdClass();
  $templates['thumbs_up_down']->value_type = 'points';
  $templates['thumbs_up_down']->options = [
    ['value' => 1, 'label' => 'up', 'class' => 'rate-updown-up'],
    ['value' => -1, 'label' => 'down', 'class' => 'rate-updown-down'],
  ];
  $templates['thumbs_up_down']->customizable = FALSE;
  $templates['thumbs_up_down']->translate = TRUE;
  $templates['thumbs_up_down']->template_title = t('Thumbs up / down');

  $templates['fivestar'] = new stdClass();
  $templates['fivestar']->value_type = 'percent';
  $templates['fivestar']->options = [
    ['value' => 0, 'label' => '1'],
    ['value' => 25, 'label' => '2'],
    ['value' => 50, 'label' => '3'],
    ['value' => 75, 'label' => '4'],
    ['value' => 100, 'label' => '5'],
  ];
  $templates['fivestar']->customizable = FALSE;
  $templates['fivestar']->translate = FALSE;
  $templates['fivestar']->template_title = t('Fivestar');

  return $templates;
}

/**
 * Determine if user can vote.
 *
 * Provide a way for custom checks of user ability to vote.
 *
 * @param bool $can_vote
 *   Boolean to determine if user can vote.
 * @param \Drupal\votingapi\Entity\Vote $vote
 *   An array of rate widget options.
 * @param object $entity
 *   The voted entity object.
 * @param \Drupal\Core\Session\AccountProxy $account
 *   The current user account.
 */
function hook_rate_can_vote(&$can_vote, \Drupal\votingapi\Entity\Vote $vote, $entity, \Drupal\Core\Session\AccountProxy $account) {
  // Forbid user to vote on all other entities except for article.
  if ($entity->bundle() != 'article') {
    $can_vote = FALSE;
  }
}
