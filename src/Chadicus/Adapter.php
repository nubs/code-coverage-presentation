<?php
/**
 * Contains the Chadicus\Adapter interface.
 */
namespace Chadicus;

/**
 * Simple interface for a client adapter.
 */
interface Adapter
{
    /**
     * Execute the specified request against the Marvel API.
     *
     * @param Request $request The request to send.
     *
     * @return Response
     */
    public function send(Request $request);
}
