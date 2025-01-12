<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */


namespace Lunetics\LocaleBundle\Tests\Validator;

use Lunetics\LocaleBundle\Validator\Locale;
use Lunetics\LocaleBundle\Validator\LocaleValidator;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleValidatorTest extends BaseMetaValidator
{

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLanguageIsValid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleValidator($intlExtension)->validate('de', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('deu', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('eng', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr', $constraint);

        // Filipino removed from known ISO-639-2 locales in Symfony 2.3+
        // @see https://github.com/symfony/symfony/issues/12583
        //$this->getLocaleValidator($intlExtension)->validate('fil', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithRegionIsValid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleValidator($intlExtension)->validate('de_DE', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en_US', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en_PH', $constraint);  // Filipino English
        $this->getLocaleValidator($intlExtension)->validate('fr_FR', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_CH', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_US', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithIso639_2Valid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');

        // Filipino removed from known ISO-639-2 locales in Symfony 2.3+
        // @see https://github.com/symfony/symfony/issues/12583
        //$this->getLocaleValidator($intlExtension)->validate('fil_PH', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithScriptValid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleValidator($intlExtension)->validate('zh_Hant_HK', $constraint);
    }

    /**
     * Test if locale is invalid
     *
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsInvalid($intlExtension)
    {
        $constraint = new Locale();
        // Need to distinguish, since the intl fallback allows every combination of languages, script and regions
        $this->context->expects($this->exactly(3))
                      ->method('addViolation');

        $this->getLocaleValidator($intlExtension)->validate('foobar', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('de_FR', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_US', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('foo_bar', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('foo_bar_baz', $constraint);

    }

    public function testValidateThrowsUnexpectedTypeException()
    {
        self::expectException('\Symfony\Component\Validator\Exception\UnexpectedTypeException');
        $validator = new LocaleValidator();
        $validator->validate(array(), $this->getMockConstraint());
    }

    public function testValidateEmptyLocale()
    {
        $validator = new LocaleValidator();

        $validator->validate(null, $this->getMockConstraint());
        $validator->validate('', $this->getMockConstraint());
    }

    protected function getMockConstraint()
    {
        return $this->createMock(Constraint::class);
    }
}
