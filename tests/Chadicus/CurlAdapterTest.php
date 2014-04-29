<?php
namespace Chadicus;

/**
 * Unit tests for \Chadicus\CurlAdapter class.
 *
 * @coversDefaultClass \Chadicus\CurlAdapter
 */
final class CurlAdapterTest extends \PHPUnit_Framework_TestCase
{
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
}
