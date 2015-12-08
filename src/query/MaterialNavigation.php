<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsoncms\api\CMS;
use samsoncms\api\Navigation;

class MaterialNavigation extends Base
{
    /** MaterialQuery constructor */
    public function __construct()
    {
        parent::__construct(
            new dbQuery(),
            '\samsoncms\api\Material',
            Navigation::$_primary,
            CMS::MATERIAL_NAVIGATION_RELATION_ENTITY
        );
    }
}
