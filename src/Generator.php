<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 14:34
 */
namespace samsoncms\api;

use samsonframework\orm\DatabaseInterface;

/**
 * Entity classes generator.
 * @package samsoncms\api
 */
class Generator
{
    /** @var DatabaseInterface */
    protected $database;

    /**
     * Transliterate string to english.
     *
     * @param string $string Source string
     * @return string Transliterated string
     */
    protected function transliterated($string)
    {
        return str_replace(
            ' ',
            '',
            ucwords(iconv("UTF-8", "UTF-8//IGNORE", strtr($string, array(
                            "'" => "",
                            "`" => "",
                            "-" => " ",
                            "_" => " ",
                            "а" => "a", "А" => "a",
                            "б" => "b", "Б" => "b",
                            "в" => "v", "В" => "v",
                            "г" => "g", "Г" => "g",
                            "д" => "d", "Д" => "d",
                            "е" => "e", "Е" => "e",
                            "ж" => "zh", "Ж" => "zh",
                            "з" => "z", "З" => "z",
                            "и" => "i", "И" => "i",
                            "й" => "y", "Й" => "y",
                            "к" => "k", "К" => "k",
                            "л" => "l", "Л" => "l",
                            "м" => "m", "М" => "m",
                            "н" => "n", "Н" => "n",
                            "о" => "o", "О" => "o",
                            "п" => "p", "П" => "p",
                            "р" => "r", "Р" => "r",
                            "с" => "s", "С" => "s",
                            "т" => "t", "Т" => "t",
                            "у" => "u", "У" => "u",
                            "ф" => "f", "Ф" => "f",
                            "х" => "h", "Х" => "h",
                            "ц" => "c", "Ц" => "c",
                            "ч" => "ch", "Ч" => "ch",
                            "ш" => "sh", "Ш" => "sh",
                            "щ" => "sch", "Щ" => "sch",
                            "ъ" => "", "Ъ" => "",
                            "ы" => "y", "Ы" => "y",
                            "ь" => "", "Ь" => "",
                            "э" => "e", "Э" => "e",
                            "ю" => "yu", "Ю" => "yu",
                            "я" => "ya", "Я" => "ya",
                            "і" => "i", "І" => "i",
                            "ї" => "yi", "Ї" => "yi",
                            "є" => "e", "Є" => "e"
                        )
                    )
                )
            )
        );
    }

    /**
     * Get class constant name by its value.
     *
     * @param string $value Constant value
     * @param string $className Class name
     * @return string Full constant name
     */
    protected function constantNameByValue($value, $className = Field::ENTITY)
    {
        // Get array where class constants are values and their values are keys
        $nameByValue = array_flip((new \ReflectionClass($className))->getConstants());

        // Try to find constant by its value
        if (isset($nameByValue[$value])) {
            // Return constant name
            return $nameByValue[$value];
        }
    }

