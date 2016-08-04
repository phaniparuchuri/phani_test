<?php namespace AdammBalogh\Box\Command\Content\File;

use AdammBalogh\Box\Command\AbstractCommand;
use AdammBalogh\Box\GuzzleHttp\Message\GetRequest;
use AdammBalogh\Box\Request\ExtendedRequest;

class DownloadFileOlderVersion extends AbstractCommand
{
    /**
     * @param string $fileId
     * @param string $versionId
     * @param ExtendedRequest $extendedRequest
     */
    public function __construct($fileId, $versionId,  ExtendedRequest $extendedRequest = null)
    {
        $this->request = new GetRequest("files/{$fileId}/content/{$versionId}");

        if (!is_null($extendedRequest)) {
            $this->request->setQuery($extendedRequest->getQuery());
        }
    }
}
