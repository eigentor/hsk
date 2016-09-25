<?php

/**
 * @file
 * Contains \Drupal\quick_node_clone\Form\QuickNodeCloneNodeForm
 */

namespace Drupal\quick_node_clone\Form;

use Drupal\node\Entity\Node;
use Drupal\node\NodeForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\Core\Routing\RouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Quick Node Clone edit forms. We can override most of
 * the node form from here! Hooray!
 */
class QuickNodeCloneNodeForm extends NodeForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    $element['submit']['#access'] = TRUE;
    $element['submit']['#value'] = $this->t('Save New Clone');

    if ($element['submit']['#access'] && \Drupal::currentUser()->hasPermission('access content overview')) {
      // Add a "Publish" button.
      $element['publish'] = $element['submit'];
      // If the "Publish" button is clicked, we want to update the status to "published".
      $element['publish']['#published_status'] = TRUE;
      $element['publish']['#value'] = t('Save New Clone');
      $element['publish']['#weight'] = 0;

      // Remove the "Save" button.
      $element['submit']['#access'] = FALSE;
      $element['unpublish']['#access'] = FALSE;
    }

    $element['delete']['#access'] = FALSE;
    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * Updates the node object by processing the submitted values.
   *
   * This function can be called by a "Next" button of a wizard to update the
   * form state's entity with the current step's values before proceeding to the
   * next step.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the node object from the submitted values.
    parent::submitForm($form, $form_state);
    $node = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('revision') && $form_state->getValue('revision') != FALSE) {
      $node->setNewRevision();
      // If a new revision is created, save the current user as revision author.
      $node->setRevisionCreationTime(REQUEST_TIME);
      $node->setRevisionAuthorId(\Drupal::currentUser()->id());
    }
    else {
      $node->setNewRevision(FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $node = $this->entity;
    $insert = $node->isNew();
    $node->save();
    $node_link = $node->link($this->t('View'));
    $context = array('@type' => $node->getType(), '%title' => $node->label(), 'link' => $node_link);
    $t_args = array('@type' => node_get_type_label($node), '%title' => $node->label());

    if ($insert) {
      $this->logger('content')->notice('@type: added %title (clone).', $context);
      drupal_set_message(t('@type %title (clone) has been created.', $t_args));
    }

    if ($node->id()) {
      $form_state->setValue('nid', $node->id());
      $form_state->set('nid', $node->id());
      if ($node->access('view')) {
        $form_state->setRedirect(
          'entity.node.canonical',
          array('node' => $node->id())
        );
      }
      else {
        $form_state->setRedirect('<front>');
      }

    }
    else {
      // In the unlikely case something went wrong on save, the node will be
      // rebuilt and node form redisplayed the same way as in preview.
      drupal_set_message(t('The cloned post could not be saved.'), 'error');
      $form_state->setRebuild();
    }
  }

}
