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
     * Sets up each test.
     *
     * @return void
     */
    protected function setUp()
    {
        FunctionRegistry::reset(array('core', 'curl'));
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
        FunctionRegistry::set(
            'curl_init',
            function () {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_setopt_array',
            function ($curl, array $options) {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_exec',
            function ($curl) {
                return "HTTP/1.1 200 OK\r\nContent-Length: 13\r\nContent-Type: application/json\r\n\n{\"foo\":\"bar\"}";
            }
        );

        FunctionRegistry::set(
            'curl_error',
            function ($curl) {
                return '';
            }
        );

        FunctionRegistry::set(
            'curl_getinfo',
            function ($curl, $option) {
                if ($option === CURLINFO_HEADER_SIZE) {
                    return 69;
                }

                if ($option === CURLINFO_HTTP_CODE) {
                    return 200;
                }
            }
        );

        $response = (new CurlAdapter())->send(new Request('not under test', 'get', [], ['foo' => 'bar']));
        $this->assertSame(200, $response->getHttpCode());
        $this->assertSame(
            [
                'Response Code' => 200,
                'Response Status' => 'OK',
                'Content-Length' => '13',
                'Content-Type' => 'application/json',
            ],
            $response->getHeaders()
        );
        $this->assertSame(['foo' => 'bar'], $response->getBody());
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
        FunctionRegistry::set(
            'extension_loaded',
            function ($name) {
                return false;
            }
        );

        (new CurlAdapter())->send(new Request('not under test', 'foo', [], []));
    }

    /**
     * Verify request headers are being sent properly
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     *
     * @return void
     */
    public function sendSetRequestHeaders()
    {
        FunctionRegistry::set(
            'curl_init',
            function () {
                return true;
            }
        );

        $actualHeaders = [];
        FunctionRegistry::set(
            'curl_setopt_array',
            function ($curl, array $options) use (&$actualHeaders) {
                $actualHeaders = $options[CURLOPT_HTTPHEADER];
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_exec',
            function ($curl) {
                return "HTTP/1.1 200 OK\r\nContent-Length: 4\r\nContent-Type: application/json\r\n\n[]";
            }
        );

        FunctionRegistry::set(
            'curl_error',
            function ($curl) {
                return '';
            }
        );

        FunctionRegistry::set(
            'curl_getinfo',
            function ($curl, $option) {
                if ($option === CURLINFO_HEADER_SIZE) {
                    return 69;
                }

                if ($option === CURLINFO_HTTP_CODE) {
                    return 200;
                }
            }
        );
        (new CurlAdapter())->send(new Request('not under test', 'get', ['foo' => 'bar'], []));

        $this->assertSame(['Expect:', 'foo: bar'], $actualHeaders);
    }

    /**
     * Verify Exception is thrown when curl_init fails.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to initialize connection
     *
     * @return void
     */
    public function sendCurlInitFails()
    {
        FunctionRegistry::set(
            'curl_init',
            function () {
                return false;
            }
        );

        (new CurlAdapter())->send(new Request('not under test', 'get', [], []));
    }

    /**
     * Verify Exception is thrown when curl_setopt_array fails.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to prepare connection
     *
     * @return void
     */
    public function sendCurlSetoptArrayFails()
    {
        FunctionRegistry::set(
            'curl_setopt_array',
            function ($curl, array $options) {
                return false;
            }
        );

        (new CurlAdapter())->send(new Request('not under test', 'get', [], []));
    }

    /**
     * Verify Exception is thrown when curl_exec fails.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     * @expectedException \Exception
     * @expectedExceptionMessage the error
     *
     * @return void
     */
    public function sendCurlExecFails()
    {
        FunctionRegistry::set(
            'curl_exec',
            function ($curl) {
                return false;
            }
        );

        FunctionRegistry::set(
            'curl_error',
            function ($curl) {
                return 'the error';
            }
        );

        (new CurlAdapter())->send(new Request('not under test', 'get', [], []));
    }

    /**
     * Verify behavior when curl_getinfo return false for CURLINFO_HEADER_SIZE.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to determine header size
     *
     * @return void
     */
    public function sendCurlGetinfoFailsOnHeaderSize()
    {
        FunctionRegistry::set(
            'curl_init',
            function () {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_setopt_array',
            function ($curl, array $options) {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_exec',
            function ($curl) {
                return "HTTP/1.1 200 OK\r\nContent-Length: 13\r\nContent-Type: application/json\r\n\n{\"foo\":\"bar\"}";
            }
        );

        FunctionRegistry::set(
            'curl_error',
            function ($curl) {
                return '';
            }
        );

        FunctionRegistry::set(
            'curl_getinfo',
            function ($curl, $option) {
                if ($option === CURLINFO_HEADER_SIZE) {
                    return false;
                }

                if ($option === CURLINFO_HTTP_CODE) {
                    return 200;
                }
            }
        );

        (new CurlAdapter())->send(new Request('not under test', 'get', [], []));
    }

    /**
     * Verify behavior when curl_getinfo return false for CURLINFO_HTTP_CODE.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to determine response HTTP code
     *
     * @return void
     */
    public function sendCurlGetinfoFailsOnHttpCode()
    {
        FunctionRegistry::set(
            'curl_init',
            function () {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_setopt_array',
            function ($curl, array $options) {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_exec',
            function ($curl) {
                return "HTTP/1.1 200 OK\r\nContent-Length: 13\r\nContent-Type: application/json\r\n\n{\"foo\":\"bar\"}";
            }
        );

        FunctionRegistry::set(
            'curl_error',
            function ($curl) {
                return '';
            }
        );

        FunctionRegistry::set(
            'curl_getinfo',
            function ($curl, $option) {
                if ($option === CURLINFO_HEADER_SIZE) {
                    return 69;
                }

                if ($option === CURLINFO_HTTP_CODE) {
                    return false;
                }
            }
        );

        (new CurlAdapter())->send(new Request('not under test', 'get', [], []));
    }

    /**
     * Verify behavior when json_last_error returns a value other than JSON_ERROR_NONE.
     *
     * @test
     * @covers ::send
     * @uses \Chadicus\Request
     * @uses \Chadicus\Response
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to parse response: Syntax error
     *
     * @return void
     */
    public function sendInvalidJsonInResult()
    {
        FunctionRegistry::set(
            'curl_init',
            function () {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_setopt_array',
            function ($curl, array $options) {
                return true;
            }
        );

        FunctionRegistry::set(
            'curl_exec',
            function ($curl) {
                // contains syntax error
                return "HTTP/1.1 200 OK\r\nContent-Length: 4\r\nContent-Type: application/json\r\n\n{xx}}";
            }
        );

        FunctionRegistry::set(
            'curl_error',
            function ($curl) {
                return '';
            }
        );

        FunctionRegistry::set(
            'curl_getinfo',
            function ($curl, $option) {
                if ($option === CURLINFO_HEADER_SIZE) {
                    return 69;
                }

                if ($option === CURLINFO_HTTP_CODE) {
                    return 200;
                }
            }
        );

        (new CurlAdapter())->send(new Request('not under test', 'get', [], []));
    }
}

