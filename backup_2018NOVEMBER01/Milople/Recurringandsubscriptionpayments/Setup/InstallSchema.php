<?php
/**
*
* Do not edit or add to this file if you wish to upgrade the module to newer
* versions in the future. If you wish to customize the module for your
* needs please contact us to https://www.milople.com/contact-us.html
*
* @category    Ecommerce
* @package     Milople_Recurringandsubscriptionpayments
* @copyright   Copyright (c) 2017 Milople Technologies Pvt. Ltd. All Rights Reserved.
* @url         https://www.milople.com/magento2-extensions/ecurring-and-subscription-payments-m2.html
*
**/
namespace Milople\Recurringandsubscriptionpayments\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class InstallSchema implements InstallSchemaInterface
{
		public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
          $table = $installer->getConnection()->newTable(
            $installer->getTable('recurringandsubscriptionpayments_plans')
        )->addColumn(
            'plan_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true,'auto_increment' => true],
            'plan_id'
        )->addColumn(
            'plan_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'plan name'
        )
        ->addColumn(
            'is_normal',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Entity Id'
        )
        ->addColumn(
            'start_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'start date'
        )
        ->addColumn(
            'plan_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 0],
            'plan_status'
        )
        ->addColumn(
            'creation_time',
             \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'creation time'
        )
        ->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'update time'
        );
        $installer->getConnection()->createTable($table);
        
		  $table = $installer->getConnection()->newTable(
            $installer->getTable('recurringandsubscriptionpayments_terms')
        )        
        ->addColumn(
            'terms_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
            ['nullable' => false, 'primary' => true,'auto_increment' => true],
            'term id'
        )
        ->addColumn(
            'label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'lable'
        )
        ->addColumn(
            'repeateach',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            5,
            ['nullable' => false],
            'repeat each'
        )
        ->addColumn(
            'termsper',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default'=> 'day'],
            'terms per'
        )
		  ->addColumn(
            'payment_before_days',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            2,
            [],
            'payment before days'
        )
			->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => false,'default' => 0],
            'price'
        )
        ->addColumn(
            'price_calculation_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            [],
            'price cal. type'
        )
        ->addColumn(
            'noofterms',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false,'default' => 0],
            'no of terms'
        )
        ->addColumn(
            'sortorder',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false,'default' => 0],
            'sort order'
        )
        ->addColumn(
            'plan_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'plan id'
        );
        $installer->getConnection()->createTable($table);
        
		  $table = $installer->getConnection()->newTable(
            $installer->getTable('recurringandsubscriptionpayments_subscription')
        )        
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false, 'primary' => true, 'auto_increment' => true],
            'id'
        )
		->addColumn(
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             64,
            ['nullable' => false],
            'Payment methods reference id'
        )
        ->addColumn(
            'date_start',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
             64,
            ['nullable' => false],
            'subscription start date'
        )
        ->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false],
            'customer id'
        )
		->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             255,
            ['nullable' => false],
            'customer name'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
             64,
            ['nullable' => false,'default' => 1],
            'status'
        )
        ->addColumn(
            'term_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false],
            'term type'
        )
        ->addColumn(
            'primary_quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false],
            'primary quote id'
        )
        ->addColumn(
            'parent_order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             40,
            ['nullable' => false],
            'parent order id'
        )
        ->addColumn(
            'last_order_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
             11,
            ['nullable' => false],
            'last order amount'
        )
        ->addColumn(
            'last_order_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             5,
            ['nullable' => false],
            'last order currency code'
        )
        ->addColumn(
            'last_order_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             64,
            ['nullable' => false],
            'last order status'
        )
        ->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false],
            'store id'
        )
        ->addColumn(
            'discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             11,
            ['nullable' => false],
            'discount amt'
        )
        ->addColumn(
            'apply_discount_on',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false,'default' => 0 ],
            'apply discount on'
        )
        
        ->addColumn(
            'products_text',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             255,
            ['nullable' => false],
            'products name'
        )
        ->addColumn(
            'date_expire',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
             null,
            ['default' => null],
            'date expiry'
        )
        ->addColumn(
            'has_shipping',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
             null,
            ['nullable' => false, 'default' => '0'],
           'has shipping'
        )
        ->addColumn(
            'next_payment_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
             null,
            [],
            'next payment date'
        )
        ->addColumn(
            'expirymail',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             1,
            ['nullable' => false, 'default' => '0'],
            'expiry mail'
        )
        ->addIndex(
            $installer->getIdxName('customer_id', ['customer_id','date_start','status']),
            ['customer_id','date_start','status']
        )
        ->addIndex(
            $installer->getIdxName('period_type', ['term_type']),
            ['term_type']
        )
        ->addIndex(
            $installer->getIdxName('primary_quote_id', ['primary_quote_id']),
            ['primary_quote_id']
        )
        ->addIndex(
            $installer->getIdxName('store_id', ['store_id']),
            ['store_id']
        )
        ->addIndex(
            $installer->getIdxName('subscription_id', ['id']),
            ['id']
        )
        ->addIndex(
            $installer->getIdxName('last_order_amount', ['last_order_amount','last_order_status']),
            ['last_order_amount','last_order_status']
        );
		  $installer->getConnection()->createTable($table);
        
		  $table = $installer->getConnection()->newTable(
            $installer->getTable('recurringandsubscriptionpayments_subscription_item')
        )        
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false, 'primary' => true,'auto_increment' => true],
            'id'
        )
        ->addColumn(
            'subscription_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false],
            'subscription id'
        )
        ->addColumn(
            'primary_order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             20,
            ['nullable' => false],
            'primary order id'
        )
        ->addColumn(
            'primary_order_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             50,
            ['nullable' => false],
            'primary order item id'
        )
        ->addIndex(
            $installer->getIdxName('subscription_id', ['subscription_id','primary_order_id','primary_order_item_id']),
            ['subscription_id','primary_order_id','primary_order_item_id']
        );
        $installer->getConnection()->createTable($table);
        
		  $table = $installer->getConnection()->newTable(
            $installer->getTable('recurringandsubscriptionpayments_sequence')
        )        
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false, 'primary' => true,'auto_increment' => true],
            'subscription id'
        )
     ->addColumn(
            'subscription_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false],
            'subscription id'
        )
     
        ->addColumn(
            'date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
             null,
             ['nullable' => false],
            'date'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             11,
            ['nullable' => false, 'default' => 'pending'],
            'status'
        )
        ->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             255,
            ['nullable' => false],
            'order id'
        )
        ->addColumn(
            'mailsent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
             null,
            ['nullable' => false,'default' => 0],
            'mail sent'
        )
		->addColumn(
            'transaction_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
            ['nullable' => true],
            'sequence transaction status'
        )
        ->addIndex(
            $installer->getIdxName('subscription_id', ['id','date']),
            ['id','date']
        )
        ->addIndex(
            $installer->getIdxName('status', ['status']),
            ['status']
        )
        ->addIndex(
            $installer->getIdxName('order_id', ['order_id']),
            ['order_id']
        );
        $installer->getConnection()->createTable($table);
        
		  $table = $installer->getConnection()->newTable(
            $installer->getTable('recurringandsubscriptionpayments_plans_product')
        )        
        ->addColumn(
            'plan_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             11,
            ['nullable' => false],
            'plan id'
        )
        ->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             10,
            ['nullable' => false,'primary' => true],
            'product id'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
