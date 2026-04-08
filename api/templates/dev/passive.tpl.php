<?= "<?php\n" ?>

namespace <?= $class_data->getNamespace(); ?>;

<?= $class_data->getUseStatements(); ?>

<?= $class_data->getClassDeclaration(); ?> <?= $interfaces ?>
{
    <?= $traits ?>

    private const HEALTH_POINTS = 1;
    private const ATTACK = 1;

    public function getId(): string
    {
        return '<?= $id; ?>';
    }
}
