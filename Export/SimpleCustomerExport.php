<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AdditionalCustomerAndOrderExports\Export;

use Thelia\ImportExport\Export\AbstractExport;
use Thelia\Model\CustomerQuery;
use Thelia\Model\Lang;
use Thelia\Model\Map\CustomerTableMap;
use Thelia\Model\Map\CustomerTitleI18nTableMap;
use Thelia\Tools\I18n;

class SimpleCustomerExport extends AbstractExport
{
    const FILE_NAME = 'customers';

    protected $orderAndAliases = [
        CustomerTableMap::REF => "ref",
        "title_TITLE" => "title",
        CustomerTableMap::LASTNAME => "last_name",
        CustomerTableMap::FIRSTNAME => "first_name",
        CustomerTableMap::EMAIL => "email",
        CustomerTableMap::DISCOUNT => "discount",
        CustomerTableMap::CREATED_AT => "sign_up_date",
    ];

    /**
     * @param  Lang                                            $lang
     * @return array|\Propel\Runtime\ActiveQuery\ModelCriteria
     *
     * The tax engine of Thelia is in PHP, so we can't compute orders for each customers
     * directly in SQL, we need two SQL queries, and some computing to get the last order amount and total amount.
     */
    public function getData()
    {
        $locale = Lang::getDefaultLanguage()->getLocale();

        $query = CustomerQuery::create()
            ->useCustomerTitleQuery("customer_title_")
                ->useCustomerTitleI18nQuery("customer_title_i18n_")
                    ->addAsColumn("title_TITLE", "customer_title_i18n_.SHORT")
                ->endUse()
            ->endUse()
            ->select([
                CustomerTableMap::REF,
                CustomerTableMap::LASTNAME,
                CustomerTableMap::FIRSTNAME,
                CustomerTableMap::EMAIL,
                CustomerTableMap::DISCOUNT,
                CustomerTableMap::CREATED_AT,
                "title_TITLE"
            ])
            ->orderById()
        ;

        I18n::addI18nCondition(
            $query,
            CustomerTitleI18nTableMap::TABLE_NAME,
            "`customer_title_`.ID",
            "`customer_title_i18n_`.ID",
            "`customer_title_i18n_`.LOCALE",
            $locale
        );

        /** @var CustomerQuery $query */
        return $query->find()->toArray();
    }
}
