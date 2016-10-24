<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use Composer\Package\BasePackage;
use ExpressiveInstaller\OptionalPackages;
use Prophecy\Argument;

class AddPackageTest extends InstallerTestCase
{
    /**
     * @dataProvider packageProvider
     */
    public function testAddPackage($packageName, $packageVersion, $expectedStability)
    {
        // Prepare the installer
        OptionalPackages::removeDevDependencies();

        $io = $this->prophesize('Composer\IO\IOInterface');
        $io->write(Argument::containingString('Adding package'))->shouldBeCalled();

        OptionalPackages::addPackage($io->reveal(), $packageName, $packageVersion);

        $this->assertComposerHasPackages(['zendframework/zend-stdlib']);
        $stabilityFlags = $this->getStabilityFlags();

        // Stability flags are only set for non-stable packages
        if ($expectedStability) {
            $this->assertArrayHasKey($packageName, $stabilityFlags);
            $this->assertEquals($expectedStability, $stabilityFlags[$packageName]);
        }
    }

    public function packageProvider()
    {
        // $packageName, $packageVersion, $expectedStability
        return [
            'dev'    => ['zendframework/zend-stdlib', '1.0.0-dev', BasePackage::STABILITY_DEV],
            'alpha'  => ['zendframework/zend-stdlib', '1.0.0-alpha2', BasePackage::STABILITY_ALPHA],
            'beta'   => ['zendframework/zend-stdlib', '1.0.0-beta.3', BasePackage::STABILITY_BETA],
            'RC'     => ['zendframework/zend-stdlib', '1.0.0-RC4', BasePackage::STABILITY_RC],
            'stable' => ['zendframework/zend-stdlib', '1.0.0', null],
        ];
    }
}
