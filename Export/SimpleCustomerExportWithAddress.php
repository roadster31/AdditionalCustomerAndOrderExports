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

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\ImportExport\Export\AbstractExport;
use Thelia\Model\CustomerQuery;
use Thelia\Model\Lang;
use Thelia\Model\Map\AddressTableMap;
use Thelia\Model\Map\CountryI18nTableMap;
use Thelia\Model\Map\CountryTableMap;
use Thelia\Model\Map\CustomerTableMap;
use Thelia\Model\Map\CustomerTitleI18nTableMap;
use Thelia\Model\Map\NewsletterTableMap;
use Thelia\Tools\I18n;

class SimpleCustomerExportWithAddress extends AbstractExport
{
    const FILE_NAME = 'customers-with-address';

    protected $orderAndAliases = [
        CustomerTableMap::REF => "ref",
        CustomerTableMap::LASTNAME => "last_name",
        CustomerTableMap::FIRSTNAME => "first_name",
        CustomerTableMap::EMAIL => "email",
        CustomerTableMap::DISCOUNT => "discount",
        CustomerTableMap::CREATED_AT => "sign_up_date",
        "title_TITLE" => "title",
        "address_TITLE" => "address_title",
        "address_LABEL" => "label",
        "address_IS_DEFAULT" => "is_default_address",
        "address_COMPANY" => "company",
        "address_ADDRESS1" => "address1",
        "address_ADDRESS2" => "address2",
        "address_ADDRESS3" => "address3",
        "address_ZIPCODE" => "zipcode",
        "address_CITY" => "city",
        "address_COUNTRY" => "country",
        "address_PHONE" => "phone",
        "address_CELLPHONE" => "cellphone",
        "address_FIRST_NAME" => "address_first_name",
        "address_LAST_NAME" => "address_last_name"
    ];

    public function getData()
    {
        $locale = Lang::getDefaultLanguage()->getLocale();
        /**
         * This first query get each customer info and addresses.
         */
        $newsletterJoin = new Join(CustomerTableMap::EMAIL, NewsletterTableMap::EMAIL, Criteria::LEFT_JOIN);

        $query = CustomerQuery::create()
            ->useCustomerTitleQuery("customer_title_")
                ->useCustomerTitleI18nQuery("customer_title_i18n_")
                    ->addAsColumn("title_TITLE", "customer_title_i18n_.SHORT")
                ->endUse()
            ->endUse()

            ->useAddressQuery()
                ->filterByIsDefault(true)

                ->useCountryQuery()
                    ->useCountryI18nQuery()
                        ->addAsColumn("address_COUNTRY", CountryI18nTableMap::TITLE)
                    ->endUse()
                ->endUse()

                ->useCustomerTitleQuery("address_title")
                    ->useCustomerTitleI18nQuery("address_title_i18n")
                        ->addAsColumn("address_TITLE", "address_title_i18n.SHORT")
                    ->endUse()
                ->endUse()

                ->addAsColumn("address_LABEL", AddressTableMap::LABEL)
                ->addAsColumn("address_FIRST_NAME", AddressTableMap::FIRSTNAME)
                ->addAsColumn("address_LAST_NAME", AddressTableMap::LASTNAME)
                ->addAsColumn("address_COMPANY", AddressTableMap::COMPANY)
                ->addAsColumn("address_ADDRESS1", AddressTableMap::ADDRESS1)
                ->addAsColumn("address_ADDRESS2", AddressTableMap::ADDRESS2)
                ->addAsColumn("address_ADDRESS3", AddressTableMap::ADDRESS3)
                ->addAsColumn("address_ZIPCODE", AddressTableMap::ZIPCODE)
                ->addAsColumn("address_CITY", AddressTableMap::CITY)
                ->addAsColumn("address_PHONE", AddressTableMap::PHONE)
                ->addAsColumn("address_CELLPHONE", AddressTableMap::CELLPHONE)
                ->addAsColumn("address_IS_DEFAULT", AddressTableMap::IS_DEFAULT)
            ->endUse()
            ->select([
                CustomerTableMap::REF,
                CustomerTableMap::LASTNAME,
                CustomerTableMap::FIRSTNAME,
                CustomerTableMap::EMAIL,
                CustomerTableMap::DISCOUNT,
                CustomerTableMap::CREATED_AT,
                "title_TITLE",
                "address_TITLE",
                "address_LABEL",
                "address_COMPANY",
                "address_FIRST_NAME",
                "address_LAST_NAME",
                "address_ADDRESS1",
                "address_ADDRESS2",
                "address_ADDRESS3",
                "address_ZIPCODE",
                "address_CITY",
                "address_COUNTRY",
                "address_PHONE",
                "address_CELLPHONE"
            ])
            ->orderById()
        ;

        I18n::addI18nCondition(
            $query,
            CountryI18nTableMap::TABLE_NAME,
            CountryTableMap::ID,
            CountryI18nTableMap::ID,
            CountryI18nTableMap::LOCALE,
            $locale
        );

        I18n::addI18nCondition(
            $query,
            CustomerTitleI18nTableMap::TABLE_NAME,
            "`customer_title_`.ID",
            "`customer_title_i18n_`.ID",
            "`customer_title_i18n_`.LOCALE",
            $locale
        );

        I18n::addI18nCondition(
            $query,
            CustomerTitleI18nTableMap::TABLE_NAME,
            "`address_title`.ID",
            "`address_title_i18n`.ID",
            "`address_title_i18n`.LOCALE",
            $locale
        );

        /** @var CustomerQuery $query */
        return $query
            ->find()
            ->toArray()
        ;
    }
}
