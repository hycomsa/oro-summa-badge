<?php

namespace Summa\Bundle\BadgeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\ProductBundle\Migrations\Schema\OroProductBundleInstaller;
use Summa\Bundle\BadgeBundle\Migrations\Data\ORM\LoadProductBadgePositions;

class BadgeBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    const PRODUCT_TABLE_NAME = 'oro_product';
    const BADGE_TABLE_NAME   = 'summa_badge';

    /** @var ExtendExtension */
    private $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createBadgeTable($schema);
        $this->createBadgeScheduleTable($schema);

        /** Foreign keys generation **/
        $this->addBadgeScheduleForeignKeys($schema);

        /** Foreign Relations **/
        $this->addBadgePositionEnumField($schema);
        $this->addBadgeRelationship($schema);
    }

    /**
     * Create badge table
     *
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function createBadgeTable(Schema $schema)
    {
        $table = $schema->createTable('summa_badge');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name','string', ['length' => 100]);
        $table->addColumn('active', 'boolean', ['notnull' => true, 'default' => true]);
        $table->addColumn('style', 'text', ['notnull' => false]);
        $table->addColumn('image_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_assignment_rule', 'text', ['notnull' => false]);
        $table->addColumn('apply_for_n_days', 'integer', ['notnull' => false]);
        $table->addColumn('contain_schedule', 'boolean', []);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addIndex(['image_id'], 'IDX_A7D0B43E3DA5256D', []);

        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName('oro_attachment_file')
        );

        $badgeTable = $schema->getTable('summa_badge');
        $targetTable = $schema->getTable('oro_attachment_file');

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $badgeTable,
            $associationName,
            $targetTable,
            'id'
        );

        $table->setPrimaryKey(['id']);
    }

    /**
     * Create summa_badge_schedule table
     *
     * @param Schema $schema
     */
    protected function createBadgeScheduleTable(Schema $schema)
    {
        $table = $schema->createTable('summa_badge_schedule');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('badge_id', 'integer', ['notnull' => false]);
        $table->addColumn('active_at', 'datetime', ['notnull' => false]);
        $table->addColumn('deactivate_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
//        $table->addIndex(['badge_id'], 'IDX_C706756E5688DED7', []);
    }

    /**
     * @param Schema $schema
     */
    protected function addBadgePositionEnumField(Schema $schema)
    {
        $badgePositionEnumTable = $this->extendExtension->addEnumField(
            $schema,
            'summa_badge',
            'position',
            'summa_badge_position',
            false,
            false,
            ['dataaudit' => ['auditable' => true]]
        );

        $badgePositionOptions = new OroOptions();
        $badgePositionOptions->set(
            'enum',
            'immutable_codes',
            LoadProductBadgePositions::getDataKeys()
        );

        $badgePositionEnumTable->addOption(OroOptions::KEY, $badgePositionOptions);
    }

    /**
     * Add summa_badge_schedule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addBadgeScheduleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('summa_badge_schedule');
        $table->addForeignKeyConstraint(
            $schema->getTable(self::BADGE_TABLE_NAME),
            ['badge_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addBadgeRelationship(Schema $schema)
    {
        $productTable   = OroProductBundleInstaller::PRODUCT_TABLE_NAME;
        $badgeTable     = self::BADGE_TABLE_NAME;

        $this->extendExtension->addManyToManyRelation(
            $schema,
            $productTable,
            'badges',
            $badgeTable,
            ['id', 'name'],
            ['id', 'name'],
            ['id', 'name'],
            [
                'entity' => ['label' => 'summa.badge.entity_plural_label'],
                'extend' => [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                    'cascade' => ['all'],
                ],
                'form' => [
                    'is_enabled' => true
                ],
                'view' => ['is_displayable' => true],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => false]
            ]
        );
    }

    /**
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