    /**
     * Create entity PHP class code.
     *
     * @param array $structureRow Collection of structure info
     * @return string Generated entitiy class code
     */
    protected function createEntityClass($structureRow)
    {
        $structureKey = ucfirst($this->transliterated($structureRow['Name']));

        $class = "\n\n" . '/** "'.$structureRow['Name'].'" entity */';
        $class .= "\n" . 'class ' . $structureKey . ' extends Entity';
        $class .= "\n" . '{';

        // Get structure fields
        //$fieldMap = array();
        $fields = array();
        $fieldIDs = array();

        // TODO: Optimize queries
        foreach ($this->database->fetch('SELECT * FROM `structurefield` WHERE `StructureID` = "' . $structureRow['StructureID'] . '" AND `Active` = "1"') as $fieldStructureRow) {
            foreach ($this->database->fetch('SELECT * FROM `field` WHERE `FieldID` = "' . $fieldStructureRow['FieldID'] . '"') as $fieldRow) {
                $type = str_replace(
                    '\samsoncms\api\Field',
                    'Field',
                    $this->constantNameByValue($fieldRow['Type'])
                );
                $fieldName = lcfirst($this->transliterated($fieldRow['Name']));

                $class .= "\n\t" . '/** @var ' . Field::phpType($fieldRow['Type']) . ' Field #' . $fieldRow['FieldID'] . '*/';
                $class .= "\n\t" . 'protected $' . $fieldName . ';';

                // Store field metadata
                $fields[$fieldName][] = $fieldRow;
                $fieldIDs[] = $fieldRow['FieldID'];
                //$fieldMap[] = '"'.$fieldName.'" => array("Id" => "'.$fieldRow['FieldID'].'", "Type" => ' . $type . ', "Name" => "' . $fieldRow['Name'] . '")';
            }
        }

        //$class .= "\n\t" . '/** @var array Entity additional fields metadata */';
        //$class .= "\n\t" .'protected $fieldsData = array('."\n\t\t".implode(','."\n\t\t", $fieldMap)."\n\t".');';
        $class .= "\n\t";
        $class .= "\n\t" . '/** @var string Not transliterated entity name */';
        $class .= "\n\t" . 'protected static $viewName = "' . $structureRow['Name'] . '";';
        $class .= "\n\t" . '/** @var array Collection of additional fields identifiers */';
        $class .= "\n\t" . 'protected static $fieldIDs = array(' . implode(',', $fieldIDs) . ');';
        $class .= "\n\t" . '/** @var array Collection of navigation identifiers */';
        $class .= "\n\t" . 'protected static $navigationIDs = array(' . $structureRow['StructureID'] . ');';
        $class .= "\n" . '}';

        // Replace tabs with spaces
        return str_replace("\t", '    ', $class);
    }

    /**
     * Get correct entity name.
     *
     * @param string $navigationName Original navigation entity name
     * @return string Correct PHP-supported entity name
     */
    protected function entityName($navigationName)
    {
        return ucfirst($this->transliterated($navigationName));
    }

    /**
     * Get correct full entity name with name space.
     *
     * @param string $navigationName Original navigation entity name
     * @param string $namespace Namespace
     * @return string Correct PHP-supported entity name
     */
    protected function fullEntityName($navigationName, $namespace = __NAMESPACE__)
    {
        return $namespace.$this->entityName($navigationName);
    }

    /**
     * Get correct field name.
     *
     * @param string $fieldName Original field name
     * @return string Correct PHP-supported field name
     */
    protected function fieldName($fieldName)
    {
        return $fieldName = lcfirst($this->transliterated($fieldName));
    }

    /**
     * Get additional field type in form of Field constant name
     * by database additional field type identifier.
     *
     * @param integer $fieldType Additional field type identifier
     * @return string Additional field type constant
     */
    protected function additionalFieldType($fieldType)
    {
        return 'Field::'.$this->constantNameByValue($fieldType);
    }

    /**
     * Generate Query::where() analog for specific field.
     *
     * @param string $fieldName Field name
     * @param string $fieldId Field primary identifier
     * @param string $fieldType Field PHP type
     * @return string Generated PHP method code
     */
    protected function generateFieldConditionMethod($fieldName, $fieldId, $fieldType)
    {
        $code = "\n\t" . '/**';
        $code .= "\n\t" . ' * Add '.$fieldName.'(#' . $fieldId . ') field query condition.';
        $code .= "\n\t" . ' * @param '.Field::phpType($fieldType).' $value Field value';
        $code .= "\n\t" . ' * @return self Chaining';
        $code .= "\n\t" . ' * @see Generic::where()';
        $code .= "\n\t" . ' */';
        $code .= "\n\t" . 'public function ' . $fieldName . '($value)';
        $code .= "\n\t" . "{";
        $code .= "\n\t\t" . 'return $this->where("'.$fieldName.'", $value);';

        return $code . "\n\t" . "}"."\n";
    }

