<?= "<?php\n" ?>

namespace <?= $class_data->getNamespace(); ?>;

<?= $class_data->getUseStatements(); ?>

<?= $class_data->getClassDeclaration(); ?>
{
    private const HEALTH_POINTS = 1;
    private const ATTACK = 1;

    public function getId(): string
    {
        return '<?= $id; ?>';
    }

    public function getAttack(): int
    {
        return $this->getValue(self::ATTACK, true);
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS;
    }
}
