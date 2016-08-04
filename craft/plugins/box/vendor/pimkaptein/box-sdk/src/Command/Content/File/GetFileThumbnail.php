<?php namespace AdammBalogh\Box\Command\Content\File;

use AdammBalogh\Box\Command\AbstractCommand;
use AdammBalogh\Box\GuzzleHttp\Message\GetRequest;
use AdammBalogh\Box\Request\ExtendedRequest;

class GetFileThumbnail extends AbstractCommand
{
    /**
     * @param string $fileId
     */
    public function __construct($fileId, $min_height = 256, $min_width = 256)
    {
        $this->request = new GetRequest("files/{$fileId}/thumbnail.png?min_height={$min_height}&min_width={$min_width}");
    }
}
