<?php

namespace Craft;

use AdammBalogh\Box\ViewClient;
use AdammBalogh\Box\Client\View\ApiClient;
use AdammBalogh\Box\Client\View\UploadClient;
use AdammBalogh\Box\Command\View;
use AdammBalogh\Box\Factory\ResponseFactory;
use AdammBalogh\Box\GuzzleHttp\Message\SuccessResponse;
use AdammBalogh\Box\GuzzleHttp\Message\ErrorResponse;
use AdammBalogh\Box\Request\ExtendedRequest;

/**
 * Box View Service.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class Box_ViewService extends BaseApplicationComponent
{
    /**
     * @var array
     */
    protected static $_session = [];

    /**
     * Get Box View client.
     *
     * @return ViewClient
     */
    public function createViewClient()
    {
        $apiKey = craft()->config->get('boxViewApiKey');
        $viewClient = new ViewClient(new ApiClient($apiKey), new UploadClient($apiKey));

        return $viewClient;
    }

    /**
     * Create a viewing session by document id.
     *
     * @param int $docID
     *
     * @throws HttpException
     *
     * @return SuccessResponse|null
     */
    public function createSession($docID)
    {
        if (isset(static::$_session[$docID])) {
            return static::$_session[$docID];
        }

        // create temp download link to feed to Box Viewer
        // see: https://developers.box.com/using-the-view-api-with-the-content-api/
        $temp_download_url = craft()->box_content->getTempDownloadUrl($docID);

        // Create a viewclient
        $viewClient = $this->createViewClient();

        // Send Box content file to Box View
        $extendedRequest = new ExtendedRequest();
        $extendedRequest->addQueryField('non_svg', true);

        $command = new View\Document\UrlDocumentUpload($temp_download_url, $extendedRequest);
        $response = ResponseFactory::getResponse($viewClient, $command);

        // File was successfully downloaded to Box View
        if ($response instanceof SuccessResponse) {

            // fetch Box View document
            $temp_document = $response->json();

            // open session for viewing Box View document
            // wait for document to be ready (converting the document is async)
            // see: https://developers.box.com/view/#post-sessions
            do {
                $command = new View\Session\CreateDocumentSession($temp_document['id']);
                $response = ResponseFactory::getResponse($viewClient, $command);

                // wait for it
                $retry_after = intval($response->getHeader('Retry-After'));
                sleep($retry_after);
            } while ($response->getStatusCode() == 202);

            // session successfully created
            if ($response instanceof SuccessResponse) {
                $result = $response->json();
                static::$_session[$docID] = $result;

                return $result;
            }
            // error creating Box View session
            elseif ($response instanceof ErrorResponse) {
                throw new HttpException(403, var_export($response->json(), true));
            }
        }
        // Error while downloading document to Box View
        elseif ($response instanceof ErrorResponse) {
            throw new HttpException(500, var_export($response->json(), true));
        }

        return false;
    }

    /**
     * List documents in Box View.
     *
     * @throws HttpException
     *
     * @return array|bool
     */
    public function listDocuments()
    {
        $viewClient = $this->createViewClient();

        $command = new View\Document\ListDocument();
        $response = ResponseFactory::getResponse($viewClient, $command);

        if ($response instanceof SuccessResponse) {
            return $response->json();
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }

        return false;
    }

    /**
     * Get document content.
     *
     * @param int $docId
     *
     * @throws HttpException
     *
     * @return string|bool
     */
    public function getDocumentContent($docId)
    {
        $viewClient = $this->createViewClient();

        $command = new View\Document\GetDocumentContent($docId, 'zip');
        $response = ResponseFactory::getResponse($viewClient, $command);

        if ($response instanceof SuccessResponse) {
            return $response->getHeader('location');
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }

        return false;
    }

    /**
     * Get thumbnail.
     *
     * @param int $docId
     *
     * @return array|bool
     */
    public function getThumbnail($docId)
    {
        $viewClient = $this->createViewClient();

        $command = new View\Document\GetDocumentThumbnail($docId);
        $response = ResponseFactory::getResponse($viewClient, $command);

        if ($response instanceof SuccessResponse) {
            return $response->json();
        }

        return false;
    }

    /**
     * Delete Box View document.
     *
     * @param int $docId
     *
     * @throws HttpException
     *
     * @return array|bool
     */
    public function deleteDocument($docId)
    {
        $viewClient = $this->createViewClient();

        $command = new View\Document\DeleteDocument($docId);
        $response = ResponseFactory::getResponse($viewClient, $command);

        if ($response instanceof SuccessResponse) {
            return $response->json();
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }

        return false;
    }
}
