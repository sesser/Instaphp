<?php

namespace Instaphp\Instagram;
include_once 'InstagramTest.php';

use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Stream\Stream;

class ErrorsTest extends InstagramTest
{
    /**
     * @var Instagram
     */
    protected $object;

    /**
     * Swaps Instagram response with new one.
     * Easiest way of mocking responses without changing Instaphp source code.
     *
     * @param int         $statusCode
     * @param string|null $body
     * @param string|null $contentType
     */
    protected function mockResponse($statusCode, $body = null, $contentType = null)
    {
        $this->config['event.after'] = function (CompleteEvent $event) use ($statusCode, $body, $contentType) {
            $response = $event->getResponse();
            $response->setStatusCode($statusCode);

            if (!is_null($body)) {
                $response->setBody(Stream::factory($body));
            }

            if (!is_null($contentType)) {
                $response->setHeader('content-type', $contentType);
            }
        };
    }

    /**
     * @covers \Instaphp\Instagram\Instagram:parseResponse
     * @expectedException \Instaphp\Exceptions\APIAgeGatedError
     */
    public function testAPIAgeGatedError()
    {
        $this->mockResponse(400, '{"meta": {"error_type": "APIAgeGatedError", "code": 400, "error_message": "you cannot view this resource"}}');

        $this->object = new Users($this->config);

        $res = $this->object->Recent(5830, ["count" => 5]);
    }

    /**
     * @covers \Instaphp\Instagram\Instagram:parseResponse
     * @expectedException \Instaphp\Exceptions\InvalidResponseFormatException
     */
    public function testHTMLPageNotFoundError()
    {
        $this->mockResponse(404, '<html><body>Page not found stub</body></html>', 'text/html');

        $this->object = new Users($this->config);

        $res = $this->object->Recent(5830, ["count" => 5]);
    }
}
