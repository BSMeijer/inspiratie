<?php

namespace Drupal\FunctionalJavascriptTests\Theme;

use Drupal\FunctionalJavascriptTests\TableDrag\TableDragTest;

/**
 * Tests draggable tables with Claro theme.
 *
 * @group claro
 *
 * @see \Drupal\FunctionalJavascriptTests\TableDrag\TableDragTest
 */
class ClaroTableDragTest extends TableDragTest {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * {@inheritdoc}
   */
  protected static $indentationXpathSelector = 'child::td[1]/div[contains(concat(" ", normalize-space(@class), " "), " js-tabledrag-cell-content ")]/div[contains(concat(" ", normalize-space(@class), " "), " js-indentation ")]';

  /**
   * {@inheritdoc}
   */
  protected static $tabledragChangedXpathSelector = 'child::td[1]/div[contains(concat(" ", normalize-space(@class), " "), " js-tabledrag-cell-content ")]/abbr[contains(concat(" ", normalize-space(@class), " "), " tabledrag-changed ")]';

  /**
   * {@inheritdoc}
   */
  protected function findWeightsToggle($expected_text) {
    $toggle = $this->getSession()->getPage()->findLink($expected_text);
    $this->assertNotEmpty($toggle);
    return $toggle;
  }

  /**
   * Ensures that there are no duplicate tabledrag handles.
   */
  public function testNoDuplicates() {
    $this->drupalGet('tabledrag_test_nested');
    $this->assertCount(1, $this->findRowById(1)->findAll('css', '.tabledrag-handle'));
  }

  /**
   * Tests draggable table drag and drop.
   *
   * In Claro, the pointer should pass the horizontal center of the currently
   * hovered row for a successful swap. With NodeElement::dragTo() we cannot
   * specify any offset, it always drags the center of the NodeElement and
   * it always drops to the center of the given target NodeElement.
   *
   * This method is mostly the copy of TableDragTest::testDragAndDrop() with two
   * differences:
   *   - 'Drag row1 over row2' action was changed from $row1->dragTo($row2) to
   *     $row1->dragTo($row3).
   *   - Row3 drag to Row1 action was changed from $row3->dragTo($row2) to
   *     $row3->dragTo($row1).
   *
   * @see \Drupal\FunctionalJavascriptTests\TableDrag\TableDragTest::testDragAndDrop()
   */
  public function testDragAndDrop() {
    $this->state->set('tabledrag_test_table', array_flip(range(1, 3)));
    $this->drupalGet('tabledrag_test');

    $page = $this->getSession()->getPage();

    $weight_select1 = $page->findField("table[1][weight]");
    $weight_select2 = $page->findField("table[2][weight]");
    $weight_select3 = $page->findField("table[3][weight]");

    // Check that initially the rows are in the correct order.
    $this->assertOrder(['Row with id 1', 'Row with id 2', 'Row with id 3']);

    // Check that the 'unsaved changes' text is not present in the message area.
    $this->assertSession()->pageTextNotContains('You have unsaved changes.');

    // Get NodeElement for the drag handle of these rows.
    $row1 = $this->findRowById(1)->find('css', 'a.tabledrag-handle');
    $row2 = $this->findRowById(2)->find('css', 'a.tabledrag-handle');
    $row3 = $this->findRowById(3)->find('css', 'a.tabledrag-handle');

    // Drag row1 over row2.
    $row1->dragTo($row3);

    // Check that the 'unsaved changes' text was added in the message area.
    $this->assertSession()->waitForText('You have unsaved changes.');

    // Check that row1 and row2 were swapped.
    $this->assertOrder(['Row with id 2', 'Row with id 1', 'Row with id 3']);

    // Check that weights were changed.
    $this->assertGreaterThan($weight_select2->getValue(), $weight_select1->getValue());
    $this->assertGreaterThan($weight_select2->getValue(), $weight_select3->getValue());
    $this->assertGreaterThan($weight_select1->getValue(), $weight_select3->getValue());

    // Move the last row (row3) to the second position, row1 should go last.
    $row3->dragTo($row2);

    // Check that the order is: row2, row3 and row1.
    $this->assertOrder(['Row with id 2', 'Row with id 3', 'Row with id 1']);

  }

}
