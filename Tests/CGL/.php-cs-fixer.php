<?php

declare(strict_types=1);

use KonradMichalik\PhpCsFixerPreset\Config;
use KonradMichalik\PhpCsFixerPreset\Package\{Author, CopyrightRange, Type};
use KonradMichalik\PhpCsFixerPreset\Rules\Header;
use KonradMichalik\PhpCsFixerPreset\Rules\Set\Set;
use KonradMichalik\PhpDocBlockHeaderFixer\Generators\DocBlockHeader;
use KonradMichalik\PhpDocBlockHeaderFixer\Rules\DocBlockHeaderFixer;
use Symfony\Component\Finder\Finder;

$rootPath = dirname(__DIR__, 2);

return Config::create()
    ->registerCustomFixers([
        new DocBlockHeaderFixer(),
    ])
    ->withRule(
        Header::create(
            'repeatable_form_elements',
            Type::TYPO3Extension,
            Author::create('Ralf Zimmermann', 'r.zimmermann@dreistrom.land'),
            CopyrightRange::from(2018),
        ),
    )
    ->withRule(
        Set::fromArray(
            DocBlockHeader::fromComposer()->__toArray(),
        ),
    )
    ->withFinder(
        static fn (Finder $finder) => $finder
            ->in($rootPath)
            ->notPath(['ext_emconf.php']),
    )
;
