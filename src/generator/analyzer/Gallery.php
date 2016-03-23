<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 23.03.16 at 11:45
 */
namespace samsoncms\api\generator\analyzer;

use samsoncms\api\Field;
use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsoncms\api\Navigation;

/**
 * Generic entities metadata analyzer.
 *
 * @package samsoncms\api\analyzer
 */
class Gallery extends Virtual
{
    /**
     * Analyze virtual entities and gather their metadata.
     *
     * @return \samsoncms\api\generator\metadata\Virtual[]
     * @throws ParentEntityNotFound
     */
    public function analyze()
    {
        $metadataCollection = [];

        // Iterate all structures, parents first
        foreach ($this->getVirtualEntities() as $structureRow) {
            // Fill in entity metadata
            $metadata = new \samsoncms\api\generator\metadata\Gallery();
            $navigationID = $structureRow[Navigation::F_PRIMARY];

            // Iterate entity fields
            foreach ($this->getEntityFields($navigationID) as $fieldID => $fieldRow) {
                // We need only gallery fields
                if ((int)$fieldRow[Field::F_TYPE] === Field::TYPE_GALLERY) {
                    // Get camelCase and transliterated field name
                    $metadata->entity = $this->fieldName($fieldRow[Field::F_IDENTIFIER]);
                    $metadata->entityClassName = $this->fullEntityName($metadata->entity);
                    $metadata->realName = $fieldRow[Field::F_IDENTIFIER];
                    $metadata->fieldID = $fieldID;
                    $metadata->parentID = $navigationID;
                }

                // Store metadata by entity identifier
                $metadataCollection[$navigationID] = $metadata;
            }
        }


        return $metadataCollection;
    }
}
//[PHPCOMPRESSOR(remove,end)]