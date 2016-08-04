<?php namespace AdammBalogh\Box\Command\View\Document;

use AdammBalogh\Box\Command\AbstractCommand;
use AdammBalogh\Box\GuzzleHttp\Message\GetRequest;
use AdammBalogh\Box\Request\ExtendedRequest;

class GetDocumentThumbnail extends AbstractCommand
{
    /**
     * @param string $documentId
     * @param ExtendedRequest $extendedRequest
     */
    public function __construct($documentId, ExtendedRequest $extendedRequest = null, $height = 256, $width = 256)
    {
        $this->request = new GetRequest("documents/{$documentId}/thumbnail?width={$width}&height={$height}");

        if (!is_null($extendedRequest)) {
            $this->request->setQuery($extendedRequest->getQuery());
        }
    }
}
