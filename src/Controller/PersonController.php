<?php

namespace Drupal\person\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\StreamWrapper\PublicStream;

class PersonController extends ControllerBase {
  public function showPerson($nodeNumber) {
    $person = $this->entityTypeManager()->getStorage('node')->load($nodeNumber);
    $personFields = $person->toArray();

    $personContent = [
      '#theme' => 'person_template',
      '#nodeNumber' => $nodeNumber,
      '#person' => [ 'type' => $person->bundle(), 'id' => $person->id() ],
      '#title' => $person->label(),
      '#firstName' => $personFields['field_person_first_name'],
      '#lastName' => $personFields['field_person_last_name'],
      '#photo' => $this->getTargetIdsFromFieldArray($personFields['field_person_photo'], 'file'),
      '#jobTitle' => $personFields['field_person_job_title'][0]['value'],
      '#jobType' => $this->getTargetIdsFromFieldArray($personFields['field_person_job_type'], 'taxonomy_term'),
      '#department' => $this->getTargetIdsFromFieldArray($personFields['field_person_department'], 'taxonomy_term'),
      '#email' => $personFields['field_person_email'][0]['value'],
      '#phone' => $personFields['field_person_phone'][0]['value'],
      '#address' => $personFields['field_person_address'][0]['value'],
      '#officeHours' => $personFields['field_person_office_hours'][0]['value'],
      '#links' => $personFields['field_person_links'],
      '#bio' => $personFields['body'][0]['value'],
      '#filter1' => $this->getTargetIdsFromFieldArray($personFields['field_person_filter_1'], 'taxonomy_term'),
      '#filter2' => $this->getTargetIdsFromFieldArray($personFields['field_person_filter_2'], 'taxonomy_term'),
      '#filter3' => $this->getTargetIdsFromFieldArray($personFields['field_person_filter_3'], 'taxonomy_term'),
    ];

    return $personContent;
    // return new JsonResponse($personContent);
  }

  private function getTargetIdsFromFieldArray(array $fieldArray, $entityType) {
    $data = [];

    foreach ($fieldArray as $field) {
      if (!$field['target_id']) return;
      else {
        $fieldData = $this->entityTypeManager()->getStorage($entityType)->load($field['target_id']);

        switch ($entityType) {
          case 'taxonomy_term':
            $desiredFieldData = $fieldData->toArray()['name'][0]['value'];
            break;

          case 'file':
            $fileLocation = explode('://', $fieldData->getTypedData()->get('uri')->getString())[1];
            $baseFilepath = PublicStream::basePath();
            $desiredFieldData = [
              'uri' => implode('/', ['', $baseFilepath, $fileLocation]),
              'alt' => $field['alt'],
              'targetId' => $field['target_id'],
              'bundle' => $fieldData->bundle()
            ];
            break;

          default:
            $desiredFieldData = $fieldData;
            break;
        }

        array_push($data, $desiredFieldData);
      }
    }

    return $data;
  }
}