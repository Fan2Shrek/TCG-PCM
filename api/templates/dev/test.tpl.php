<?= "<?php\n" ?>

namespace <?= $class_data->getNamespace(); ?>;

<?= $class_data->getUseStatements(); ?>

<?= $class_data->getClassDeclaration(); ?>
{
    public function getCardFQCN(): string
    {
        return <?= $card_name ?>::class;
    }

    public function testCard()
    {
        self::markTestIncomplete( \sprintf('TODO: Implement %s method.', __METHOD__));
    }
}
