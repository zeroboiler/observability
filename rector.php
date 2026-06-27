{
    "$schema": "https://getrector.com/schema.json",
    "importClasses": false,
    "importNames": false,
    "importDocBlockNames": false,
    "indent": "    ",
    "lineBreak": "\n",
    "paths": [
        "src"
    ],
    "phpstan": {
        "includes": [
            "vendor/pestphp/pest/phpstan-baseline.neon",
            "phpstan-baseline.neon"
        ]
    },
    "sets": [
        "@level1/up-to-php85",
        "@laravel85"
    ],
    "rules": [
        "\Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector",
        "\Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector",
        "\Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector",
        "\Rector\CodingStyle\Rector\Encapsed\EncapsedStringToSprintfRector",
        "\Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector",
        "\Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector",
        "\Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector",
        "\Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector",
        "\Rector\Php85\Rector\Class_\ReadOnlyPropertyRector",
        "\Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector",
        "\Rector\Privatization\Rector\Class_\ChangeGlobalVariableToStaticVariableRector",
        "\Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector",
        "\Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector",
        "\Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector",
        "\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector",
        "\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector",
        "\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector",
        "\Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector",
        "\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_85",
        "\Rector\Set\ValueObject\SetList::CODE_QUALITY",
        "\Rector\Set\ValueObject\SetList::CODING_STYLE",
        "\Rector\Set\ValueObject\SetList::DEAD_CODE",
        "\Rector\Set\ValueObject\SetList::EARLY_RETURN",
        "\Rector\Set\ValueObject\SetList::PRIVATIZATION",
        "\Rector\Set\ValueObject\SetList::TYPE_DECLARATION",
        "\Rector\Laravel\Set\ValueObject\SetList::LARAVEL_130"
    ],
    "skip": [
        "tests"
    ]
}