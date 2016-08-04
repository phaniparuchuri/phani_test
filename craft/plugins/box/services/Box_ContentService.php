<?php

namespace Craft;

use AdammBalogh\Box\Client\OAuthClient;
use AdammBalogh\KeyValueStore\KeyValueStore;
use AdammBalogh\KeyValueStore\Adapter\FileAdapter;
use Flintstone\Flintstone;
use AdammBalogh\Box\Exception\ExitException;
use AdammBalogh\Box\Exception\OAuthException;
use GuzzleHttp\Exception\ClientException;
use AdammBalogh\Box\ContentClient;
use AdammBalogh\Box\Command\Content;
use AdammBalogh\Box\Factory\ResponseFactory;
use AdammBalogh\Box\Client\Content\ApiClient;
use AdammBalogh\Box\Client\Content\UploadClient;
use AdammBalogh\Box\GuzzleHttp\Message\SuccessResponse;
use AdammBalogh\Box\GuzzleHttp\Message\ErrorResponse;
use AdammBalogh\Box\Request\ExtendedRequest;

/**
 * Box Content Service.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class Box_ContentService extends BaseApplicationComponent
{
    /**
     * Fetch access token (refresh if necessary).
     *
     * @return string
     */
    private function fetchAccessToken()
    {
        $clientId = craft()->config->get('boxContentClientId');
        $clientSecret = craft()->config->get('boxContentClientSecret');
        $redirectUri = craft()->config->get('boxContentRedirectUri');

        $fileClient = Flintstone::load('BoxContentTokens', ['dir' => $this->getTokenStoragePath()]);
        $adapter = new FileAdapter($fileClient);
        $keyValueStore = new KeyValueStore($adapter);

        $oAuthClient = new OAuthClient($keyValueStore, $clientId, $clientSecret, $redirectUri);

        try {
            $oAuthClient->authorize();
        } catch (ExitException $e) {
            # echo "Location header has set (box's authorize page)";
            # Location header has set (box's authorize page)
            # Instead of an exit call it throws an ExitException
            craft()->end();
        } catch (OAuthException $e) {
            # echo "Invalid user credentials";
            # e.g. Invalid user credentials
            # e.g. The user denied access to your application
        } catch (ClientException $e) {
            # echo "code is older than 30 sec";
            # e.g. if $_GET['code'] is older than 30 sec
        }

        $accessToken = $oAuthClient->getAccessToken();

        return $accessToken;
    }

    /**
     * Get Box Content client.
     *
     * @return ContentClient
     */
    public function createClient()
    {
        $accessToken = $this->fetchAccessToken();

        $contentClient = new ContentClient(new ApiClient($accessToken), new UploadClient($accessToken));

        return $contentClient;
    }

    /**
     * Get temporary download url or older version.
     *
     * @param int $docId
     * @param int $version
     *
     * @throws HttpException
     *
     * @return string|null
     */
    public function getTempDownloadUrlOlderVersion($docId, $version)
    {
        $contentClient = $this->createClient();

        $command = new Content\File\DownloadFileOlderVersion($docId, $version);
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->getHeader('location');
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }
    }

    /**
     * Get temporary Box download url.
     *
     * @param int $docId
     *
     * @throws HttpException
     *
     * @return string|null
     */
    public function getTempDownloadUrl($docId)
    {
        $contentClient = $this->createClient();

        $command = new Content\File\DownloadFile($docId);
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->getHeader('location');
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }
    }

    /**
     * Get temporary Box download url.
     *
     * @param int $docId
     *
     * @throws HttpException
     *
     * @return string|null
     */
    public function GetFileThumbnail($docId)
    {
        $contentClient = $this->createClient();

        $command = new Content\File\GetFileThumbnail($docId);
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->getHeader('location');
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }
    }

    /**
     * Get Box file info.
     *
     * @param int $docId
     *
     * @return array|null
     */
    public function getDocumentInfo($docId)
    {
        $contentClient = $this->createClient();

        $command = new Content\File\GetFileInfo($docId);
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->json();
        } elseif ($response instanceof ErrorResponse) {
            return array();
        }
    }

    /**
     * Get Box file version.
     *
     * @param int $docId
     *
     * @return array|null
     */
    public function getFileVersion($docId)
    {
        $contentClient = $this->createClient();

        $command = new Content\File\GetFileVersions($docId);
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->json();
        } elseif ($response instanceof ErrorResponse) {
            return array();
        }
    }

    /**
     * Delete Box file by id.
     *
     * @param int $docId
     *
     * @throws HttpException
     *
     * @return array|bool
     */
    public function deleteDocument($docId)
    {
        $contentClient = $this->createClient();

        $command = new Content\File\DeleteFile($docId);
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->json();
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }

        return false;
    }

    /**
     * Upload a new file version.
     *
     * @param int    $docId
     * @param string $filePath
     *
     * @throws HttpException
     *
     * @return array|bool
     */
    public function uploadNewVersion($docId, $filePath)
    {
        $contentClient = $this->createClient();

        $command = new Content\File\UploadNewFileVersion($docId, file_get_contents($filePath));
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->json();
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }

        return false;
    }

    /**
     * Get folder contents by Folder id.
     *
     * @param int $folderId
     *
     * @throws HttpException
     *
     * @returns array|bool
     */
    private function listFolder($folderId)
    {
        $contentClient = $this->createClient();

        $extendedRequest = new ExtendedRequest();
        $extendedRequest->addQueryField('limit', 1000);

        $command = new Content\Folder\ListFolder($folderId, $extendedRequest);
        $response = ResponseFactory::getResponse($contentClient, $command);

        if ($response instanceof SuccessResponse && $response->getStatusCode() != 400) {
            return $response->json();
        } elseif ($response instanceof ErrorResponse) {
            throw new HttpException(403, var_export($response->json(), true));
        }

        return false;
    }

    /**
     * Saves Box acquired token.
     *
     * @return string
     */
    public function saveToken()
    {
        return $this->fetchAccessToken();
    }

    /**
     * Get token storage path.
     *
     * @return string
     */
    private function getTokenStoragePath()
    {
        $path = craft()->path->getStoragePath().'box_tokens/';
        IOHelper::ensureFolderExists($path);

        return $path;
    }
}
