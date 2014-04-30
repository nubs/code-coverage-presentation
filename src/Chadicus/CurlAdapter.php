<?php
namespace Chadicus;

use DominionEnterprises\Util;

/**
 * Concrete implementation of Adapter using cURL.
 */
final class CurlAdapter implements Adapter
{
    /**
     * Execute the specified request against the Marvel API.
     *
     * @param Request $request The request to send.
     *
     * @return Response
     *
     * @throws \Exception Throws on error.
     */
    public function send(Request $request)
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('cURL extension must be enabled');
        }

        $curlHeaders = array('Expect:');//stops curl automatically putting in expect 100.
        foreach ($request->getHeaders() as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }

        $curlOptions = array(
            CURLOPT_URL => $request->getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => false,
            CURLOPT_HEADER => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_HTTPHEADER => $curlHeaders,
        );

        if (strtoupper($request->getMethod()) !== 'GET') {
            throw new \Exception("Unsupported method '{$request->getMethod()}' given");
        }

        $curl = curl_init();
        if ($curl === false) {
            throw new Exception('Unable to initialize connection');
        }

        if (curl_setopt_array($curl, $curlOptions) === false) {
            throw new Exception('Unable to prepare connection');
        }

        $result = curl_exec($curl);
        if ($result === false) {
            throw new Exception(curl_error($curl));
        }

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        if ($headerSize === false) {
            throw new Exception('Unable to determine header size');
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode === false) {
            throw new Exception('Unable to determine response HTTP code');
        }

        $headers = Util\Http::parseHeaders(substr($result, 0, $headerSize - 1));
        $body = json_decode(substr($result, $headerSize), true);
        $error = Util\Arrays::get(
            [
                JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
                JSON_ERROR_STATE_MISMATCH => ' Invalid or malformed JSON',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX => 'Syntax error',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            ],
            json_last_error()
        );

        if ($error !== null) {
            throw new Exception("Unable to parse response: {$error}");
        }

        return new Response($httpCode, $headers, $body ?: []);
    }
}
