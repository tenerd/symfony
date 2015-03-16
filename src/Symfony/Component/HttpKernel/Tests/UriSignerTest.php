<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Tests;

use Symfony\Component\HttpKernel\UriSigner;

class UriSignerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string backup original value for ini setting */
    protected $argSeparatorOutputBackup;

    protected function setUp()
    {
        parent::setUp();
        $this->argSeparatorOutputBackup = ini_get('arg_separator.output');
    }

    protected function tearDown()
    {
        ini_set('arg_separator.output', $this->argSeparatorOutputBackup);
        parent::tearDown();
    }

    public function testSign()
    {
        $signer = new UriSigner('foobar');

        $this->assertContains('?_hash=', $signer->sign('http://example.com/foo'));
        $this->assertContains('&_hash=', $signer->sign('http://example.com/foo?foo=bar'));
    }

    public function testCheck()
    {
        $signer = new UriSigner('foobar');

        $this->assertFalse($signer->check('http://example.com/foo?_hash=foo'));
        $this->assertFalse($signer->check('http://example.com/foo?foo=bar&_hash=foo'));
        $this->assertFalse($signer->check('http://example.com/foo?foo=bar&_hash=foo&bar=foo'));

        $this->assertTrue($signer->check($signer->sign('http://example.com/foo')));
        $this->assertTrue($signer->check($signer->sign('http://example.com/foo?foo=bar')));

        $this->assertTrue($signer->sign('http://example.com/foo?foo=bar&bar=foo') === $signer->sign('http://example.com/foo?bar=foo&foo=bar'));
    }

    public function testCheckWithDifferentArgSeparator()
    {
        $signer = new UriSigner('foobar');

        ini_set('arg_separator.output', '&amp;');
        $this->assertTrue($signer->check($signer->sign('http://example.com/foo')));
        $this->assertTrue($signer->check($signer->sign('http://example.com/foo?foo=bar&baz=bay')));

        $this->assertTrue($signer->sign('http://example.com/foo?foo=bar&bar=foo') === $signer->sign('http://example.com/foo?bar=foo&foo=bar'));
    }
}