    /**
     * Create entity query PHP class code.
     *
     * @param integer $navigationID Entity navigation identifier
     * @param string $navigationName Original entity name
     * @param string $entityName PHP entity name
     * @param array $navigationFields Collection of entity additional fields
     * @return string Generated entity query PHP class code
     */
    protected function createQueryClass($navigationID, $navigationName, $entityName, $navigationFields)
    {
        $class = "\n\n" . '/** Class for getting "'.$navigationName.'" instances from database */';
        $class .= "\n" . 'class ' . $entityName . ' extends Generic';
        $class .= "\n" . '{';

        // Iterate additional fields
        $fieldIDs = array();
        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $class .= $this->generateFieldConditionMethod(
                $fieldName,
                $fieldRow['FieldID'],
                $fieldRow['Type']
            );

            // Store field metadata
            $fieldIDs[] = '"' . $fieldName . '" => "' . $fieldID . '"';
        }

        $class .= "\n\t";
        $class .= "\n\t" . '/** @var string Not transliterated entity name */';
        $class .= "\n\t" . 'protected static $identifier = "'.$this->fullEntityName($navigationName).'";';
        $class .= "\n\t" . '/** @var array Collection of navigation identifiers */';
        $class .= "\n\t" . 'protected static $navigationIDs = array(' . $navigationID . ');';
        $class .= "\n\t" . '/** @var array Collection of additional fields identifiers */';
        $class .= "\n\t" . 'protected static $fieldIDs = array(' . "\n\t\t". implode(','."\n\t\t", $fieldIDs) . "\n\t".');';
        $class .= "\n" . '}';

        // Replace tabs with spaces
        return $class;
    }

    /** @return string Entity state hash */
    public function entityHash()
    {
        // Получим информацию о всех таблицах из БД
        return md5(serialize($this->database->fetch(
            'SELECT `TABLES`.`TABLE_NAME` as `TABLE_NAME`
              FROM `information_schema`.`TABLES` as `TABLES`
              WHERE `TABLES`.`TABLE_SCHEMA`="' . $this->database->database() . '";'
        )));
    }

    /** @return array Get collection of navigation objects */
    protected function entityNavigations($type = 0)
    {
        return $this->database->fetch('
        SELECT * FROM `structure`
        WHERE `Active` = "1" AND `Type` = "'.$type.'"'
        );
    }

    /** @return array Collection of navigation additional fields */
    protected function navigationFields($navigationID)
    {
        $return = array();
        // TODO: Optimize queries make one single query with only needed data
        foreach ($this->database->fetch('SELECT * FROM `structurefield` WHERE `StructureID` = "' . $navigationID . '" AND `Active` = "1"') as $fieldStructureRow) {
            foreach ($this->database->fetch('SELECT * FROM `field` WHERE `FieldID` = "' . $fieldStructureRow['FieldID'] . '"') as $fieldRow) {
                $return[$fieldRow['FieldID']] = $fieldRow;
            }
        }

        return $return;
    }

    /** @return string Generate entity classes */
    public function createEntityClasses()
    {
        $classes = "\n" . 'namespace ' . __NAMESPACE__ . ';';
        $classes .= "\n";
        $classes .= "\n" . 'use \samsoncms\api\Field;';
        $classes .= "\n" . 'use \samsoncms\api\query\Generic;';
        // Iterate all structures
        foreach ($this-> entityNavigations() as $structureRow) {
            $navigationFields = $this->navigationFields($structureRow['StructureID']);
            $classes .= $this->createEntityClass($structureRow);
            $classes .= $this->createQueryClass(
                $structureRow['StructureID'],
                $structureRow['Name'],
                $this->entityName($structureRow['Name'].'Query'),
                $navigationFields
            );
        }

        // Make correct code formatting
        return str_replace("\t", '    ', $classes);
    }

    /**
     * Generator constructor.
     * @param DatabaseInterface $database Database instance
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }
}