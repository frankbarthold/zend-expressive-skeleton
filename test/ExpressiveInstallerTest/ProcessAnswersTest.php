<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use Prophecy\Argument;

class ProcessAnswersTest extends InstallerTestCase
{
    protected $teardownFiles = [
        '/config/container.php',
    ];

    public function testInvalidAnswer()
    {
        $io = $this->prophesize('Composer\IO\IOInterface');
        $io->write()->shouldNotBeCalled();

        $config       = $this->getConfig();
        $question     = $config['questions']['container'];
        $answer       = 'foobar';
        $copyFilesKey = 'minimal-files';
        $result       = OptionalPackages::processAnswer($io->reveal(), $question, $answer, $copyFilesKey);

        $this->assertFalse($result);
        $this->assertFileNotExists($this->getProjectRoot() . '/config/container.php');
    }

    public function testAnsweredWithN()
    {
        $io = $this->prophesize('Composer\IO\IOInterface');
        $io->write()->shouldNotBeCalled();

        $config       = $this->getConfig();
        $question     = $config['questions']['container'];
        $answer       = 'n';
        $copyFilesKey = 'minimal-files';
        $result       = OptionalPackages::processAnswer($io->reveal(), $question, $answer, $copyFilesKey);

        $this->assertFalse($result);
        $this->assertFileNotExists($this->getProjectRoot() . '/config/container.php');
    }

    public function testAnsweredWithInvalidOption()
    {
        $io = $this->prophesize('Composer\IO\IOInterface');
        $io->write()->shouldNotBeCalled();

        $config       = $this->getConfig();
        $question     = $config['questions']['container'];
        $answer       = 10;
        $copyFilesKey = 'minimal-files';
        $result       = OptionalPackages::processAnswer($io->reveal(), $question, $answer, $copyFilesKey);

        $this->assertFalse($result);
        $this->assertFileNotExists($this->getProjectRoot() . '/config/container.php');
    }

    public function testAnsweredWithValidOption()
    {
        // Prepare the installer
        OptionalPackages::removeDevDependencies();

        $io = $this->prophesize('Composer\IO\IOInterface');

        $io->write(Argument::containingString('Adding package <info>aura/di</info>'))->shouldBeCalled();
        $io->write(Argument::containingString('Copying <info>/config/container.php</info>'))->shouldBeCalled();

        $config       = $this->getConfig();
        $question     = $config['questions']['container'];
        $answer       = 1;
        $copyFilesKey = 'minimal-files';
        $result       = OptionalPackages::processAnswer($io->reveal(), $question, $answer, $copyFilesKey);

        $this->assertTrue($result);
        $this->assertFileExists($this->getProjectRoot() . '/config/container.php');
        $this->assertComposerHasPackages(['aura/di']);
    }

    public function testAnsweredWithPackage()
    {
        // Prepare the installer
        OptionalPackages::removeDevDependencies();

        $io = $this->prophesize('Composer\IO\IOInterface');

        $io->write(Argument::containingString('Adding package <info>league/container</info>'))->shouldBeCalled();
        $io->write(Argument::containingString('<warning>You need to edit public/index.php'))->shouldBeCalled();

        $config       = $this->getConfig();
        $question     = $config['questions']['container'];
        $answer       = 'league/container:2.2.0';
        $copyFilesKey = 'minimal-files';
        $result       = OptionalPackages::processAnswer($io->reveal(), $question, $answer, $copyFilesKey);

        $this->assertTrue($result);
        $this->assertFileNotExists($this->getProjectRoot() . '/config/container.php');
        $this->assertComposerHasPackages(['league/container']);
    }
}
