<?php namespace AdammBalogh\Box\Command\Content\File;

use AdammBalogh\Box\Command\AbstractCommand;
use AdammBalogh\Box\GuzzleHttp\Message\GetRequest;

class GetFileVersions extends AbstractCommand
{
    /**
     * @param string $fileId
     */
    public function __construct($fileId)
    {
        $this->request = new GetRequest("files/{$fileId}/versions");
    }
}
