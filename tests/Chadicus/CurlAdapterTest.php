<?php
namespace Chadicus;

/**
 * Unit tests for \Chadicus\CurlAdapter class.
 *
 * @coversDefaultClass \Chadicus\CurlAdapter
 */
final class CurlAdapterTest extends \PHPUnit_Framework_TestCase
{
    public static $extensionLoaded = null;

    /**
     * Sets up each test.
     *
     * @return void
     */
    protected function setUp()
    {
        self::$extensionLoaded = null;
    }

    /**
     * Verify basic behavior of send.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     *
     * @return void
     */
    public function send()
    {
        $response = (new CurlAdapter())->send(new Request('http://httpbin.org/get', 'get', [], ['foo' => 'bar']));
        $this->assertSame(200, $response->getHttpCode());
        $headers = $response->getHeaders();
        unset($headers['Date']);
        $this->assertSame(
            [
                'Response Code' => 200,
                'Response Status' => 'OK',
                'Access-Control-Allow-Origin' => '*',
                'Content-Type' => 'application/json',
                'Server' => 'gunicorn/0.17.4',
                'Content-Length' => '234',
                'Connection' => 'keep-alive',
            ],
            $headers
        );

        $body = $response->getBody();
        unset($body['headers']['X-Request-Id']);
        $this->assertEquals(
            [
                'headers' => [
                    'Host' => 'httpbin.org',
                    'Connection' => 'close',
                    'Accept' => '*/*',
                ],
                'url' => 'http://httpbin.org/get',
                'args' => [],
                'origin' => '204.154.44.36',
            ],
            $body
        );
    }

    /**
     * Verify Exception is thrown when $method is not valid.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     * @expectedException \Exception
     * @expectedExceptionMessage Unsupported method 'foo' given
     *
     * @return void
     */
    public function sendWithInvalidMethod()
    {
        (new CurlAdapter())->send(new Request('not under test', 'foo', [], []));
    }

    /**
     * Verify RuntimeException is thrown when curl extension is not loaded
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @expectedException \RuntimeException
     * @expectedExceptionMessage cURL extension must be enabled
     *
     * @return void
     */
    public function sendCurlNotLoaded()
    {
        self::$extensionLoaded = function ($name) {
            return false;
        };

        (new CurlAdapter())->send(new Request('not under test', 'foo', [], []));
    }
}

/**
 * Custom override of \extension_loaded
 *
 * @param string $name The extension name.
 *
 * @return boolean
 */
function extension_loaded($name)
{
    if (CurlAdapterTest::$extensionLoaded === null) {
        return \extension_loaded($name);
    }

    return call_user_func_array(CurlAdapterTest::$extensionLoaded, array($name));
}
